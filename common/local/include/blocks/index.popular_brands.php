<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок на главной странице: популярные бренды
 */

/** @global CMain $APPLICATION */

// получим id популярных брендов, у которых есть товары
$GLOBALS['arHomePopularBrandsFilterExt'] = [
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
        'ELEMENT_CNT' => 10,
        'PAGER_SHOW' => 'N',
        'ELEMENT_FILTER_NAME' => 'arHomePopularBrandsFilterExt',
        'SORT_BY1' => 'PROPERTY_BRAND.PROPERTY_POPULAR',
        'SORT_ORDER1' => 'DESC',
        'SORT_BY2' => 'PROPERTY_BRAND.SORT',
        'SORT_ORDER2' => 'ASC',
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
if ($componentResult['ITEMS']) {
    // по полученному списку создаем внешний фильтр
    $GLOBALS['arHomePopularBrandsFilterExt'] = [
        'ID' => array_keys($componentResult['ITEMS']),
    ];
    echo '<section class="b-common-section">';
    $APPLICATION->IncludeComponent(
        'bitrix:news.list',
        'fp.17.0.popular_brands_home',
        [
            'IBLOCK_TYPE'  => IblockType::CATALOG,
            'IBLOCK_ID'    => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS),
            'SORT_BY1'     => 'SORT',
            'SORT_ORDER1'  => 'ASC',
            'SORT_BY2'     => 'NAME',
            'SORT_ORDER2'  => 'ASC',
            'FIELD_CODE'   => [
                'NAME',
                'PREVIEW_PICTURE',
                'DETAIL_PICTURE',
            ],
            'FILTER_NAME'  => 'arHomePopularBrandsFilterExt',
            'CACHE_FILTER' => 'Y',
            'CACHE_GROUPS' => 'N',
            'NEWS_COUNT'   => 10,
            'CACHE_TIME'   => 43200,
            'CACHE_TYPE'   => 'A',
            'DETAIL_URL'   => '',

            'RESIZE_WIDTH'  => '195',
            'RESIZE_HEIGHT' => '69',
            'RESIZE_TYPE'   => 'BX_RESIZE_IMAGE_PROPORTIONAL',

            'ACTIVE_DATE_FORMAT'              => 'd.m.Y',
            'ADD_SECTIONS_CHAIN'              => 'N',
            'AJAX_MODE'                       => 'N',
            'AJAX_OPTION_ADDITIONAL'          => '',
            'AJAX_OPTION_HISTORY'             => 'N',
            'AJAX_OPTION_JUMP'                => 'N',
            'AJAX_OPTION_STYLE'               => 'N',
            'HIDE_LINK_WHEN_NO_DETAIL'        => 'N',
            'INCLUDE_IBLOCK_INTO_CHAIN'       => 'N',
            'INCLUDE_SUBSECTIONS'             => 'N',
            'PAGER_BASE_LINK_ENABLE'          => 'N',
            'PAGER_DESC_NUMBERING'            => 'N',
            'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
            'PAGER_SHOW_ALL'                  => 'N',
            'PAGER_SHOW_ALWAYS'               => 'N',
            'PAGER_TEMPLATE'                  => '',
            'PAGER_TITLE'                     => '',
            'PARENT_SECTION'                  => '',
            'PARENT_SECTION_CODE'             => '',
            'PREVIEW_TRUNCATE_LEN'            => '',
            'PROPERTY_CODE'                   => [],
            'SET_BROWSER_TITLE'               => 'N',
            'SET_LAST_MODIFIED'               => 'N',
            'SET_META_DESCRIPTION'            => 'N',
            'SET_META_KEYWORDS'               => 'N',
            'SET_STATUS_404'                  => 'N',
            'SET_TITLE'                       => 'N',
            'SHOW_404'                        => 'N',
        ],
        null,
        [
            'HIDE_ICONS' => 'Y',
        ]
    );
    echo '</section>';
}
