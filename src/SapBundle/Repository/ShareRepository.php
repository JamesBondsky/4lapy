<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Repository;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\BitrixOrm\Query\ShareQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * Class ShareRepository
 *
 * @package FourPaws\SapBundle\Repository
 */
class ShareRepository extends IblockElementRepository
{
    /**
     * @throws IblockNotFoundException
     *
     * @return int
     */
    public function getIblockId(): int
    {
        return IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::SHARES);
    }

    /**
     * @return IblockElementQuery
     */
    protected function getQuery(): IblockElementQuery
    {
        return (new ShareQuery())
            ->withFilter([]);
    }
}
