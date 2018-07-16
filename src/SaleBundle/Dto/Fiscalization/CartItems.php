<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\Fiscalization;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

class CartItems
{
    /**
     * @var ArrayCollection
     *
     * @Serializer\SerializedName("items")
     * @Serializer\Type("ArrayCollection<FourPaws\SaleBundle\Dto\Fiscalization\Item>")
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