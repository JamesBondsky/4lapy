<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;

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
     * BaseResult constructor.
     *
     * @param CalculationResult|null $result
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
        if ($this->deliveryDate) {
            $this->deliveryDate = $this->doCalculateDeliveryDate();
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
     * @return \DateTime
     */
    protected function doCalculateDeliveryDate()
    {
        $deliveryDate = clone $this->getCurrentDate();
        $modifier = 0;

        /**
         * Если есть отложенные товары, то добавляем к дате доставки DPD
         * срок поставки на склад по графику
         */
        if (!$this->getStockResult()->getDelayed()->isEmpty()) {
            $modifier += $this->getStockResult()
                              ->getDeliveryDate()
                              ->diff($this->getCurrentDate())->days;
        }

        if ($modifier > 0) {
            $deliveryDate->modify(sprintf('+%s days', $modifier));
        }

        return $deliveryDate;
    }

    public function __clone()
    {
        $this->deliveryDate = null;
    }
}
