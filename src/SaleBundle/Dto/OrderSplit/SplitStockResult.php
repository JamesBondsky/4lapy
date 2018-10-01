<?php

namespace FourPaws\SaleBundle\Dto\OrderSplit;

use FourPaws\DeliveryBundle\Collection\StockResultCollection;

class SplitStockResult
{
    /**
     * @var StockResultCollection
     */
    protected $available;

    /**
     * @var StockResultCollection
     */
    protected $delayed;

    /**
     * @return StockResultCollection
     */
    public function getAvailable(): StockResultCollection
    {
        return $this->available;
    }

    /**
     * @param StockResultCollection $available
     * @return SplitStockResult
     */
    public function setAvailable(StockResultCollection $available): SplitStockResult
    {
        $this->available = $available;

        return $this;
    }

    /**
     * @return StockResultCollection
     */
    public function getDelayed(): StockResultCollection
    {
        return $this->delayed;
    }

    /**
     * @param StockResultCollection $delayed
     * @return SplitStockResult
     */
    public function setDelayed(StockResultCollection $delayed): SplitStockResult
    {
        $this->delayed = $delayed;

        return $this;
    }
}
