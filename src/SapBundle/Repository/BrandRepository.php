<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Repository;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Query\BrandQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class BrandRepository extends IblockElementRepository
{

    /**
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @return int
     */
    public function getIblockId(): int
    {
        return IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS);
    }

    /**
     * @return IblockElementQuery
     */
    protected function getQuery(): IblockElementQuery
    {
        return (new BrandQuery())
            ->withFilter([]);
    }
}
