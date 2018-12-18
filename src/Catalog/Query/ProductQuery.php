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

    public function getProperties(): array
    {
        return [
            'BRAND',
            'FOR_WHO',
            'PET_SIZE',
            'PET_AGE',
            'PET_AGE_ADDITIONAL',
            'PET_BREED',
            'PET_GENDER',
            'CATEGORY',
            'PURPOSE',
            'IMG',
            'LABEL',
            'STM',
            'COUNTRY',
            'TRADE_NAME',
            'MAKER',
            'MANAGER_OF_CATEGORY',
            'MANUFACTURE_MATERIAL',
            'SEASON_CLOTHES',
            'WEIGHT_CAPACITY_PACKING',
            'LICENSE',
            'LOW_TEMPERATURE',
            'PET_TYPE',
            'PHARMA_GROUP',
            'FEED_SPECIFICATION',
            'FOOD',
            'CONSISTENCE',
            'FLAVOUR',
            'FEATURES_OF_INGREDIENTS',
            'PRODUCT_FORM',
            'TYPE_OF_PARASITE',
            'YML_NAME',
            'SALES_NOTES',
            'GROUP',
            'GROUP_NAME',
            'GOOGLE_CATEGORY',
            'PRODUCED_BY_HOLDER',
            'SPECIFICATIONS',
            'COMPOSITION',
            'NORMS_OF_USE',
            'PACKING_COMBINATION',
            'LAYOUT_DESCRIPTION',
            'LAYOUT_COMPOSITION',
            'LAYOUT_RECOMMENDATIONS',
            'AQUARIUM_COMBINATION',
            'POWER_MIN',
            'POWER_MAX'
        ];
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
            'PROPERTY_BRAND.NAME',
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
