<?php

namespace FourPaws\Catalog\Query;

use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\D7QueryBase;
use FourPaws\Catalog\Collection\PriceCollection;
use FourPaws\Catalog\Exception\PriceNotFoundException;
use Bitrix\Catalog\Model\Price;
use Bitrix\Catalog\PriceTable;
use LogicException;

class PriceQuery extends D7QueryBase
{
    public function __construct()
    {
        parent::__construct(PriceTable::query());
    }

    /**
     * @param string $regionId
     * @param int $elementId
     *
     * @return Price
     * @throws PriceNotFoundException
     */
    public function getPrice(int $elementId): Price
    {
        $priceCollection = $this->withFilter(
            [
                '=PRODUCT_ID' => $elementId,
                //'=CATALOG_GROUP_ID'  => $regionId,
            ]
        )->exec();

        if ($priceCollection->isEmpty()) {
            throw new PriceNotFoundException(
                sprintf(
                    'Не найдена цена элемента %d',
                    $elementId
                    //$regionId
                )
            );
        }

        if ($priceCollection->count() > 1) {
            throw new LogicException(
                sprintf(
                    'Найдено более одной цены для элемента %d',
                    $elementId
                    //$regionId
                )
            );
        }

        return $priceCollection->current();
    }

    /**
     * @param int $elementId
     *
     * @return PriceCollection
     */
    public function getAllPrices(int $elementId): PriceCollection
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->withFilter(['=PRODUCT_ID' => $elementId])
            ->exec();
    }

    /**
     * @inheritdoc
     */
    public function getBaseSelect(): array
    {
        return [
            'ID',
            'PRODUCT_ID',
            'CATALOG_GROUP_ID',
            'PRICE',
            'CURRENCY',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getBaseFilter(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function exec(): CollectionBase
    {
        return new PriceCollection($this->doExec());
    }

}
