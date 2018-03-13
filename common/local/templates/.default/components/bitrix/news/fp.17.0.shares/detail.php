<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Страница карточки акции в разделе Акции
 *
 * @updated: 12.01.2018
 */

/** @global $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponent $component */

$APPLICATION->SetPageProperty('PUBLICATION_DETAIL_CONTAINER_1', 'b-container b-container--news-detail');
$APPLICATION->SetPageProperty('PUBLICATION_DETAIL_CONTAINER_2', 'b-detail-page');

$this->setFrameMode(true);

$elementId = $APPLICATION->IncludeComponent(
    'bitrix:news.detail',
    '',
    [
        'IBLOCK_TYPE'               => $arParams['IBLOCK_TYPE'],
        'IBLOCK_ID'                 => $arParams['IBLOCK_ID'],
        'FIELD_CODE'                => $arParams['DETAIL_FIELD_CODE'],
        'PROPERTY_CODE'             => $arParams['DETAIL_PROPERTY_CODE'],
        'IBLOCK_URL'                => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['news'],
        'DETAIL_URL'                => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['detail'],
        'SECTION_URL'               => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['section'],
        'META_KEYWORDS'             => $arParams['META_KEYWORDS'],
        'META_DESCRIPTION'          => $arParams['META_DESCRIPTION'],
        'BROWSER_TITLE'             => $arParams['BROWSER_TITLE'],
        'SET_CANONICAL_URL'         => $arParams['DETAIL_SET_CANONICAL_URL'],
        'DISPLAY_PANEL'             => $arParams['DISPLAY_PANEL'],
        'SET_LAST_MODIFIED'         => $arParams['SET_LAST_MODIFIED'],
        'SET_TITLE'                 => $arParams['SET_TITLE'],
        'MESSAGE_404'               => $arParams['MESSAGE_404'],
        'SET_STATUS_404'            => $arParams['SET_STATUS_404'],
        'SHOW_404'                  => $arParams['SHOW_404'],
        'FILE_404'                  => $arParams['FILE_404'],
        'INCLUDE_IBLOCK_INTO_CHAIN' => $arParams['INCLUDE_IBLOCK_INTO_CHAIN'],
        'ADD_SECTIONS_CHAIN'        => $arParams['ADD_SECTIONS_CHAIN'],
        'ACTIVE_DATE_FORMAT'        => $arParams['DETAIL_ACTIVE_DATE_FORMAT'],
        'CACHE_TYPE'                => $arParams['CACHE_TYPE'],
        'CACHE_TIME'                => $arParams['CACHE_TIME'],
        'CACHE_GROUPS'              => $arParams['CACHE_GROUPS'],
        'USE_PERMISSIONS'           => $arParams['USE_PERMISSIONS'],
        'GROUP_PERMISSIONS'         => $arParams['GROUP_PERMISSIONS'],
        'DISPLAY_TOP_PAGER'         => $arParams['DETAIL_DISPLAY_TOP_PAGER'],
        'DISPLAY_BOTTOM_PAGER'      => $arParams['DETAIL_DISPLAY_BOTTOM_PAGER'],
        'PAGER_TITLE'               => $arParams['DETAIL_PAGER_TITLE'],
        'PAGER_SHOW_ALWAYS'         => 'N',
        'PAGER_TEMPLATE'            => $arParams['DETAIL_PAGER_TEMPLATE'],
        'PAGER_SHOW_ALL'            => $arParams['DETAIL_PAGER_SHOW_ALL'],
        'CHECK_DATES'               => $arParams['CHECK_DATES'],
        'ELEMENT_ID'                => $arResult['VARIABLES']['ELEMENT_ID'],
        'ELEMENT_CODE'              => $arResult['VARIABLES']['ELEMENT_CODE'],
        'SECTION_ID'                => $arResult['VARIABLES']['SECTION_ID'],
        'SECTION_CODE'              => $arResult['VARIABLES']['SECTION_CODE'],
        'USE_SHARE'                 => $arParams['USE_SHARE'],
        'ADD_ELEMENT_CHAIN'         => $arParams['ADD_ELEMENT_CHAIN'],
        'STRICT_SECTION_CHECK'      => $arParams['STRICT_SECTION_CHECK'],
    ],
    $component,
    [
        'HIDE_ICONS' => 'Y',
    ]
);

/**
 * Распродажа
 */
if (isset($arParams['SHOW_PRODUCTS_SALE']) && $arParams['SHOW_PRODUCTS_SALE'] === 'Y') {
    global $params, $elId;
    $elId = $elementId;
    $params = $arParams;
    $APPLICATION->IncludeFile(
        'blocks/components/sale_products.php',
        [],
        [
            'SHOW_BORDER' => false,
            'NAME'        => 'Блок распродажи товаров',
            'MODE'        => 'php',
        ]
    );
}

/**
 * Рассказать в соцсетях
 */
if (isset($arParams['USE_SHARE']) && $arParams['USE_SHARE'] === 'Y') {
    $APPLICATION->IncludeFile(
        'blocks/components/social_share.php',
        [],
        [
            'SHOW_BORDER' => false,
            'NAME'        => 'Блок Рассказать в соцсетях',
            'MODE'        => 'php',
        ]
    );
}
