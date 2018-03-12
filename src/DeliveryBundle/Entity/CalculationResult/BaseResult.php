<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Entity\Store;

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
            $this->selectedStore = $this->getStockResult()->getStores()->first();
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
     */
    protected function doCalculateDeliveryDate(): void
    {
        $this->deliveryDate = clone $this->getCurrentDate();
        $modifier = 0;

        /**
         * Если есть отложенные товары, то добавляем к дате доставки
         * срок поставки на склад по графику
         */
        $modifier += $this->getStoreShipmentDate($this->getSelectedStore())->diff($this->currentDate)->days;

        if ($modifier > 0) {
            $this->deliveryDate->modify(sprintf('+%s days', $modifier));
        }
    }

    /**
     * Получение даты возможной доставки для указанного склада
     *
     * @param Store $store
     * @return \DateTime
     */
    protected function getStoreShipmentDate(Store $store): \DateTime
    {
        $stockResult = $this->getStockResult()->filterByStore($store);
        if ($stockResult->getDelayed()->isEmpty()) {
            return $this->getCurrentDate();
        }

        $shipmentDay = $this->getShipmentDay($store, $this->getCurrentDate());

        /**
         * Если день поставки нашелся, то выполняем расчет,
         * иначе ищем по графику поставок
         */
        if (null !== $shipmentDay) {
            $shipmentDay++;
        } else {
            /* @todo поиск в графике поставок */
            $shipmentDay = 0;
        }
        $date = clone $this->getCurrentDate();
        $date->modify(sprintf('+%s days', $shipmentDay));

        /**
         * Если склад является магазином, то учитываем его график работы
         */
        if ($store->isShop()) {
            $schedule = $store->getSchedule();
            $hour = (int)$date->format('G');
            if ($hour < $schedule->getFrom()) {
                $date->setTime($schedule->getFrom() + 1, 0);
            } elseif ($hour > $schedule->getTo()) {
                $date->modify('+1 day');
                $date->setTime($schedule->getFrom() + 1, 0);
            } else {
                $date->modify('+1 hour');
            }
        }

        /**
         * Если товар под заказ, то рассчитывается дата поставки на склад по графику
         */
        /* @todo расчет даты для товаров под заказ
         * if ($offer->isByRequest()) {
         * $stockResult->setType(StockResult::TYPE_DELAYED)
         * ->setDeliveryDate((new \DateTime())->modify('+10 days'));
         * continue;
         * }
         */

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

    public function __clone()
    {
        $this->deliveryDate = null;
    }
}
