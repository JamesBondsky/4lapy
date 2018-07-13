<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\Fiscalization;

use Doctrine\Common\Collections\ArrayCollection;

class CartItems
{
    /**
     * @var ArrayCollection
     */
    protected $items;

    /**
     * @return ArrayCollection
     */
    public function getItems(): ArrayCollection
    {
        return $this->items;
    }

    /**
     * @param ArrayCollection $items
     * @return CartItems
     */
    public function setItems(ArrayCollection $items): CartItems
    {
        $this->items = $items;
        return $this;
    }
}