<?php

namespace FourPaws\SapBundle\Dto\In\StoresStock;

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
