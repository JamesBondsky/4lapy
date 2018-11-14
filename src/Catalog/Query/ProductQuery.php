<?php

namespace FourPaws\Catalog\Query;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Catalog\Model\Product;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use WebArch\BitrixCache\BitrixCache;

class ProductQuery extends IblockElementQuery
{
    /** кешируем на неделю  */
    protected const CACHE_TIME_BY_ID = 7 * 24 * 60 * 60;

    public static function getActiveAccessableElementsFilter(): array
    {
        $array = parent::getActiveAccessableElementsFilter();
        $array['SECTION_GLOBAL_ACTIVE'] = 'Y';
        return $array;
    }

    /**
     * @param int $id
     *
     * @return Product|null
     */
    public static function getById(int $id = 0): ?Product
    {
        if ($id <= 0) {
            /** @todo вместо null выбивать exception */
            return null;
        }
        $query = new static();
        $getProduct = function () use ($id, $query) {
            $collection = $query->withFilter(['ID' => $id])->exec();

            return !$collection->isEmpty() ? $collection->first() : null;
        };
        $bitrixCache = new BitrixCache();
        $bitrixCache->withId('product_' . $id);
        $bitrixCache->withTag('catalog:product:' . $id);
        $bitrixCache->withTag('iblock:item:' . $id);
        $bitrixCache->withTime(static::CACHE_TIME_BY_ID);
        try {
            return $bitrixCache->resultOf($getProduct)['result'];
        } catch (\Exception $e) {
            /** @todo вместо null выбивать exception */
            $logger = LoggerFactory::create('product');
            $logger->warning('ошибка получения товара по id - ' . $id . ': ' . $e->getMessage());
            return null;
        }
    }

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
            'IBLOCK_SECTION_ID',
            'NAME',
            'XML_ID',
            'CODE',
            'SORT',
            'DETAIL_PAGE_URL',
            'SECTION_PAGE_URL',
            'LIST_PAGE_URL',
            'CANONICAL_PAGE_URL',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
//            'PROPERTY_BRAND',
            'PROPERTY_BRAND.NAME',
//            'PROPERTY_FOR_WHO',
//            'PROPERTY_PET_SIZE',
//            'PROPERTY_PET_AGE',
//            'PROPERTY_PET_AGE_ADDITIONAL',
//            'PROPERTY_PET_BREED',
//            'PROPERTY_PET_GENDER',
//            'PROPERTY_CATEGORY',
//            'PROPERTY_PURPOSE',
//            'PROPERTY_IMG',
//            'PROPERTY_LABEL',
//            'PROPERTY_STM',
//            'PROPERTY_COUNTRY',
//            'PROPERTY_TRADE_NAME',
//            'PROPERTY_MAKER',
//            'PROPERTY_MANAGER_OF_CATEGORY',
//            'PROPERTY_MANUFACTURE_MATERIAL',
//            'PROPERTY_SEASON_CLOTHES',
//            'PROPERTY_WEIGHT_CAPACITY_PACKING',
//            'PROPERTY_LICENSE',
//            'PROPERTY_LOW_TEMPERATURE',
//            'PROPERTY_PET_TYPE',
//            'PROPERTY_PHARMA_GROUP',
//            'PROPERTY_FEED_SPECIFICATION',
//            'PROPERTY_FOOD',
//            'PROPERTY_CONSISTENCE',
//            'PROPERTY_FLAVOUR',
//            'PROPERTY_FEATURES_OF_INGREDIENTS',
//            'PROPERTY_PRODUCT_FORM',
//            'PROPERTY_TYPE_OF_PARASITE',
//            'PROPERTY_YML_NAME',
//            'PROPERTY_SALES_NOTES',
//            'PROPERTY_GROUP',
//            'PROPERTY_GROUP_NAME',
//            'PROPERTY_GOOGLE_CATEGORY',
//            'PROPERTY_PRODUCED_BY_HOLDER',
//            'PROPERTY_SPECIFICATIONS',
//            'PROPERTY_COMPOSITION',
//            'PROPERTY_NORMS_OF_USE',
//            'PROPERTY_PACKING_COMBINATION',
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
     * @return ProductCollection|CollectionBase
     */
    public function exec(): CollectionBase
    {
        return new ProductCollection($this->doExec());
    }
}
