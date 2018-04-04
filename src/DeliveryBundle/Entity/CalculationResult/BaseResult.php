<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\StoreBundle\Collection\DeliveryScheduleResultCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliveryScheduleResult;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\DeliveryScheduleService;

abstract class BaseResult extends CalculationResult implements CalculationResultInterface
{
    /**
     * @var \DateTime
     */
    protected $currentDate;

    /**
     * @var int
     */
    protected $deliveryId;

    /**
     * @var string
     */
    protected $deliveryCode;

    /**
     * @var string
     */
    protected $deliveryName;

    /**
     * @var string
     */
    protected $deliveryZone;

    /**
     * @var StockResultCollection
     */
    protected $stockResult;

    /**
     * @var IntervalCollection
     */
    protected $intervals;

    /**
     * @var int
     */
    protected $freeFrom = 0;

    /**
     * @var \DateTime
     */
    protected $deliveryDate;

    /**
     * @var Interval
     */
    protected $selectedInterval;

    /**
     * @var Store
     */
    protected $selectedStore;

    /**
     * @var int
     */
    protected $dateOffset = 0;

    /**
     * @var DeliveryScheduleResult
     */
    protected $shipmentResult;

    /**
     * @return \DateTime
     */
    public function getCurrentDate(): \DateTime
    {
        if (!$this->currentDate) {
            $this->currentDate = new \DateTime();
        }

        return $this->currentDate;
    }

