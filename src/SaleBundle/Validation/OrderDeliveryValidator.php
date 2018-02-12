<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Validation;

use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Exception\NotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderDeliveryValidator extends ConstraintValidator
{
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
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof OrderStorage || !$constraint instanceof OrderDelivery) {
            return;
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
        $deliveryMethods = $this->orderService->getDeliveries();
        $delivery = null;
        foreach ($deliveryMethods as $deliveryMethod) {
            if ($deliveryId === (int)$deliveryMethod->getData()['DELIVERY_ID']) {
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
            $intervals = $delivery->getData()['DELIVERY_INTERVALS'];
            if (!empty($intervals)) {
                if (($intervalIndex < 1) || $intervalIndex > \count($intervals)) {
                    $this->context->addViolation($constraint->deliveryIntervalMessage);
                }
            }
        } else {
            try {
                $availableStores = $this->deliveryService->getStockResultByDelivery($delivery)->getStores();
                $storeXmlId = $entity->getDeliveryPlaceCode();
                if (!isset($availableStores[$storeXmlId])) {
                    $this->context->addViolation($constraint->deliveryPlaceCodeMessage);

                    return;
                }
                /* @todo проверка частичного получения заказа */
            } catch (NotFoundException $e) {
                $this->context->addViolation($constraint->deliveryPlaceCodeMessage);
            }
        }
    }
}
