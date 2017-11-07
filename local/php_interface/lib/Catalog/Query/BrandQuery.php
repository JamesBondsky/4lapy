<?php

namespace FourPaws\Catalog\Query;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Collection\BrandCollection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class BrandQuery extends IblockElementQuery
{
    /**
     * @inheritdoc
     */
    public function getBaseSelect(): array
    {
        return [
            'ACTIVE',
            'DATE_ACTIVE_FROM',
            'DATE_ACTIVE_TO',
            'IBLOCK_ID',
            'ID',
            'NAME',
            'XML_ID',
            'CODE',
            'DETAIL_PAGE_URL',
            'SECTION_PAGE_URL',
            'LIST_PAGE_URL',
            'CANONICAL_PAGE_URL',
            'PROPERTY_POPULAR',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getBaseFilter(): array
    {
        return ['IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS)];
    }

    /**
     * @inheritdoc
     */
    public function exec(): CollectionBase
    {
        return new BrandCollection($this->doExec());
    }

}
