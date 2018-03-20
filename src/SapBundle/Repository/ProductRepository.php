<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Repository;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class ProductRepository extends IblockElementRepository
{
    /**
     * @return int
     */
    public function getIblockId(): int
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
    }

    /**
     * @return IblockElementQuery
     */
    protected function getQuery(): IblockElementQuery
    {
        return (new ProductQuery())
            ->withFilter([]);
    }
}