    /**
     * @param \DateTime $currentDate
     *
     * @return CalculationResultInterface
     */
    public function setCurrentDate(\DateTime $currentDate): CalculationResultInterface
    {
        $this->resetResult();
        $this->currentDate = $currentDate;

        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return \DateTime
     */
    public function getDeliveryDate(): \DateTime
    {
        if (null === $this->deliveryDate) {
            $this->doCalculateDeliveryDate();
            $this->doCalculatePeriod();
        }

        return $this->deliveryDate;
    }

    /**
     * @return int
     */
    public function getDeliveryId(): int
    {
        return $this->deliveryId;
    }

    /**
     * @param int $deliveryId
     *
     * @return CalculationResultInterface
     */
    public function setDeliveryId(int $deliveryId): CalculationResultInterface
    {
        $this->resetResult();
        $this->deliveryId = $deliveryId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryCode(): string
    {
        return $this->deliveryCode;
    }

    /**
     * @param string $deliveryCode
     *
     * @return CalculationResultInterface
     */
    public function setDeliveryCode(string $deliveryCode): CalculationResultInterface
    {
        $this->deliveryCode = $deliveryCode;

        return $this;
    }

    /**
     * @return StockResultCollection
     */
    public function getStockResult(): StockResultCollection
    {
        if (!$this->stockResult) {
            $this->stockResult = new StockResultCollection();
        }

        return $this->stockResult;
    }

    /**
     * @param StockResultCollection $stockResult
     *
     * @return CalculationResultInterface
     */
    public function setStockResult(StockResultCollection $stockResult): CalculationResultInterface
    {
        $this->resetResult();
        $this->stockResult = $stockResult;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryName(): string
    {
        return $this->deliveryName;
    }

    /**
     * @param string $deliveryName
     *
     * @return CalculationResultInterface
     */
    public function setDeliveryName(string $deliveryName): CalculationResultInterface
    {
        $this->deliveryName = $deliveryName;

        return $this;
    }

    /**
     * @return IntervalCollection
     */
    public function getIntervals(): IntervalCollection
    {
        if (!$this->intervals) {
            $this->intervals = new IntervalCollection();
        }

        return $this->intervals;
    }

    /**
     * @param IntervalCollection $intervals
     *
     * @return CalculationResultInterface
     */
    public function setIntervals(IntervalCollection $intervals): CalculationResultInterface
    {
        $this->resetResult();
        $this->intervals = $intervals;

        return $this;
    }

    /**
     * @param int $dateIndex
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     * @return IntervalCollection
     */
    public function getAvailableIntervals(int $dateIndex = 0): IntervalCollection
    {
        $result = new IntervalCollection();
        $date = clone $this->getDeliveryDate();
        $diff = abs($this->getPeriodTo() - $this->getPeriodFrom());
        if ($dateIndex < 0 || $dateIndex >= $diff) {
            return $result;
        }

        if ($dateIndex > 0) {
            $date->modify(sprintf('+%s days', $dateIndex));
        }
        $date->setTime(0, 0, 0, 0);

        /** @var Interval $interval */
        foreach ($this->getIntervals() as $interval) {
            $tmpDelivery = clone $this;
            $tmpDate = clone $tmpDelivery->setSelectedInterval($interval)->getDeliveryDate();
            $tmpDate->setTime(0, 0, 0, 0);
            if ($tmpDate <= $date) {
                $result->add($interval);
            }
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getFreeFrom(): int
    {
        return $this->freeFrom;
    }

    /**
     * @param int $freeFrom
     *
     * @return CalculationResultInterface
     */
    public function setFreeFrom(int $freeFrom): CalculationResultInterface
    {
        $this->freeFrom = $freeFrom;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryZone(): string
    {
        return $this->deliveryZone;
    }

    /**
     * @param string $deliveryZone
     *
     * @return CalculationResultInterface
     */
    public function setDeliveryZone(string $deliveryZone): CalculationResultInterface
    {
        $this->resetResult();
        $this->deliveryZone = $deliveryZone;

        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return Interval|null
     */
    public function getSelectedInterval(): ?Interval
    {
        /**
         * Если интервал не выбран, подбираем наиболее подходящий (с минимальной датой доставки)
         */
        if (null === $this->selectedInterval) {
            /** @var IntervalService $intervalService */
            $intervalService = Application::getInstance()->getContainer()->get(IntervalService::class);
            try {
                $this->selectedInterval = $intervalService->getFirstInterval(
                    $this,
                    $this->getIntervals()
                );
            } catch (NotFoundException $e) {
                $this->selectedInterval = $this->getIntervals()->first();
            }
        }

        return $this->selectedInterval;
    }

    /**
     * @param Interval $selectedInterval
     *
     * @return CalculationResultInterface
     */
    public function setSelectedInterval(Interval $selectedInterval): CalculationResultInterface
    {
        $this->resetResult();
        $this->selectedInterval = $selectedInterval;

        return $this;
    }

    /**
     * @return Store
     */
    public function getSelectedStore(): Store
    {
        if (!$this->selectedStore instanceof Store) {
            $stores = $this->getStockResult()->getStores();
            if ($stores->isEmpty()) {
                $this->addError(new Error('Нет складов с доступными товарами'));
            } else {
                $this->setSelectedStore($stores->first());
            }
        }

        return $this->selectedStore;
    }

    /**
     * @param Store $selectedStore
     * @return CalculationResultInterface
     */
    public function setSelectedStore(Store $selectedStore): CalculationResultInterface
    {
        $this->resetResult();
        $this->selectedStore = $selectedStore;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     */
    protected function doCalculateDeliveryDate(): void
    {
        $date = clone $this->getCurrentDate();

        if (null !== $this->stockResult) {
            $this->getSelectedStore();
            $stockResult = $this->getStockResult()->filterByStore($this->selectedStore);

            /**
             * Если есть отложенные товары, то добавляем к дате доставки
             * срок поставки на склад по графику
             */
            if (!$stockResult->getDelayed()->isEmpty()) {
                $date = $this->getStoreShipmentDate($this->selectedStore, $stockResult->getDelayed());
            }

            /**
             * Если склад является магазином, то учитываем его график работы
             */
            if ($this->selectedStore->isShop()) {
                $this->calculateWithStoreSchedule($date, $this->selectedStore);
            }
        }

        $this->deliveryDate = $date;
    }

    protected function doCalculatePeriod(): void
    {
        $this->setPeriodFrom($this->deliveryDate->diff($this->getCurrentDate())->days);
        $this->setPeriodType(self::PERIOD_TYPE_DAY);
    }

    /**
     * @param Store $store
     * @param StockResultCollection $stockResult
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return \DateTime
     */
    protected function getStoreShipmentDate(Store $store, StockResultCollection $stockResult): \DateTime
    {
        $date = clone $this->getCurrentDate();

        $regularStoresByOffer = [];
        $byRequestStoresByOffer = [];
        $hasRegular = false;
        $hasByRequest = false;

        /** @var Offer $offer */
        foreach ($stockResult->getOffers() as $offer) {
            $neededAmount = $stockResult->filterByOffer($offer)->getAmount();
            if ($neededAmount === 0) {
                continue;
            }

            $stores = $offer->getStocks()->getStores($neededAmount);

            if (!$offer->isByRequest()) {
                $hasRegular = true;
                $regularStoresByOffer[$offer->getId()] = $stores;
            } else {
                $hasByRequest = true;
                $byRequestStoresByOffer[$offer->getId()] = $stores;
            }
        }
        $byRequestStores = empty($byRequestStoresByOffer)
            ? new StoreCollection()
            : $this->getStoreIntersection($byRequestStoresByOffer);

        $regularStores = empty($regularStoresByOffer)
            ? new StoreCollection()
            : $this->getStoreIntersection($regularStoresByOffer);

        if ($hasByRequest && $byRequestStores->isEmpty()) {
            $this->addError(new Error('Не найдено складов для товаров под заказ'));
            return $date;
        }

        if ($hasRegular && $regularStores->isEmpty()) {
            $this->addError(new Error('Не найдено складов для товаров из регулярного ассортимента'));
            return $date;
        }

        $resultCollection = new DeliveryScheduleResultCollection();

        $scheduleService = Application::getInstance()->getContainer()->get(DeliveryScheduleService::class);
        /**
         * Если есть товары под заказ
         */
        if (!$byRequestStores->isEmpty()) {
            /**
             * Для товаров под заказ добавляем +2 ко дню доставки
             */
            $date->modify(sprintf('+%s days', 2));

            /**
             * Находим маршрут от складов поставщика до РЦ
             */
            $schedules = $scheduleService->findBySenders($byRequestStores);
            if ($schedules->isEmpty()) {
                $this->addError(new Error('Не найдено графиков поставок со складов поставщика'));
                return $date;
            }

            if (!$regularStores->isEmpty()) {
                /**
                 * Если есть товары из регулярного ассортимента, то оставляем только те склады,
                 * куда есть поставки со складов поставщика
                 */
                $regularStores = $this->getStoreIntersection([$schedules->getReceivers(), $regularStores]);
                /** @var DeliveryScheduleResult $result */
                foreach ($schedules->getNextDeliveries($regularStores, $date) as $result) {
                    if (!$tmpResult = $this->getDCShipmentResult(
                        $store,
                        $result->getSchedule()->getReceiver(),
                        $result->getDate())
                    ) {
                        continue;
                    }

                    $resultCollection->add($tmpResult);
                }
            } else {
                $receivers = $schedules->getReceivers();
                if ($receivers->hasStore($store)) {
                    /**
                     * Можно доставить со склада поставщика напрямую на нужный склад
                     */
                    $resultCollection = $schedules->getNextDeliveries($schedules->getReceivers(), $date);
                } else {
                    foreach ($schedules->getNextDeliveries($schedules->getReceivers(), $date) as $result) {
                        if (!$tmpResult = $this->getDCShipmentResult(
                            $store,
                            $result->getSchedule()->getReceiver(),
                            $result->getDate())
                        ) {
                            continue;
                        }

                        $resultCollection->add($tmpResult);
                    }
                }
            }
        } else {
            /** @var Store $sender */
            foreach ($regularStores as $sender) {
                if (!$result = $this->getDCShipmentResult($store, $sender, $date)) {
                    continue;
                }

                $resultCollection->add($result);
            }
        }

        if ($resultCollection->isEmpty()) {
            $this->addError(new Error('Не найдено графиков поставок с РЦ'));
            return $date;
        }

        $this->shipmentResult = $resultCollection->getFastest();
        $date = $this->shipmentResult->getDate();

        if ($store->isShop()) {
            /**
             * Добавляем "срок поставки" к дате доставки
             * (он должен быть не менее 1 дня)
             */
            $modifier = $store->getDeliveryTime();
            if ($store->getDeliveryTime() < 1) {
                $modifier = 1;
            }
            $date->modify(sprintf('+%s days', $modifier));
        }

        return $date;
    }

    /**
     * @param Store $receiver
     * @param Store $sender
     * @param \DateTime $date
     *
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws ApplicationCreateException
     * @return DeliveryScheduleResult|null
     */
    protected function getDCShipmentResult(Store $receiver, Store $sender, \DateTime $date): ?DeliveryScheduleResult
    {
        $scheduleService = Application::getInstance()->getContainer()->get(DeliveryScheduleService::class);

        $date = clone $date;
        /**
         * Находим дату отгрузки
         */
        $date = $receiver->getShipmentDate($date);

        /**
         * Добавляем к ней 1 день (по ТЗ)
         */
        $date->modify('+1 day');

        /**
         * Ищем графики поставок с РЦ на нужный склад/магазин
         */
        return $scheduleService->findBySender($sender)
            ->getNextDelivery(
                $receiver,
                $date
            );
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return int
     */
    public function getPeriodFrom(): int
    {
        if (null === $this->periodFrom) {
            $this->getDeliveryDate();
            $this->doCalculatePeriod();
        }

        return parent::getPeriodFrom();
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return string
     */
    public function getPeriodType(): string
    {
        $this->getDeliveryDate();
        return parent::getPeriodType();
    }

    /**
     * @return int
     */
    public function getDateOffset(): int
    {
        return $this->dateOffset;
    }

    /**
     * @param int $dateOffset
     * @return BaseResult
     */
    public function setDateOffset(int $dateOffset): CalculationResultInterface
    {
        $this->dateOffset = $dateOffset;
        $this->resetResult();
        return $this;
    }

    /**
     * @return DeliveryScheduleResult
     */
    public function getShipmentResult(): ?DeliveryScheduleResult
    {
        return $this->shipmentResult;
    }

    /**
     * @param DeliveryScheduleResult $shipmentResult
     * @return BaseResult
     */
    public function setShipmentResult(DeliveryScheduleResult $shipmentResult): CalculationResultInterface
    {
        $this->shipmentResult = $shipmentResult;
        return $this;
    }

    /**
     * @param bool $internalCall
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return bool
     */
    public function isSuccess($internalCall = false)
    {
        if ($this->isSuccess) {
            /**
             * Расчет даты доставки
             */
            $this->getDeliveryDate();
        }
        return parent::isSuccess($internalCall);
    }

    /**
     * Изменяет дату доставки в соответствии с графиком работы магазина
     *
     * @param \DateTime $date
     * @param Store $store
     */
    protected function calculateWithStoreSchedule(\DateTime $date, Store $store): void
    {
        $schedule = $store->getSchedule();
        $hour = (int)$date->format('G') + 1;
        if ($hour < $schedule->getFrom()) {
            $date->setTime($schedule->getFrom() + 1, 0);
        } elseif ($hour > $schedule->getTo()) {
            $date->modify('+1 day');
            $date->setTime($schedule->getFrom() + 1, 0);
        } elseif ($date->format('z') !== $this->getCurrentDate()->format('z')) {
            $date->setTime($schedule->getFrom() + 1, 0);
        } else {
            $date->modify('+1 hour');
        }
    }

    /**
     * Получить пересечение коллекций
     *
     * @param array $storeCollections
     * @return StoreCollection
     */
    protected function getStoreIntersection(array $storeCollections = []): StoreCollection
    {
        if (empty($storeCollections)) {
            return new StoreCollection();
        }
        if (\count($storeCollections) === 1) {
            return current($storeCollections);
        }

        /**
         * @var string $i
         * @var StoreCollection $storeCollection
         */
        foreach ($storeCollections as $i => $storeCollection) {
            $storeCollections[$i] = $storeCollection->toArray();
        }

        /**
         * Функция сравнения складов
         * @param Store $store1
         * @param Store $store2
         * @return int
         */
        $storeCollections[] = function (Store $store1, Store $store2) {
            return $store1->getXmlId() <=> $store2->getXmlId();
        };

        return new StoreCollection(array_uintersect(...$storeCollections));
    }

    protected function resetResult(): void
    {
/** @todo А здесь не надо сбрасывать $this->periodFrom ? */
        $this->deliveryDate = null;
        $this->errors = new ErrorCollection();
        $this->isSuccess = true;
        $this->warnings = new ErrorCollection();
    }

    public function __clone()
    {
        $this->resetResult();
    }
}
