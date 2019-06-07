<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global               $APPLICATION
 *
 * @var array            $arParams
 * @var array            $arResult
 * @var CBitrixComponent $component
 */

$APPLICATION->SetPageProperty('PUBLICATION_DETAIL_CONTAINER_1', 'b-container b-container--news-detail');
$APPLICATION->SetPageProperty('PUBLICATION_DETAIL_CONTAINER_2', 'b-detail-page');

$this->setFrameMode(true);

$elementId = $APPLICATION->IncludeComponent(
    'fourpaws:shares.detail',
    '',
    [
        'IBLOCK_TYPE'               => $arParams['IBLOCK_TYPE'],
        'IBLOCK_ID'                 => $arParams['IBLOCK_ID'],
        'FIELD_CODE'                => $arParams['DETAIL_FIELD_CODE'],
        'ELEMENT_ID'                => $arResult['VARIABLES']['ELEMENT_ID'],
        'ELEMENT_CODE'              => $arResult['VARIABLES']['ELEMENT_CODE'],
        'SECTION_ID'                => $arResult['VARIABLES']['SECTION_ID'],
        'SECTION_CODE'              => $arResult['VARIABLES']['SECTION_CODE'],
        'USE_SHARE'                 => $arParams['USE_SHARE'],
        'ADD_ELEMENT_CHAIN'         => $arParams['ADD_ELEMENT_CHAIN'],
        'STRICT_SECTION_CHECK'      => $arParams['STRICT_SECTION_CHECK'],
        'URL_REDIRECT_404'          => $arParams['URL_REDIRECT_404'],
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
    $APPLICATION->IncludeComponent(
        'fourpaws:products.by.prop',
        '',
        [
            'IBLOCK_ID'     => $arParams['IBLOCK_ID'],
            'ITEM_ID'       => $elementId,
            'TITLE'         => '',
            'COUNT_ON_PAGE' => 20,
            'PROPERTY_CODE' => 'PRODUCTS',
            'FILTER_FIELD'  => 'XML_ID',
            'IS_SHARE'      => true
        ],
        $component,
        [
            'HIDE_ICONS' => 'Y',
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
