<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\DeliveryScheduleResult;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Collection\DeliveryScheduleResultCollection;
use FourPaws\DeliveryBundle\Service\DeliveryScheduleResultService;
use FourPaws\StoreBundle\Collection\ScheduleResultCollection;
use FourPaws\StoreBundle\Collection\StockCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Entity\Stock;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\ScheduleResultService;

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
     * @var int
     */
    protected $freeFrom = 0;

    /**
     * @var \DateTime
     */
    protected $deliveryDate;

    /**
     * @var Store
     */
    protected $selectedStore;

    /**
     * @var DeliveryScheduleResultCollection
     */
    protected $shipmentResults;

    /**
     * @var string
     */
    protected $currency = '₽';

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
        $this->currentDate = clone $currentDate;

        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
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
            $this->stockResult = $this->getFullStockResult()->isEmpty() ?
                $this->getFullStockResult() :
                clone $this->getFullStockResult()->filterByStore($this->getSelectedStore());
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

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency($currency): CalculationResultInterface
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    protected function doCalculateDeliveryDate(): void
    {
        $date = clone $this->getCurrentDate();
        $this->deliveryDate = $date;

        if (null !== $this->fullstockResult) {
            if ($this->fullstockResult->getOrderable()->isEmpty()) {
                $this->addError(new Error('Нет остатков на складах по данному набору товаров'));
            } else {
                $this->getSelectedStore();
                $stockResult = $this->getStockResult()->getOrderable()->filterByStore($this->selectedStore);
                /** @var StockResult $item */
                foreach ($stockResult as $item) {
                    if (!$this->checkIsDeliverable($item->getOffer())) {
                        $item->setType(StockResult::TYPE_UNAVAILABLE);
                    }
                }
                $stockResult = $stockResult->getOrderable();
                if ($stockResult->isEmpty()) {
                    $this->addError(new Error('Нет доступных для доставки товаров'));
                    return;
                }

                /**
                 * Если есть отложенные товары, то добавляем к дате доставки
                 * срок поставки на склад по графику
                 */
                if (!$stockResult->getDelayed()->isEmpty()) {
                    $date = $this->getStoreShipmentDate($this->selectedStore, $stockResult);
                }

                if (!$stockResult->getDelayed()->getPrice() && !$stockResult->getAvailable()->getPrice()) {
                    $this->addError(new Error('Нет доступных для доставки товаров с ненулевой ценой'));
                    return;
                }
            }
        }

        $this->deliveryDate = $date;
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    protected function doCalculatePeriod(): void
    {
        $currentDate = clone $this->getCurrentDate();
        $deliveryDate = clone $this->getDeliveryDate();
        $currentDate->setTime(0,0,0,0);
        $deliveryDate->setTime(0,0,0,0);
        $this->setPeriodFrom($deliveryDate->diff($currentDate)->days);
        $this->setPeriodType(self::PERIOD_TYPE_DAY);
    }

    /**
     * @param Store                 $store
     * @param StockResultCollection $stockResult
     *
     * @throws ApplicationCreateException
     * @throws StoreNotFoundException
     * @return \DateTime
     */
    protected function getStoreShipmentDate(Store $store, StockResultCollection $stockResult): \DateTime
    {
        if (!static::$scheduleResults) {
            static::$scheduleResults = new DeliveryScheduleResultCollection();
        }

        $date = clone $this->getCurrentDate();

        $delayed = $stockResult->getDelayed();
        /** @var StockCollection[] $stocksByStore */
        $stocksByStore = [];
        $stores = [];

        $offers = $delayed->getOffers();
        /** @var Offer $offer */
        foreach ($offers as $offer) {
            /** @var Stock $stock */
            foreach ($offer->getAllStocks() as $stock) {
                $storeXmlId = $stock->getStore()->getXmlId();
                if (!isset($stores[$storeXmlId])) {
                    $stores[$storeXmlId] = $stock->getStore();
                }

                if (!isset($stocksByStore[$storeXmlId])) {
                    $stocksByStore[$storeXmlId] = new StockCollection();
                }
                $stocksByStore[$storeXmlId][$offer->getId()] = $stock;
            }
        }

        /**
         * @var string $storeXmlId
         * @var StockCollection $stocks
         */
        $resultCollection = new DeliveryScheduleResultCollection();
        foreach ($stocksByStore as $storeXmlId => $stocks) {
            foreach ($this->getScheduleResults($stores[$storeXmlId], $store, $stocks, $delayed) as $scheduleResult) {
                $resultCollection->add($scheduleResult);
            }
        }

        if ($resultCollection->isEmpty()) {
            if ($stockResult->getAvailable()->isEmpty()) {
                $this->addError(new Error('Нет доступных товаров и не найдено графиков поставок для недоступных'));
            } else {
                $delayed->setType(StockResult::TYPE_UNAVAILABLE);
            }
        } else {
            /** @var DeliveryScheduleResultService $scheduleResultService */
            $scheduleResultService = Application::getInstance()->getContainer()->get(DeliveryScheduleResultService::class);
            $this->shipmentResults = $scheduleResultService->getFastest($resultCollection, $this->getCurrentDate());

            $date->modify(sprintf('+%s days', $this->shipmentResults->getDays($this->getCurrentDate())));
            foreach ($offers as $offer) {
                $amount = $this->shipmentResults->getAmountByOffer($offer);
                /** @var StockResult $stockResultForOffer */
                $stockResultForOffer = $delayed->filterByOffer($offer)->first();
                if ($amount) {
                    $diff = $stockResultForOffer->getAmount() - $amount;
                    if ($diff > 0) {
                        /**
                         * Если может быть поставлено меньшее, чем нужно, количество
                         */
                        $unavailableStockResultForOffer = $stockResultForOffer->splitByAmount($amount);

                        $this->stockResult->add(
                            $unavailableStockResultForOffer->setType(StockResult::TYPE_UNAVAILABLE)
                        );
                    }
                } else {
                    /**
                     * Если для этого оффера нет графиков
                     */
                    $stockResultForOffer->setType(StockResult::TYPE_UNAVAILABLE);
                }
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

        if ($this->shipmentResults) {
            /**
             * Устанавливаем время доставки 9 утра
             */
            $date->setTime(9, 0, 0, 0);
        }

        return $date;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Store                 $sender
     * @param Store                 $receiver
     * @param StockCollection       $stocks
     * @param StockResultCollection $delayed
     *
     * @throws ApplicationCreateException
     * @return DeliveryScheduleResultCollection
     */
    protected function getScheduleResults(
        Store $sender,
        Store $receiver,
        StockCollection $stocks,
        StockResultCollection $delayed
    ): DeliveryScheduleResultCollection
    {
        /** @var Store $sender */
        $cacheKey = implode('_', [$sender->getXmlId(), $receiver->getXmlId()]);
        if (array_key_exists($cacheKey, static::$scheduleResults)) {
            $scheduleResults = static::$scheduleResults[$cacheKey];
        } else {
            $scheduleResultService = Application::getInstance()->getContainer()->get(ScheduleResultService::class);

            $scheduleResults = new ScheduleResultCollection();

            /** @var ScheduleResult $scheduleResult */
            foreach ($scheduleResultService->findResultsBySenderAndReceiver($sender, $receiver) as $scheduleResult) {
                $key = implode(',', $scheduleResult->getRouteCodes());

                $days = $scheduleResult->getDays($this->getCurrentDate());
                if ($days === ScheduleResult::RESULT_ERROR) {
                    continue;
                }

                if (!isset($scheduleResults[$key]) ||
                    ($scheduleResults[$key]->getDays($this->getCurrentDate()) > $days)
                ) {
                    $scheduleResults[$key] = $scheduleResult;
                }
            }

            static::$scheduleResults[$cacheKey] = $scheduleResults;
        }

        $results = new DeliveryScheduleResultCollection();

        $currentStockResults = new StockResultCollection();
        /** @var Stock $stock */
        foreach ($stocks as $stock) {
            $offerId = $stock->getProductId();
            $currentStockResults[$offerId] = $delayed->filterByOfferId($offerId)->first();
        }

        foreach ($scheduleResults as $scheduleResult) {
            $results->add(
                (new DeliveryScheduleResult())->setScheduleResult($scheduleResult)
                    ->setStocks($stocks)
                    ->setStockResults($currentStockResults)
            );
        }

        return $results;
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
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
     * @throws SystemException
     * @return string
     */
    public function getPeriodType(): string
    {
        $this->getDeliveryDate();
        return parent::getPeriodType();
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
     * @param bool $internalCall
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
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

        /**
         * Фикс для праздников
         */
        $holidayDateMin = date_create_from_format('d.m.Y H:i:s', '31.12.2018 19:00:00');
        $holidayDateMax = date_create_from_format('d.m.Y H:i:s', '01.01.2019 23:59:59');
        if ($this->getDeliveryCode() == '4lapy_pickup' && $this->getDeliveryDate() >= $holidayDateMin && $this->getDeliveryDate() <= $holidayDateMax) {
            $this->deliveryDate = date_create_from_format('d.m.Y H:i:s', '02.01.2019 09:00:00');
        }

        return parent::isSuccess($internalCall);
    }

    /**
     * @return float
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     */
    public function getPrice(): float
    {
        $price = parent::getPrice();

        if ($this->getFreeFrom()) {
            $price = $this->getStockResult()->getPrice() >= $this->getFreeFrom() ? 0 : $price;
        }

        return $price;
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
                'RESULT'          => $calculationResult,
                'AVAILABLE_PRICE' => $calculationResult->getStockResult()->getAvailable()->getPrice(),
                'ORDERABLE_PRICE' => $calculationResult->getStockResult()->getOrderable()->getPrice(),
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
         * @throws ArgumentException
         * @throws ApplicationCreateException
         * @throws StoreNotFoundException
         * @throws SystemException
         * @return int
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
        $this->periodFrom = null;
        $this->shipmentResults = null;
    }

    /**
     * @param Offer $offer
     *
     * @return bool
     */
    protected function checkIsDeliverable(Offer $offer): bool
    {
        return (bool)$offer->getId();
    }

    public function __clone()
    {
        $this->stockResult = $this->stockResult instanceof StockResultCollection
            ? clone $this->stockResult
            : $this->stockResult;
        $this->deliveryDate = $this->deliveryDate instanceof \DateTime
            ? clone $this->deliveryDate
            : $this->deliveryDate;
        $this->currentDate = $this->currentDate instanceof \DateTime
            ? clone $this->currentDate
            : $this->currentDate;
    }
}
