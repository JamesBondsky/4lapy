<?php

namespace FourPaws\Catalog\Query;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class ProductQuery extends IblockElementQuery
{
    /**
     * @inheritdoc
     */
    public function getBaseSelect(): array
    {
        return [
            'IBLOCK_ID',
            'ID',
            'NAME',
            'XML_ID',
            'CODE',
            'DETAIL_PAGE_URL',
            'SECTION_PAGE_URL',
            'LIST_PAGE_URL',
            'CANONICAL_PAGE_URL',
            'PROPERTY_BRAND',
            'PROPERTY_BRAND.NAME',
            'PROPERTY_FOR_WHO',
            'PROPERTY_PET_SIZE',
            'PROPERTY_PET_AGE',
            'PROPERTY_PET_AGE_ADDITIONAL',
            'PROPERTY_PET_BREED',
            'PROPERTY_PET_GENDER',
            'PROPERTY_CATEGORY',
            'PROPERTY_PURPOSE',
            'PROPERTY_IMG',
            'PROPERTY_LABEL',
            'PROPERTY_STM',
            'PROPERTY_COUNTRY',
            'PROPERTY_TRADE_NAME',
            'PROPERTY_MAKER',
            'PROPERTY_MANAGER_OF_CATEGORY',
            'PROPERTY_MANUFACTURE_MATERIAL',
            'PROPERTY_SEASON_CLOTHES',
            'PROPERTY_WEIGHT_CAPACITY_PACKING',
            'PROPERTY_LICENSE',
            'PROPERTY_LOW_TEMPERATURE',
            'PROPERTY_PET_TYPE',
            'PROPERTY_PHARMA_GROUP',
            'PROPERTY_FEED_SPECIFICATION',
            'PROPERTY_FOOD',
            'PROPERTY_CONSISTENCE',
            'PROPERTY_FLAVOUR',
            'PROPERTY_FEATURES_OF_INGREDIENTS',
            'PROPERTY_PRODUCT_FORM',
            'PROPERTY_TYPE_OF_PARASITE',
            'PROPERTY_YML_NAME',
            'PROPERTY_SALES_NOTES',
            'PROPERTY_GROUP',
            'PROPERTY_GROUP_NAME',
            'PROPERTY_PRODUCED_BY_HOLDER',
            'PROPERTY_SPECIFICATIONS',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getBaseFilter(): array
    {
        return ['IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)];
    }

    /**
     * @inheritdoc
     */
    public function exec(): CollectionBase
    {
        return new ProductCollection($this->doExec());
    }

}
