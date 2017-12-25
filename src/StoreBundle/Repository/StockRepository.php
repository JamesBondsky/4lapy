<?php

namespace FourPaws\StoreBundle\Repository;

use Bitrix\Catalog\StoreProductTable;
use FourPaws\StoreBundle\Collection\StockCollection;
use FourPaws\StoreBundle\Entity\Stock;

class StockRepository extends BaseRepository
{
    protected function getDataClass(): string
    {
        return StoreProductTable::class;
    }

    protected function getCollectionClass(): string
    {
        return StockCollection::class;
    }

    protected function getEntityClass(): string
    {
        return Stock::class;
    }

    protected function getDefaultFilter(): array
    {
        return [];
    }

    protected function getDefaultOrder(): array
    {
        return ['ID' => 'ASC'];
    }
}
