<?php

use FourPaws\Catalog\Model\Filter\BrandFilter;
use FourPaws\Catalog\Model\Filter\SectionFilter;
use FourPaws\Catalog\Model\Variant;
use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @global $APPLICATION */
/** @var array $arParams */

if (!isset($GLOBALS['arCatalogPopularBrandsFilterExt'])) {
    $GLOBALS['arCatalogPopularBrandsFilterExt'] = [];
}
$GLOBALS['arCatalogPopularBrandsFilterExt']['=PROPERTY_POPULAR'] = 1;

$isFilterSet = true;
if (isset($arParams['categoryRequest'])) {
    $isFilterSet = false;
    $categoryRequest = $arParams['categoryRequest'];
    if ($categoryRequest instanceof CatalogCategorySearchRequestInterface) {
        /** @var CatalogCategorySearchRequestInterface $categoryRequest */
        $filterCollection = $categoryRequest->getCategory()->getFilters();
        /** @var BrandFilter $brandFilter */
        $brandFilter = $filterCollection->filter(
            function ($collectionItem) {
                return $collectionItem instanceof BrandFilter;
            }
        )->first();

        if ($brandFilter && $brandFilter->hasAvailableVariants()) {
            $isFilterSet = true;
            $GLOBALS['arCatalogPopularBrandsFilterExt']['=CODE'] = [];
            foreach ($brandFilter->getAvailableVariants() as $item) {
                /** @var Variant $item */
                $GLOBALS['arCatalogPopularBrandsFilterExt']['=CODE'][] = $item->getValue();
            }
            $GLOBALS['arCatalogPopularBrandsFilterExt']['=CODE'] = array_unique($GLOBALS['arCatalogPopularBrandsFilterExt']['=CODE']);
            // Ссылки должны вести на карточку бренда с предустановленным фильтром по разделу,
            // за это там отвечает фильтр \FourPaws\Catalog\Model\Filter\SectionFilter
            // сгенерируем ссылку
            $filterCode = (new SectionFilter())->getFilterCode();
            $arParams['ADD_URL_PARAMS'] = $filterCode.'='.$categoryRequest->getCategory()->getId();
        }
    }
}

if (!$isFilterSet) {
    return;
}

$APPLICATION->IncludeComponent('bitrix:news.list',
    'fp.17.0.popular_brands_catalog',
    [
        'RESIZE_WIDTH' => $arParams['RESIZE_WIDTH'] ?? '226',
        'RESIZE_HEIGHT' => $arParams['RESIZE_HEIGHT'] ?? '101',
        'RESIZE_TYPE' => $arParams['RESIZE_TYPE'] ?? 'BX_RESIZE_IMAGE_PROPORTIONAL',

        'ADD_URL_PARAMS' => $arParams['ADD_URL_PARAMS'] ?? '',

        'IBLOCK_TYPE' => IblockType::CATALOG,
        'IBLOCK_ID' => IblockCode::BRANDS,
        'SORT_BY1' => 'SORT',
        'SORT_ORDER1' => 'ASC',
        'SORT_BY2' => 'NAME',
        'SORT_ORDER2' => 'ASC',
        'FIELD_CODE' => [
            'NAME',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE',
        ],
        'FILTER_NAME' => 'arCatalogPopularBrandsFilterExt',
        'CACHE_FILTER' => 'Y',
        'CACHE_GROUPS' => 'N',
        'NEWS_COUNT' => $arParams['ITEMS_COUNT'] ?? '8',
        'CACHE_TIME' => '43200',
        'CACHE_TYPE' => 'A',
        'DETAIL_URL' => $arParams['DETAIL_URL'] ?? '',
        'ACTIVE_DATE_FORMAT' => 'd.m.Y',
        'ADD_SECTIONS_CHAIN' => 'N',
        'AJAX_MODE' => 'N',
        'AJAX_OPTION_ADDITIONAL' => '',
        'AJAX_OPTION_HISTORY' => 'N',
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_STYLE' => 'N',
        'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
        'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
        'INCLUDE_SUBSECTIONS' => 'N',
        'PAGER_BASE_LINK_ENABLE' => 'N',
        'PAGER_DESC_NUMBERING' => 'N',
        'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
        'PAGER_SHOW_ALL' => 'N',
        'PAGER_SHOW_ALWAYS' => 'N',
        'PAGER_TEMPLATE' => '',
        'PAGER_TITLE' => '',
        'PARENT_SECTION' => '',
        'PARENT_SECTION_CODE' => '',
        'PREVIEW_TRUNCATE_LEN' => '',
        'PROPERTY_CODE' => [],
        'SET_BROWSER_TITLE' => 'N',
        'SET_LAST_MODIFIED' => 'N',
        'SET_META_DESCRIPTION' => 'N',
        'SET_META_KEYWORDS' => 'N',
        'SET_STATUS_404' => 'N',
        'SET_TITLE' => 'N',
        'SHOW_404' => 'N',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);
