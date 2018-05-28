<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Entity\PriceForAmount;

class PriceForAmountCollection extends ArrayCollection
{
    public function getPrice(): float
    {
        $result = 0;
        /** @var PriceForAmount $item */
        foreach ($this->getIterator() as $item) {
            $result += $item->getAmount() * $item->getPrice();
        }

        return $result;
    }

    public function getAmount(): int
    {
        $result = 0;
        /** @var PriceForAmount $item */
        foreach ($this->getIterator() as $item) {
            $result += $item->getAmount();
        }

        return $result;
    }

    public function __clone()
    {
        foreach ($this->getIterator() as $i => $item) {
            $this[$i] = clone $item;
        }
    }
}