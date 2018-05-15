<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\StoresStock;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class StoresStock
 *
 * @package FourPaws\SapBundle\Dto\In
 */
class StoresStock
{
    /**
     * Остатки
     *
     * @var Collection|StockItem[]
     */
    protected $items;

    /**
     * @return Collection|StockItem[]
     */
    public function getItems(): Collection
    {
        if (!$this->items) {
            $this->items = new ArrayCollection();
        }

        return $this->items;
    }

    /**
     * @param Collection $items
     *
     * @return StoresStock
     */
    public function setItems(Collection $items): StoresStock
    {
        $this->items = $items;

        return $this;
    }
}
