<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Source;

use FourPaws\SapBundle\Dto\In\StoresStock\StockItem;
use FourPaws\SapBundle\Dto\In\StoresStock\StoresStock;

/**
 * Class CsvDirectorySource
 *
 * @package FourPaws\SapBundle\Source
 */
class CsvDirectorySource extends DirectorySource
{
    /**
     * @param $data
     *
     * @return StoresStock
     */
    protected function convert($data): StoresStock
    {
        /**
         * @todo
         * Костыли!
         * Пока разбирается только один тип CSV
         * Переделать на сереализацию/десериализацию!
         */
        $data = explode("\r\n", trim($data, "\r\n"));

        $stock = new StoresStock();
        $items = $stock->getItems();

        foreach ($data as $string) {
            $parsed = str_getcsv(
                $string,
                ';'
            );

            $items->add(
                (new StockItem())
                    ->setOfferXmlId($parsed[0])
                    ->setStoreCode($parsed[1])
                    ->setStockValue((float)$parsed[2])
            );
        }

        return $stock;
    }
}
