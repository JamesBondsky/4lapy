<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Entity;

use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\StoreBundle\Collection\StockCollection;
use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Entity\Stock;

/**
 * Class TmpDeliveryScheduleResult
 */
class DeliveryScheduleResult
{
    /**
     * @var ScheduleResult
     */
    protected $scheduleResult;

    /**
     * @var StockCollection
     */
    protected $stocks;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var StockResultCollection
     */
    protected $stockResults;

    /**
     * @var int[]
     */
    protected $deliverableAmounts = [];

    /**
     * @return ScheduleResult
     */
    public function getScheduleResult(): ScheduleResult
    {
        return $this->scheduleResult;
    }

    /**
     * @param ScheduleResult $scheduleResult
     *
     * @return DeliveryScheduleResult
     */
    public function setScheduleResult(ScheduleResult $scheduleResult): DeliveryScheduleResult
    {
        $this->scheduleResult = $scheduleResult;
        return $this;
    }

    /**
     * @return StockCollection
     */
    public function getStocks(): StockCollection
    {
        return $this->stocks;
    }

    /**
     * @param StockCollection $stocks
     * @return DeliveryScheduleResult
     */
    public function setStocks(StockCollection $stocks): DeliveryScheduleResult
    {
        $this->stocks = $stocks;
        $this->price = null;
        $this->deliverableAmounts = [];
        return $this;
    }

    /**
     * @return StockResultCollection
     */
    public function getStockResults(): StockResultCollection
    {
        return $this->stockResults;
    }

    /**
     * @param StockResultCollection $stockResults
     * @return DeliveryScheduleResult
     */
    public function setStockResults(StockResultCollection $stockResults): DeliveryScheduleResult
    {
        $this->stockResults = $stockResults;
        $this->price = null;
        $this->deliverableAmounts = [];
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        if (null === $this->price) {
            $price = 0;
            /** @var StockResult $stockResult */
            foreach ($this->getStockResults() as $stockResult) {
                $offer = $stockResult->getOffer();
                $amount = $this->getAmountByOffer($offer);
                $price += $amount * $offer->getPrice();
            }

            $this->price = $price;
        }

        return $this->price;
    }

    /**
     * @param Offer $offer
     * @return int
     */
    public function getAmountByOffer(Offer $offer): int
    {
        if (!isset($this->deliverableAmounts[$offer->getId()])) {
            $neededAmount = $this->getStockResults()->filterByOffer($offer)->getAmount();

            $stock = $this->getStocks()->filterByOffer($offer)->first();
            $availableAmount = $stock instanceof Stock ? $stock->getAmount() : 0;
            $this->deliverableAmounts[$offer->getId()] = $neededAmount > $availableAmount
                ? $availableAmount
                : $neededAmount;
        }

        return $this->deliverableAmounts[$offer->getId()];
    }
}
