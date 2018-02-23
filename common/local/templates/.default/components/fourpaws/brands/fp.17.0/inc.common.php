<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Бренды: главная страница
 */

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

$arParams['CACHE_TIME'] = $arParams['CACHE_TIME'] ?? 43200;
$arParams['CACHE_TYPE'] = $arParams['CACHE_TYPE'] ?? 'A';

$arParams['RESIZE_WIDTH'] = $arParams['RESIZE_WIDTH'] ?? 115;
$arParams['RESIZE_HEIGHT'] = $arParams['RESIZE_HEIGHT'] ?? 43;
$arParams['RESIZE_TYPE'] = $arParams['RESIZE_TYPE'] ?? 'BX_RESIZE_IMAGE_PROPORTIONAL';

$arParams['IBLOCK_TYPE'] = $arParams['IBLOCK_TYPE'] ?? IblockType::CATALOG;
$arParams['IBLOCK_CODE'] = $arParams['IBLOCK_CODE'] ?? IblockCode::BRANDS;

// получим id брендов, у которых есть товары
$GLOBALS['arGetActualBrandsFilterExt'] = [
    '!PROPERTY_BRAND' => false,
    'PROPERTY_BRAND.ACTIVE' => 'Y',
    'PROPERTY_BRAND.ACTIVE_DATE' => 'Y',
];
$componentResult = $APPLICATION->IncludeComponent(
    'adv:system.iblock_data_list',
    '',
    [
        'CACHE_TYPE' => 'A',
        'CACHE_TIME' => 43200,
        'CACHE_GROUPS' => 'N',
        'ELEMENT_CNT' => 0,
        'PAGER_SHOW' => 'N',
        'ELEMENT_FILTER_NAME' => 'arGetActualBrandsFilterExt',
        'SORT_BY1' => '',
        'SORT_ORDER1' => '',
        'SORT_BY2' => '',
        'SORT_ORDER2' => '',
        'IBLOCKS' => [
            IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)
        ],
        'IBLOCK_CODES' => [],
        'GROUP_BY' => [
            'PROPERTY_BRAND'
        ],
        'FIELD_CODE' => [],
        'KEY_FIELD' => 'PROPERTY_BRAND_VALUE',
        'INCLUDE_TEMPLATE' => 'N',
        'CACHE_TEMPLATE' => 'Y',
        'CACHE_EMPTY_RESULT' => 'Y',
        'GET_NEXT_ELEMENT_MODE' => 'N',
        'GET_DISPLAY_PROPERTIES' => 'N',
        'CHECK_DATES' => 'Y',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y'
    ]
);
$actualBrandIds = $componentResult['ITEMS'] ? array_keys($componentResult['ITEMS']) : [];

$GLOBALS['arActualBrandsFilterExt'] = [];
if ($actualBrandIds) {
    $GLOBALS['arActualBrandsFilterExt']['ID'] = $actualBrandIds;
}


echo '<div class="b-container">';
echo '<h1 class="b-title b-title--h1 b-title--block b-title--catalog-h2">Бренды</h1>';
//
// Популярные бренды
//
$GLOBALS['arPopularBrandsFilterExt'] = $GLOBALS['arActualBrandsFilterExt'];
$GLOBALS['arPopularBrandsFilterExt']['=PROPERTY_POPULAR'] = 1;
$APPLICATION->IncludeComponent(
    'bitrix:news.list',
    'fp.17.0.popular',
    [
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
        'IBLOCK_ID' => $arParams['IBLOCK_CODE'],
        'SORT_BY1' => 'SORT',
        'SORT_ORDER1' => 'ASC',
        'SORT_BY2' => 'NAME',
        'SORT_ORDER2' => 'ASC',
        'FIELD_CODE' => [
            'NAME',
            'DETAIL_PICTURE',
        ],
        'FILTER_NAME' => 'arPopularBrandsFilterExt',
        'CACHE_FILTER' => 'Y',
        'CACHE_GROUPS' => 'N',
        'NEWS_COUNT' => '8',
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'DETAIL_URL' => '',

        'RESIZE_WIDTH' => $arParams['RESIZE_WIDTH'],
        'RESIZE_HEIGHT' => $arParams['RESIZE_HEIGHT'],
        'RESIZE_TYPE' => $arParams['RESIZE_TYPE'],

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
    $component,
    [
        'HIDE_ICONS' => 'Y'
    ]
);
echo '</div>';


echo '<div class="b-container b-container--brand-list">';
//
// Алфавитный указатель
//
$APPLICATION->IncludeComponent(
    'fourpaws:iblock.alphabetical.index',
    'fp.17.0.default',
    [
        'ELEMENT_FILTER_NAME' => 'arActualBrandsFilterExt',
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
        'IBLOCK_CODE' => $arParams['IBLOCK_CODE'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CHARS_COUNT' => 1,
        'TEMPLATE_NO_CACHE' => 'N',
        'LETTER_PAGE_URL' => $arResult['FOLDER'].'#LETTER_REDUCED#/',
    ],
    $component,
    [
        'HIDE_ICONS' => 'Y'
    ]
);

//
// Список всех брендов
//
$APPLICATION->IncludeComponent(
    'bitrix:news.list',
    'fp.17.0.list',
    [
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
        'IBLOCK_ID' => $arParams['IBLOCK_CODE'],
        'SORT_BY1' => 'SORT',
        'SORT_ORDER1' => 'ASC',
        'SORT_BY2' => 'NAME',
        'SORT_ORDER2' => 'ASC',
        'FIELD_CODE' => [
            'NAME',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE',
        ],
        'FILTER_NAME' => 'arActualBrandsFilterExt',
        'CACHE_FILTER' => 'Y',
        'CACHE_GROUPS' => 'N',
        'NEWS_COUNT' => '9999',
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CHECK_DATES' => 'Y',
        'DETAIL_URL' => '',

        'RESIZE_WIDTH' => $arParams['RESIZE_WIDTH'],
        'RESIZE_HEIGHT' => $arParams['RESIZE_HEIGHT'],
        'RESIZE_TYPE' => $arParams['RESIZE_TYPE'],

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
    $component,
    [
        'HIDE_ICONS' => 'Y'
    ]
);
echo '</div>';
