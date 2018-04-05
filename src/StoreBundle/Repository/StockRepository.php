<?php

namespace FourPaws\StoreBundle\Repository;

use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\SystemException;
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
        return [
            '>AMOUNT' => 0,
            'STORE.ACTIVE' => 'Y'
        ];
    }

    protected function getDefaultOrder(): array
    {
        return ['ID' => 'ASC'];
    }

    /**
     * @param Query $query
     * @return Query
     * @throws ArgumentException
     * @throws SystemException
     */
    protected function modifyQuery(Query $query): Query
    {
        $query->registerRuntimeField(
            new ReferenceField(
                'STORE',
                StoreTable::class,
                ['=this.STORE_ID' => 'ref.ID'],
                ['join_type' => 'INNER'])
        );

        return parent::modifyQuery($query);
    }
}
