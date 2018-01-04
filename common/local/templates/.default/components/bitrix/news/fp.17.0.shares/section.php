<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Страница списка акций в разделе Акции
 *
 * @updated: 01.01.2018
 */
$this->setFrameMode(true);

$APPLICATION->SetPageProperty('PUBLICATION_LIST_CONTAINER_1', 'b-container b-container--promo');
$APPLICATION->SetPageProperty('PUBLICATION_LIST_CONTAINER_2', 'b-promo');

$arParams['RESIZE_WIDTH'] = isset($arParams['RESIZE_WIDTH']) ? $arParams['RESIZE_WIDTH'] : '305';
$arParams['RESIZE_HEIGHT'] = isset($arParams['RESIZE_HEIGHT']) ? $arParams['RESIZE_HEIGHT'] : '160';
$arParams['RESIZE_TYPE'] = isset($arParams['RESIZE_TYPE']) ? $arParams['RESIZE_TYPE'] : 'BX_RESIZE_IMAGE_EXACT';

if (isset($arParams['USE_FILTER']) && $arParams['USE_FILTER'] === 'Y') {
    $arParams['FILTER_NAME'] = 'arSharesFilterExt';
    // фильтр по видам питомцев
    $APPLICATION->IncludeComponent(
        'adv:system.iblock_data_list',
        'fp.17.0.filter',
        [
            'FILTER_PROPERTY_CODE' => 'TYPE',
            // параметр c тильдой не будет влиять на кэш
            '~SELECTED_VALUE' => isset($arResult['VARIABLES']['SECTION_CODE']) ? $arResult['VARIABLES']['SECTION_CODE'] : '',
            'ALL_URL' => $arResult['FOLDER'],
            'URL_TEMPLATE' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['section'],

            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
            'CACHE_TIME' => $arParams['CACHE_TIME'],
            'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
            'ELEMENT_CNT' => 0,
            'PAGER_SHOW' => 'N',
            'ELEMENT_FILTER_NAME' => $arParams['FILTER_NAME'],
            'SORT_BY1' => '',
            'SORT_ORDER1' => '',
            'SORT_BY2' => '',
            'SORT_ORDER2' => '',
            'IBLOCKS' => intval($arParams['IBLOCK_ID']) > 0 ? [$arParams['IBLOCK_ID']] : [],
            'IBLOCK_CODES' => is_string($arParams['IBLOCK_ID']) ? [$arParams['IBLOCK_ID']] : [],
            'GROUP_BY' => [
                'PROPERTY_TYPE',
            ],
            'FIELD_CODE' => [],
            'KEY_FIELD' => '',
            'INCLUDE_TEMPLATE' => 'Y',
            'CACHE_TEMPLATE' => 'Y',
            'CACHE_EMPTY_RESULT' => 'Y',
            'GET_NEXT_ELEMENT_MODE' => 'Y',
            'GET_DISPLAY_PROPERTIES' => 'N',
            'CHECK_DATES' => $arParams['CHECK_DATES'],
        ],
        $component,
        [
            'HIDE_ICONS' => 'Y'
        ]
    );
}

// список акций
$APPLICATION->IncludeComponent(
    'bitrix:news.list',
    '',
    [
        'RESIZE_WIDTH' => $arParams['RESIZE_WIDTH'],
        'RESIZE_HEIGHT' => $arParams['RESIZE_HEIGHT'],
        'RESIZE_TYPE' => $arParams['RESIZE_TYPE'],
        'DEFAULT_PUBLICATION_TYPE_VALUE' => isset($arParams['DEFAULT_PUBLICATION_TYPE_VALUE']) ? $arParams['DEFAULT_PUBLICATION_TYPE_VALUE'] : '',

        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'NEWS_COUNT' => $arParams['NEWS_COUNT'],
        'SORT_BY1' => $arParams['SORT_BY1'],
        'SORT_ORDER1' => $arParams['SORT_ORDER1'],
        'SORT_BY2' => $arParams['SORT_BY2'],
        'SORT_ORDER2' => $arParams['SORT_ORDER2'],
        'FIELD_CODE' => $arParams['LIST_FIELD_CODE'],
        'PROPERTY_CODE' => $arParams['LIST_PROPERTY_CODE'],
        'DETAIL_URL' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['detail'],
        'SECTION_URL' => '',
        'IBLOCK_URL' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['news'],
        'DISPLAY_PANEL' => $arParams['DISPLAY_PANEL'],
        'SET_TITLE' => 'N',//$arParams['SET_TITLE'],
        'SET_LAST_MODIFIED' => $arParams['SET_LAST_MODIFIED'],
        'MESSAGE_404' => $arParams['MESSAGE_404'],
        'SET_STATUS_404' => $arParams['SET_STATUS_404'],
        'SHOW_404' => $arParams['SHOW_404'],
        'FILE_404' => $arParams['FILE_404'],
        'INCLUDE_IBLOCK_INTO_CHAIN' => $arParams['INCLUDE_IBLOCK_INTO_CHAIN'],
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'CACHE_FILTER' => $arParams['CACHE_FILTER'],
        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
        'DISPLAY_TOP_PAGER' => $arParams['DISPLAY_TOP_PAGER'],
        'DISPLAY_BOTTOM_PAGER' => $arParams['DISPLAY_BOTTOM_PAGER'],
        'PAGER_TITLE' => $arParams['PAGER_TITLE'],
        'PAGER_TEMPLATE' => $arParams['PAGER_TEMPLATE'],
        'PAGER_SHOW_ALWAYS' => $arParams['PAGER_SHOW_ALWAYS'],
        'PAGER_DESC_NUMBERING' => $arParams['PAGER_DESC_NUMBERING'],
        'PAGER_DESC_NUMBERING_CACHE_TIME' => $arParams['PAGER_DESC_NUMBERING_CACHE_TIME'],
        'PAGER_SHOW_ALL' => $arParams['PAGER_SHOW_ALL'],
        'PAGER_BASE_LINK_ENABLE' => $arParams['PAGER_BASE_LINK_ENABLE'],
        'PAGER_BASE_LINK' => $arParams['PAGER_BASE_LINK'],
        'PAGER_PARAMS_NAME' => $arParams['PAGER_PARAMS_NAME'],
        'DISPLAY_DATE' => $arParams['DISPLAY_DATE'],
        'DISPLAY_PREVIEW_TEXT' => $arParams['DISPLAY_PREVIEW_TEXT'],
        'PREVIEW_TRUNCATE_LEN' => $arParams['PREVIEW_TRUNCATE_LEN'],
        'ACTIVE_DATE_FORMAT' => $arParams['LIST_ACTIVE_DATE_FORMAT'],
        'USE_PERMISSIONS' => $arParams['USE_PERMISSIONS'],
        'GROUP_PERMISSIONS' => $arParams['GROUP_PERMISSIONS'],
        'FILTER_NAME' => $arParams['FILTER_NAME'],
        'HIDE_LINK_WHEN_NO_DETAIL' => $arParams['HIDE_LINK_WHEN_NO_DETAIL'],
        'CHECK_DATES' => $arParams['CHECK_DATES'],
    ],
    $component,
    [
        'HIDE_ICONS' => 'N'
    ]
);
