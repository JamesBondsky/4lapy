<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Validation;

use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Exception\NotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderDeliveryValidator extends ConstraintValidator
{
    /**
     * Максимальное время хранения даты перехода пользователя на 2й шаг оформления заказа
     */
    public const MAX_DATE_DIFF = 1800;


    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    public function __construct(OrderService $orderService, DeliveryService $deliveryService)
    {
        $this->orderService = $orderService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * @param mixed $entity
     * @param Constraint $constraint
     * @throws DeliveryNotFoundException
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof OrderStorage || !$constraint instanceof OrderDelivery) {
            return;
        }

        $dateDiff = $entity->getCurrentDate()->getTimestamp() - (new \DateTime())->getTimestamp();
        if (abs($dateDiff) > static::MAX_DATE_DIFF) {
            $this->context->addViolation($constraint->deliveryDateExpiredMessage);
        }

        /**
         * Проверка, что выбрана доставка
         */
        if (!$deliveryId = $entity->getDeliveryId()) {
            $this->context->addViolation($constraint->deliveryIdMessage);

            return;
        }

        /**
         * Проверка, что выбрана доступная доставка
         */
        $deliveryMethods = $this->orderService->getDeliveries($entity);
        $delivery = null;
        foreach ($deliveryMethods as $deliveryMethod) {
            if ($deliveryId === $deliveryMethod->getDeliveryId()) {
                $delivery = $deliveryMethod;
                break;
            }
        }
        if (!$delivery) {
            $this->context->addViolation($constraint->deliveryIdMessage);

            return;
        }

        /**
         * Если выбрана курьерская доставка, проверим, что выбрана дата доставки и интервал
         * Если выбран самовывоз, проверим, что выбран магазин или терминал DPD
         */
        if ($this->deliveryService->isDelivery($delivery)) {
            /*
             * это число в общем случае должно быть от 0
             * до разницы между минимальной и максимальной датами доставки
             */
            $dateIndex = $entity->getDeliveryDate();
            if (($dateIndex < 0) || $dateIndex >= ($delivery->getPeriodTo() - $delivery->getPeriodFrom())) {
                $this->context->addViolation($constraint->deliveryDateMessage);
            }

            $intervalIndex = $entity->getDeliveryInterval();
            $intervals = $delivery->getIntervals();
            if (!empty($intervals)) {
                if (($intervalIndex < 1) || $intervalIndex > $intervals->count()) {
                    $this->context->addViolation($constraint->deliveryIntervalMessage);
                }
            }
        } else {
            try {
                $availableStores = $delivery->getStockResult()->getStores();
                $storeXmlId = $entity->getDeliveryPlaceCode();
                if (!isset($availableStores[$storeXmlId])) {
                    $this->context->addViolation($constraint->deliveryPlaceCodeMessage);

                    return;
                }
                /* @todo проверка частичного получения заказа */
            } catch (DeliveryNotFoundException $e) {
                $this->context->addViolation($constraint->deliveryPlaceCodeMessage);
            }
        }
    }
}
