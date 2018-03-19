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
use FourPaws\StoreBundle\Collection\DeliveryScheduleResultCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
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
     * BaseResult constructor.
     *
     * @param null|CalculationResult $result
     */
    public function __construct(CalculationResult $result = null)
    {
        parent::__construct();

        if ($result) {
            $this->setDeliveryPrice($result->getDeliveryPrice());
            $this->setExtraServicesPrice($result->getExtraServicesPrice());
            $this->setDescription($result->getDescription());
            $this->setPacksCount($result->getPacksCount());

            if ($result->isNextStep()) {
                $this->setAsNextStep();
            }

            $this->setTmpData($result->getTmpData());
            $this->setData($result->getData());

            $this->setPeriodDescription($result->getPeriodDescription());
            $this->setPeriodFrom($result->getPeriodFrom());
            $this->setPeriodType($result->getPeriodType());
            $this->setPeriodTo($result->getPeriodTo());

            if ($result->getErrors()) {
                $this->addErrors($result->getErrors());
            }

            if ($result->getWarnings()) {
                $this->addWarnings($result->getWarnings());
            }
        }
    }

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
     * @throws NotFoundException
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
     * @return Interval
     */
    public function getSelectedInterval(): Interval
    {
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
     * @throws NotFoundException
     */
    public function getSelectedStore(): Store
    {
        if (!$this->selectedStore instanceof Store) {
            $this->setSelectedStore($this->getStockResult()->getStores()->first());
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
     * @throws NotFoundException
     * @throws StoreNotFoundException
     */
    protected function doCalculateDeliveryDate(): void
    {
        $date = clone $this->getCurrentDate();

        if (null !== $this->stockResult) {
            $stockResult = $this->getStockResult()->filterByStore($this->getSelectedStore());

            /**
             * Если есть отложенные товары, то добавляем к дате доставки
             * срок поставки на склад по графику
             */
            if (!$stockResult->getDelayed()->isEmpty()) {
                $date = $this->getStoreShipmentDate($this->getSelectedStore(), $stockResult->getDelayed());
            }

            /**
             * Если склад является магазином, то учитываем его график работы
             */
            if ($this->getSelectedStore()->isShop()) {
                $this->calculateWithStoreSchedule($date, $this->getSelectedStore());
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

        $modifier = 0;

        /** @var DeliveryScheduleService $scheduleService */
        $scheduleService = Application::getInstance()->getContainer()->get(DeliveryScheduleService::class);
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

        $shipmentDay = 0;
        /**
         * Если есть товары под заказ
         */
        if (!$byRequestStores->isEmpty()) {
            /**
             * Для товаров под заказ добавляем +2 ко дню доставки
             */
            $tmpDate = (clone $date)->modify(sprintf('+%s days', 2));

            if (!$hasRegular) {
                /**
                 * Если нет товаров из регулярного ассортимента, то находим скорейший маршрут
                 *  от складов поставщика до нужного склада/магазина
                 */

                $scheduleResult = $scheduleService->findBySenders($byRequestStores)
                    ->getNextDelivery(
                        $store,
                        $tmpDate
                    );

                if (null === $scheduleResult) {
                    $this->addError(new Error('Нет доступных графиков поставок'));
                    return $date;
                }

                $shipmentDay += $scheduleResult->getDate()->diff($date)->days;
            } else {
                /**
                 * Если есть товары из регулярного ассортимента, то находим маршруты
                 * от складов поставщика до складов, где есть требуемое кол-во данных товаров
                 */
                $scheduleResults = $scheduleService->findBySenders($byRequestStores)
                    ->getNextDeliveries(
                        $regularStores,
                        $tmpDate
                    );
                if ($scheduleResults->isEmpty()) {
                    $this->addError(new Error('Нет доступных графиков поставок'));
                    return $date;
                }

                /** @var DeliveryScheduleResult $senderResult */
                $collection = new DeliveryScheduleResultCollection();
                foreach ($scheduleResults as $senderResult) {
                    $schedules = $senderResult->getSchedule()->getReceiverSchedules();
                    if ($schedules->isEmpty()) {
                        continue;
                    }
                    if ($result = $schedules->getNextDelivery($store, $senderResult->getDate())) {
                        $collection->add($result);
                    }
                }

                if ($collection->isEmpty()) {
                    $this->addError(new Error('Нет доступных графиков поставок'));
                    return $date;
                }

                /** @noinspection NullPointerExceptionInspection */
                $shipmentDay += $collection->getFastest()->getDate()->diff($date)->days;
            }
            /**
             * Если есть только товары из регулярного ассортимента
             */
        } elseif (!$regularStores->isEmpty()) {
            $schedules = $scheduleService->findBySenders($regularStores);
            if ($schedules->isEmpty()) {
                $this->addError(new Error('Нет доступных графиков поставок'));
                return $date;
            }
            $day = $this->getShipmentDay($store, $date);

            if (null !== $day) {
                $shipmentDay += $day;
            } else {
                $scheduleResult = $schedules->getNextDelivery(
                    $store,
                    $date
                );
                if ($scheduleResult) {
                    $shipmentDay = $scheduleResult->getDate()->diff($date)->days;
                } else {
                    $this->addError(new Error('Нет доступных графиков поставок'));
                }
            }
        }

        $modifier += $shipmentDay;

        /**
         * Добавляем "срок поставки" к дате доставки
         */
        $modifier += $store->getDeliveryTime();

        /**
         * Вычисляем день доставки
         */
        $date->modify(sprintf('+%s days', $modifier));

        return $date;
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
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
     * @throws NotFoundException
     * @throws StoreNotFoundException
     * @return string
     */
    public function getPeriodType(): string
    {
        $this->getDeliveryDate();
        return parent::getPeriodType();
    }

    /**
     * @param bool $internalCall
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
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
     * Поиск ближайшего дня поставки по дням отгрузки в магазин
     * Возвращает кол-во дней до отгрузки
     *
     * @param Store $store
     * @param \DateTime $date
     * @return null|int
     */
    protected function getShipmentDay(Store $store, \DateTime $date): ?int
    {
        $items = [
            11 => $store->getShipmentTill11(),
            13 => $store->getShipmentTill13(),
            18 => $store->getShipmentTill18(),
        ];

        $currentDay = (int)$date->format('w');
        $currentHour = (int)$date->format('G');
        $results = [];

        /**
         * @var int $maxHour
         * @var array $days
         */
        foreach ($items as $maxHour => $days) {
            if (empty($days)) {
                continue;
            }

            $res = [];
            foreach ($days as $day) {
                /**
                 * Если текущий день является днем отгрузки
                 */
                if ($day === $currentDay) {
                    /**
                     * Если текущий час меньше времени окончания отгрузки,
                     * то отгрузка в текущий день, иначе - через неделю
                     */
                    if ($currentHour < $maxHour) {
                        $res[] = 0;
                    } else {
                        $res[] = 7;
                    }
                    continue;
                }

                $diff = $day - $currentDay;
                /**
                 * если diff < 0, то поставка на следующей неделе, соответственно, добавляем 7 дней
                 */
                $res[] = ($diff > 0) ? $diff : $diff + 7;
            }

            $results[] = min($res);
        }

        /**
         * Если найден результат, то добавляем к нему 1 день (по ТЗ)
         */
        return empty($results) ? null : min($results) + 1;
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
        $this->deliveryDate = null;
        $this->errors = new ErrorCollection();
        $this->wereErrorsChecked = false;
        $this->isSuccess = true;
        $this->warnings = new ErrorCollection();
    }

    public function __clone()
    {
        $this->resetResult();
    }
}
