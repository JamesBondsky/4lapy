<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Validation;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderDeliveryValidator extends ConstraintValidator
{
    /**
     * Максимальное время хранения даты перехода пользователя на 2й шаг оформления заказа
     */
    public const MAX_DATE_DIFF = 1800;

    /** @var OrderService */
    protected $orderService;

    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    public function __construct(
        OrderService $orderService,
        OrderStorageService $orderStorageService,
        DeliveryService $deliveryService
    ) {
        $this->orderService = $orderService;
        $this->orderStorageService = $orderStorageService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * @param mixed $entity
     *
     * @param Constraint $constraint
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof OrderStorage || !$constraint instanceof OrderDelivery) {
            return;
        }

        $checkDate = function (int $dateIndex, int $intervalIndex, CalculationResultInterface $delivery) use (
            $constraint
        ) {
            /**
             * это число в общем случае должно быть от 0
             * до разницы между минимальной и максимальной датами доставки
             */
            if (($dateIndex < 0) || $dateIndex >= ($delivery->getPeriodTo() - $delivery->getPeriodFrom())) {
                $this->context->addViolation($constraint->deliveryDateMessage);
            }

            $delivery->setDateOffset($dateIndex);
            $intervals = $delivery->getAvailableIntervals();
            if (!$intervals->isEmpty()) {
                if (($intervalIndex < 1) || $intervalIndex > $intervals->count()) {
                    $this->context->addViolation($constraint->deliveryIntervalMessage);
                }
            } else {
                $this->context->addViolation($constraint->deliveryIntervalMessage);
            }
        };

        $dateDiff = $entity->getCurrentDate()->getTimestamp() - (new \DateTime())->getTimestamp();
        if (abs($dateDiff) > static::MAX_DATE_DIFF) {
            $this->context->addViolation($constraint->deliveryDateExpiredMessage);
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
        $deliveryMethods = $this->orderStorageService->getDeliveries($entity);
        $delivery = null;
        /** @var CalculationResultInterface $deliveryMethod */
        foreach ($deliveryMethods as $deliveryMethod) {
            if ($deliveryId === $deliveryMethod->getDeliveryId()) {
                $delivery = $deliveryMethod;
                break;
            }
        }
        if (null === $delivery) {
            $this->context->addViolation($constraint->deliveryIdMessage);

            return;
        }

        if ($entity->isSplit() && !$this->orderService->canSplitOrder($entity)) {
            $this->context->addViolation($constraint->orderSplitMessage);

            return;
        }

        /**
         * Если выбрана курьерская доставка, проверим, что выбрана дата доставки и интервал
         * Если выбран самовывоз, проверим, что выбран магазин или терминал DPD
         */
        if ($this->deliveryService->isDelivery($delivery)) {
            $checkDate($entity->getDeliveryDate(), $entity->getDeliveryInterval(), $delivery);
            if ($entity->isSplit()) {
                $checkDate($entity->getSecondDeliveryDate(), $entity->getSecondDeliveryInterval(), $delivery);
            }
        } else {
            /** @var PickupResultInterface $delivery */
            try {
                $availableStores = $delivery->getBestShops();
                $storeXmlId = $entity->getDeliveryPlaceCode();
                $selectedStore = null;
                /** @var Store $store */
                foreach ($availableStores as $store) {
                    if ($store->getXmlId() === $storeXmlId) {
                        $selectedStore = $store;
                        break;
                    }
                }

                if (null === $selectedStore) {
                    $this->context->addViolation($constraint->deliveryPlaceCodeMessage);
                    return;
                }

                if (!$delivery->setSelectedStore($selectedStore)->isSuccess()) {
                    $this->context->addViolation($constraint->deliveryPlaceCodeMessage);
                    return;
                }
            } catch (DeliveryNotFoundException $e) {
                $this->context->addViolation($constraint->deliveryPlaceCodeMessage);
            }
        }
    }
}
