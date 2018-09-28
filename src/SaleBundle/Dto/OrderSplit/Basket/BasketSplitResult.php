<?php

namespace FourPaws\SaleBundle\Dto\OrderSplit\Basket;

use Doctrine\Common\Collections\ArrayCollection;

class BasketSplitResult
{
    /**
     * @var ArrayCollection
     */
    protected $available;

    /**
     * @var ArrayCollection
     */
    protected $delayed;

    /**
     * @return ArrayCollection
     */
    public function getAvailable(): ArrayCollection
    {
        return $this->available;
    }

    /**
     * @param ArrayCollection $available
     * @return BasketSplitResult
     */
    public function setAvailable(ArrayCollection $available): BasketSplitResult
    {
        $this->available = $available;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDelayed(): ArrayCollection
    {
        return $this->delayed;
    }

    /**
     * @param ArrayCollection $delayed
     * @return BasketSplitResult
     */
    public function setDelayed(ArrayCollection $delayed): BasketSplitResult
    {
        $this->delayed = $delayed;

        return $this;
    }
}
