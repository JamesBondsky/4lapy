<?php

namespace FourPaws\Catalog\Query;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class OfferQuery extends IblockElementQuery
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
            'PROPERTY_CML2_LINK',
            'PROPERTY_COLOUR',
            'PROPERTY_VOLUME_REFERENCE',
            'PROPERTY_VOLUME',
            'PROPERTY_CLOTHING_SIZE',
            'PROPERTY_IMG',
            'PROPERTY_BARCODE',
            'PROPERTY_KIND_OF_PACKING',
            'PROPERTY_SEASON_YEAR',
            'PROPERTY_MULTIPLICITY',
            'PROPERTY_REWARD_TYPE',
            'PROPERTY_COLOUR_COMBINATION',
            'PROPERTY_FLAVOUR_COMBINATION',
            'PROPERTY_OLD_URL',
            'PROPERTY_BY_REQUEST',
            'CATALOG_GROUP_2',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getBaseFilter(): array
    {
        return ['IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS)];
    }

    /**
     * @inheritdoc
     */
    public function exec(): CollectionBase
    {
        return new OfferCollection($this->doExec());
    }
}
