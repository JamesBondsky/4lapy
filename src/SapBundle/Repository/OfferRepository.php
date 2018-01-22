<?php

namespace FourPaws\SapBundle\Repository;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class OfferRepository extends IblockElementRepository
{
    /**
     * @return int
     */
    public function getIblockId(): int
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
    }

    /**
     * @return OfferQuery
     */
    protected function getQuery(): IblockElementQuery
    {
        return (new OfferQuery())
            ->withFilter([]);
    }
}
