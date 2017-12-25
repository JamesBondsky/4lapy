<?php

namespace FourPaws\StoreBundle\Repository;

use Bitrix\Catalog\StoreTable;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;

class StoreRepository extends BaseRepository
{
    protected function getDataClass(): string
    {
        return StoreTable::class;
    }

    protected function getCollectionClass(): string
    {
        return StoreCollection::class;
    }

    protected function getEntityClass(): string
    {
        return Store::class;
    }

    protected function getDefaultFilter(): array
    {
        return ['ACTIVE' => 'Y'];
    }

    protected function getDefaultOrder(): array
    {
        return ['SORT' => 'ASC', 'ID' => 'ASC'];
    }
}
