<?php

namespace FourPaws\Catalog\Query;

use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\D7QueryBase;
use FourPaws\Catalog\Collection\PriceCollection;
use FourPaws\Catalog\Exception\PriceNotFoundException;
use FourPaws\Catalog\Model\Price;
use FourPaws\Catalog\Table\CatalogPriceTable;
use LogicException;

class PriceQuery extends D7QueryBase
{
    public function __construct()
    {
        parent::__construct(CatalogPriceTable::query());
    }

    /**
     * @param string $regionId
     * @param int $elementId
     *
     * @return Price
     * @throws PriceNotFoundException
     */
    public function getPrice(string $regionId, int $elementId): Price
    {
        $priceCollection = $this->withFilter(
            [
                '=ELEMENT_ID' => $elementId,
                '=REGION_ID'  => $regionId,
            ]
        )->exec();

        if ($priceCollection->isEmpty()) {
            throw new PriceNotFoundException(
                sprintf(
                    'Не найдена цена элемента %d в регионе %s',
                    $elementId,
                    $regionId
                )
            );
        }

        if ($priceCollection->count() > 1) {
            throw new LogicException(
                sprintf(
                    'Найдено более одной цены для элемента %d в регионе %s',
                    $elementId,
                    $regionId
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
        return $this->withFilter(['=ELEMENT_ID' => $elementId])
                    ->exec();
    }

    /**
     * @inheritdoc
     */
    public function getBaseSelect(): array
    {
        return ['*'];
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
