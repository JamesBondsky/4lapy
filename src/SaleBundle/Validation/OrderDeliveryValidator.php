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
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderSplitService;
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

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /**
     * @var OrderSplitService
     */
    protected $orderSplitService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    public function __construct(
        OrderService $orderService,
        OrderStorageService $orderStorageService,
        OrderSplitService $orderSplitService,
        DeliveryService $deliveryService
    ) {
        $this->orderService = $orderService;
        $this->orderStorageService = $orderStorageService;
        $this->orderSplitService = $orderSplitService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * @param mixed $entity
     *
     * @param Constraint $constraint
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
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

        $checkDate = function (int $dateIndex, int $intervalIndex, DeliveryResultInterface $delivery) use (
            $constraint
        ) {
            $delivery = clone $delivery;

            /**
             * это число в общем случае должно быть от 0
             * до разницы между минимальной и максимальной датами доставки
             */
            if (($dateIndex < 0) || $dateIndex >= ($delivery->getPeriodTo() - $delivery->getPeriodFrom())) {
                $this->context->addViolation($constraint->deliveryDateMessage);
            }

            $delivery->setDateOffset($dateIndex);

            if ($delivery->getIntervals()->isEmpty()) {
                return;
            }

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
            $this->context->buildViolation($constraint->deliveryPlaceCodeMessage)
                ->setCode(OrderStorageService::SESSION_EXPIRED_VIOLATION)->addViolation();
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
        try {
            $delivery = $this->orderStorageService->getSelectedDelivery($entity);

        } catch (DeliveryNotFoundException $e) {
            $this->context->addViolation($constraint->deliveryIdMessage);

            return;
        }

        /**
         * Если выбрана курьерская доставка, проверим, что выбрана дата доставки и интервал
         * Если выбран самовывоз, проверим, что выбран магазин или терминал DPD
         */
        if ($this->deliveryService->isDelivery($delivery)) {
            /** @var DeliveryResultInterface $delivery */
            $checkDate($entity->getDeliveryDate(), $entity->getDeliveryInterval(), $delivery);

            if ($entity->isSplit() && !$this->orderSplitService->canSplitOrder($delivery)) {
                $this->context->addViolation($constraint->orderSplitMessage);
                return;
            }

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

                if (!$delivery->setSelectedShop($selectedStore)->isSuccess()) {
                    $this->context->addViolation($constraint->deliveryPlaceCodeMessage);
                    return;
                }

                if ($entity->isSplit() &&
                    !($this->orderSplitService->canSplitOrder($delivery) || $this->orderSplitService->canGetPartial($delivery))
                 ) {
                    $this->context->addViolation($constraint->orderSplitMessage);
                    return;
                }
            } catch (DeliveryNotFoundException $e) {
                $this->context->addViolation($constraint->deliveryPlaceCodeMessage);
            }
        }
    }
}
