<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\DeliveryScheduleService;

abstract class BaseResult extends CalculationResult
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

            $this->addErrors($result->getErrors());
            $this->addWarnings($result->getWarnings());
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
     * @return BaseResult
     */
    public function setCurrentDate(\DateTime $currentDate): BaseResult
    {
        $this->deliveryDate = null;
        $this->currentDate = $currentDate;

        return $this;
    }

    /**
     * @return \DateTime
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getDeliveryDate(): \DateTime
    {
        if (null === $this->deliveryDate) {
            $this->doCalculateDeliveryDate();
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
     * @return BaseResult
     */
    public function setDeliveryId(int $deliveryId): BaseResult
    {
        $this->deliveryDate = null;
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
     * @return BaseResult
     */
    public function setDeliveryCode(string $deliveryCode): BaseResult
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
     * @return BaseResult
     */
    public function setStockResult(StockResultCollection $stockResult): BaseResult
    {
        $this->deliveryDate = null;
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
     * @return BaseResult
     */
    public function setDeliveryName(string $deliveryName): BaseResult
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
     * @return BaseResult
     */
    public function setIntervals(IntervalCollection $intervals): BaseResult
    {
        $this->deliveryDate = null;
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
     * @return BaseResult
     */
    public function setFreeFrom(int $freeFrom): BaseResult
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
     * @return BaseResult
     */
    public function setDeliveryZone(string $deliveryZone): BaseResult
    {
        $this->deliveryDate = null;
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
     * @return BaseResult
     */
    public function setSelectedInterval(Interval $selectedInterval): BaseResult
    {
        $this->deliveryDate = null;
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
            /* @todo выбор наиболее подходящего склада/магазина */
            $this->setSelectedStore($this->getStockResult()->getStores()->first());
        }

        return $this->selectedStore;
    }

    /**
     * @param Store $selectedStore
     * @return BaseResult
     */
    public function setSelectedStore(Store $selectedStore): BaseResult
    {
        $this->deliveryDate = null;
        $this->selectedStore = $selectedStore;
        return $this;
    }

    /**
     * @throws NotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    protected function doCalculateDeliveryDate(): void
    {
        $store = $this->getSelectedStore();
        $stockResult = $this->getStockResult()->filterByStore($store);

        /**
         * Все товары в наличии
         */
        if ($stockResult->getDelayed()->isEmpty()) {
            $date = clone $this->getCurrentDate();
        } else {
            /**
             * Если есть отложенные товары, то добавляем к дате доставки
             * срок поставки на склад по графику
             */
            $date = $this->getStoreShipmentDate($store, $stockResult);
        }

        /**
         * Если склад является магазином, то учитываем его график работы
         */
        if ($store->isShop()) {
            $this->calculateWithStoreSchedule($date, $store);
        }

        $this->deliveryDate = $date;
    }

    /**
     * @param Store $store
     * @param StockResultCollection $stockResult
     * @return \DateTime
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    protected function getStoreShipmentDate(Store $store, StockResultCollection $stockResult): \DateTime
    {
        $date = clone $this->getCurrentDate();

        $modifier = 0;

        /** @var DeliveryScheduleService $deliveryScheduleService */
        $deliveryScheduleService = Application::getInstance()->getContainer()->get(DeliveryScheduleService::class);

        /**
         * Если есть товары под заказ, то рассчитывается дата поставки на склад по графику
         */
        $scheduleDays = [0];
        $hasRegularOffers = false;
        /** @var Offer $offer */
        foreach ($stockResult->getOffers() as $offer) {
            if (!$offer->isByRequest()) {
                $hasRegularOffers = true;
                continue;
            }

            /**
             * Для товаров под заказ добавляем +2 ко дню доставки
             */
            $scheduleDay = 2;

            /* @todo поиск в графике поставок с учетом складов поставщика */
//            $scheduleDay = $deliveryScheduleService->findByReceiver($this->getSelectedStore())->getNextDelivery($date);
            $scheduleDays[] = $scheduleDay;
        }
        $scheduleDay = max($scheduleDays);

        /**
         * Если есть товары из регулярного ассортимента, то рассчитываем их дату поставки
         */
        $shipmentDay = 0;
        if ($hasRegularOffers) {
            $day = $this->getShipmentDay($store, $date);
            /**
             * Если день поставки нашелся, то выполняем расчет,
             * иначе ищем по графику поставок
             */
            if (null !== $day) {
                /**
                 * По ТЗ мы должны добавить еще один день
                 */
                $shipmentDay += $day + 1;
            } else {
                if (($schedule = $deliveryScheduleService->findByReceiver($store)->getNextDelivery($date)) &&
                    ($scheduleDate = $schedule->getNextDelivery($date))
                ) {
                    $shipmentDay = $scheduleDate->diff($date)->days;
                } else {
                    $this->addError(new Error('Нет доступных графиков поставок'));
                }
            }
        }

        $modifier += max([$shipmentDay, $scheduleDay]);

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
     * Поиск ближайшего дня поставки по дням отгрузки в магазин
     * Возвращает кол-во дней до отгрузки
     *
     * @param Store $store
     * @param \DateTime $date
     * @return int|null
     */
    protected function getShipmentDay(Store $store, \DateTime $date): ?int
    {
        $items = [
            11 => $store->getShipmentTill11(),
            13 => $store->getShipmentTill13(),
            18 => $store->getShipmentTill18()
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
                if ($day === $currentDay) {
                    if ($currentHour < $maxHour) {
                        $results[] = 0;
                    } else {
                        $results[] = 7;
                    }
                    continue;
                }

                $diff = $day - $currentDay;
                $results[] = ($diff > 0) ? $diff : $diff + 7;
            }

            $results[] = min($res);
        }

        return empty($results) ? null : min($results);
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

    public function __clone()
    {
        $this->deliveryDate = null;
    }
}
