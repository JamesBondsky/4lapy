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
use FourPaws\DeliveryBundle\Entity\StockResult;
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
    /** @var DeliveryScheduleResult[] */
    protected static $scheduleResults = [];

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
    protected $fullstockResult;

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
     * @var DeliveryScheduleResultCollection
     */
    protected $shipmentResults;

    /**
     * @var Store
     */
    protected $shipmentStore;

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
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return StockResultCollection
     */
    public function getStockResult(): StockResultCollection
    {
        if (null === $this->stockResult) {
            $this->stockResult = clone $this->getFullStockResult()->filterByStore($this->getSelectedStore());
        }

        return $this->stockResult;
    }

    /**
     * @return StockResultCollection
     */
    public function getFullStockResult(): StockResultCollection
    {
        if (!$this->fullstockResult) {
            $this->fullstockResult = new StockResultCollection();
        }

        return $this->fullstockResult;
    }

    /**
     * @param StockResultCollection $stockResult
     *
     * @return CalculationResultInterface
     */
    public function setStockResult(StockResultCollection $stockResult): CalculationResultInterface
    {
        $this->resetResult();
        $this->fullstockResult = $stockResult;

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
        if (($dateIndex >= 0) && ($dateIndex <= $diff)) {
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
                if (!$this->getIntervals()->isEmpty()) {
                    $this->selectedInterval = $this->getIntervals()->first();
                }
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
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return Store
     */
    public function getSelectedStore(): Store
    {
        if (!$this->selectedStore instanceof Store) {
            $this->selectedStore = $this->doGetBestStores()->first();
        }

        return $this->selectedStore;
    }

    /**
     * @param Store $selectedStore
     *
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

        if (null !== $this->fullstockResult) {
            $this->getSelectedStore();
            $stockResult = $this->getStockResult()->getOrderable()->filterByStore($this->selectedStore);

            /**
             * Если есть отложенные товары, то добавляем к дате доставки
             * срок поставки на склад по графику
             */
            if (!$stockResult->getDelayed()->isEmpty()) {
//                $date = $this->getStoreShipmentDate($this->selectedStore, $stockResult);
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
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return \DateTime
     */
    protected function getStoreShipmentDate(Store $store, StockResultCollection $stockResult): \DateTime
    {
        if (!static::$scheduleResults) {
            static::$scheduleResults = new DeliveryScheduleResultCollection();
        }

        $date = clone $this->getCurrentDate();

        $scheduleService = Application::getInstance()->getContainer()->get(DeliveryScheduleService::class);
        $delayed = $stockResult->getDelayed();
        $resultCollection = new DeliveryScheduleResultCollection();

        /** @var Offer $offer */
        foreach ($stockResult->getOffers() as $offer) {
            $stockResultForOffer = $delayed->filterByOffer($offer);

            $stocksForOffer = $offer->getStocks();
            $storesForOffer = $stocksForOffer->getStores();

            $tmpDate = clone $date;

            if ($offer->isByRequest()) {
                /**
                 * Для товаров под заказ добавляем +2 ко дню доставки
                 */
                $tmpDate->modify(sprintf('+%s days', 2));

                /**
                 * Находим маршрут от складов поставщика до РЦ
                 */
                $schedules = $scheduleService->findBySenders($storesForOffer);
                if ($schedules->isEmpty()) {
                    /**
                     * Товар под заказ недоступен для доставки
                     */
                    $stockResultForOffer->setType(StockResult::TYPE_UNAVAILABLE);
                    continue;
                }

                /** @var Store $receiver */
                $senders = $schedules->getSenders();
                foreach ($schedules->getReceivers() as $receiver) {
                    if (!$result = $this->getScheduleResult($receiver, $senders, $date)) {
                        continue;
                    }

                    $amount = $stocksForOffer->filterByStore($result->getSchedule()->getSender())->getTotalAmount();
                    if ($result->getSchedule()->getReceiver()->getXmlId() === $store->getXmlId()) {
                        $result->setOffer($offer)->setAmount($amount);
                        $resultCollection->add($result);
                        continue;
                    }

                    if (!$tmpResult = $this->getDCShipmentResult(
                        $store,
                        new StoreCollection([$result->getSchedule()->getReceiver()]),
                        $result->getDate())
                    ) {
                        continue;
                    }

                    $tmpResult->setOffer($offer)->setAmount($amount);

                    $resultCollection->add($tmpResult);
                }
            } else {
                if ($result = $this->getDCShipmentResult($store, $storesForOffer, $date)) {
                    $result->setOffer($offer)
                        ->setAmount(
                            $stocksForOffer->filterByStore($result->getSchedule()->getSender())->getTotalAmount()
                        );
                    $resultCollection->add($result);
                } else {
                    /**
                     * Товар из регулярного ассортимента недоступен для доставки
                     */
                    $stockResultForOffer->setType(StockResult::TYPE_UNAVAILABLE);
                    continue;
                }
            }
        }

        if ($resultCollection->isEmpty()) {
            if ($stockResult->getAvailable()->isEmpty()) {
                $this->addError(new Error('Нет доступных товаров и не найдено графиков поставок для недоступных'));
            } else {
                $delayed->setType(StockResult::TYPE_UNAVAILABLE);
            }
        } else {
            $this->shipmentResults = $resultCollection->getFastest();
            $date = $this->shipmentResults->getDate();

            $delayed = $stockResult->getDelayed();
            foreach ($delayed->getOffers() as $offer) {
                /** @var DeliveryScheduleResult $shipmentResultForOffer */
                $shipmentResultForOffer = $this->shipmentResults->filterByOffer($offer)->first();
                /** @var StockResult $stockResultForOffer */
                $stockResultForOffer = $delayed->filterByOffer($offer)->first();
                if ($shipmentResultForOffer) {
                    $this->shipmentStore = $shipmentResultForOffer->getSchedule()->getSender();

                    $diff = $stockResultForOffer->getAmount() - $shipmentResultForOffer->getAmount();
                    if ($diff > 0) {
                        /**
                         * Если может быть поставлено меньшее, чем нужно, количество
                         */
                        $stockResultForOffer->setAmount($shipmentResultForOffer->getAmount());
                        $this->stockResult->add(
                            (clone $stockResultForOffer)->setAmount($diff)
                                ->setType(StockResult::TYPE_UNAVAILABLE)
                        );
                    }
                } else {
                    /**
                     * Если для этого оффера нет графиков
                     */
                    $stockResultForOffer->setType(StockResult::TYPE_UNAVAILABLE);
                }
            }

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
        }

        return $date;
    }

    /**
     * @param Store $receiver
     * @param StoreCollection $senders
     * @param \DateTime $date
     *
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws ApplicationCreateException
     * @return DeliveryScheduleResult|null
     */
    protected function getDCShipmentResult(
        Store $receiver,
        StoreCollection $senders,
        \DateTime $date
    ): ?DeliveryScheduleResult {
        $result = null;

        $date = clone $date;
        /**
         * Находим дату отгрузки
         */
        $date = $receiver->getShipmentDate($date);

        /**
         * Добавляем к ней 1 день (по ТЗ)
         */
        $date->modify('+1 day');

        return $this->getScheduleResult($receiver, $senders, $date);
    }

    /**
     * @param Store $receiver
     * @param StoreCollection $senders
     * @param \DateTime $date
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return DeliveryScheduleResult|null
     */
    protected function getScheduleResult(
        Store $receiver,
        StoreCollection $senders,
        \DateTime $date
    ): ?DeliveryScheduleResult {
        $cacheKey = json_encode([
            'receiver' => $receiver->getXmlId(),
            'senders' => $senders->getXmlIds(),
            'from' => $date->getTimestamp()
        ]);

        if (array_key_exists($cacheKey, static::$scheduleResults)) {
            $result = static::$scheduleResults[$cacheKey];
        } else {
            $scheduleService = Application::getInstance()->getContainer()->get(DeliveryScheduleService::class);
            $result = $scheduleService->findBySenders($senders)
                ->getNextDelivery(
                    $receiver,
                    $date
                );
            static::$scheduleResults[$cacheKey] = $result;
        }

        return $result;
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
     *
     * @return BaseResult
     */
    public function setDateOffset(int $dateOffset): CalculationResultInterface
    {
        $this->dateOffset = $dateOffset;
        $this->resetResult();
        return $this;
    }

    /**
     * @return DeliveryScheduleResultCollection|null
     */
    public function getShipmentResults(): ?DeliveryScheduleResultCollection
    {
        return $this->shipmentResults;
    }

    /**
     * @param DeliveryScheduleResult $shipmentResults
     *
     * @return BaseResult
     */
    public function setShipmentResults(DeliveryScheduleResult $shipmentResults): CalculationResultInterface
    {
        $this->shipmentResults = $shipmentResults;
        return $this;
    }

    /**
     * @return Store
     */
    public function getShipmentStore(): ?Store
    {
        return $this->shipmentStore;
    }

    /**
     * @param Store $shipmentStore
     *
     * @return BaseResult
     */
    public function setShipmentStore(Store $shipmentStore): CalculationResultInterface
    {
        $this->shipmentStore = $shipmentStore;
        return $this;
    }

    /**
     * @param bool $internalCall
     *
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
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return StoreCollection
     */
    protected function doGetBestStores(): StoreCollection
    {
        $stores = $this->fullstockResult->getStores();
        $storeData = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            $calculationResult = (clone $this)->setSelectedStore($store);
            try {
                $calculationResult->isSuccess();
            } catch (NotFoundException $e) {
                // не нужно
            }
            $storeData[$store->getXmlId()] = [
                'RESULT' => $calculationResult,
                'AVAILABLE_PRICE' => $calculationResult->getStockResult()->getAvailable()->getPrice(),
                'ORDERABLE_PRICE' => $calculationResult->getStockResult()->getOrderable()->getPrice()
            ];
        }

        /**
         * 1) По убыванию % от суммы товаров заказа в наличии в магазине
         * 2) По возрастанию даты готовности заказа к выдаче
         * 3) По адресу магазина в алфавитном порядке
         */
        /**
         * @param Store $store1
         * @param Store $store2
         *
         * @return int
         * @throws ArgumentException
         * @throws ApplicationCreateException
         * @throws StoreNotFoundException
         */
        $sortFunc = function (Store $store1, Store $store2) use ($storeData) {
            /** @var array $storeData1 */
            $storeData1 = $storeData[$store1->getXmlId()];
            /** @var array $storeData2 */
            $storeData2 = $storeData[$store2->getXmlId()];

            /** @var PickupResult $result1 */
            $result1 = $storeData1['RESULT'];
            /** @var PickupResult $result2 */
            $result2 = $storeData2['RESULT'];

            /** в начало переносим склады с доступной доставкой/самовывозом */
            if ($result1->isSuccess() !== $result2->isSuccess()) {
                return $result2->isSuccess() <=> $result1->isSuccess();
            }

            if ($storeData1['ORDERABLE_PRICE'] !== $storeData2['ORDERABLE_PRICE']) {
                return $storeData2['ORDERABLE_PRICE'] <=> $storeData1['ORDERABLE_PRICE'];
            }

            if ($storeData1['AVAILABLE_PRICE'] !== $storeData2['AVAILABLE_PRICE']) {
                return $storeData2['AVAILABLE_PRICE'] <=> $storeData1['AVAILABLE_PRICE'];
            }

            $deliveryDate1 = $result1->getDeliveryDate();
            $deliveryDate2 = $result2->getDeliveryDate();

            if ($deliveryDate1 !== $deliveryDate2) {
                return $deliveryDate1 <=> $deliveryDate2;
            }

            return $store1->getAddress() <=> $store2->getAddress();
        };

        $iterator = $stores->getIterator();
        $iterator->uasort($sortFunc);

        return new StoreCollection(iterator_to_array($iterator));
    }

    protected function resetResult(): void
    {
        $this->deliveryDate = null;
        $this->errors = new ErrorCollection();
        $this->isSuccess = true;
        $this->warnings = new ErrorCollection();
        $this->stockResult = null;
    }

    public function __clone()
    {
        $this->resetResult();
    }
}
