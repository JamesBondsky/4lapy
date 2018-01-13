<?php
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок "Просмотренные мной товары"
 *
 * @updated: 12.01.2018
 */

/** @global $APPLICATION */
/** @var array $arParams */

$arParams['PRODUCTS_IBLOCK_TYPE'] = IblockType::CATALOG;
$arParams['PRODUCTS_IBLOCK_CODE'] = IblockCode::PRODUCTS;
$arParams['PRODUCTS_IBLOCK_ID'] = IblockUtils::getIblockId($arParams['PRODUCTS_IBLOCK_TYPE'], $arParams['PRODUCTS_IBLOCK_CODE']);
$arParams['OFFERS_IBLOCK_TYPE'] = IblockType::CATALOG;
$arParams['OFFERS_IBLOCK_CODE'] = IblockCode::OFFERS;
$arParams['OFFERS_IBLOCK_ID'] = IblockUtils::getIblockId($arParams['OFFERS_IBLOCK_TYPE'], $arParams['OFFERS_IBLOCK_CODE']);

$APPLICATION->IncludeComponent(
	'bitrix:catalog.products.viewed', 
	'fp.17.0.default', 
	[
		'COMPONENT_TEMPLATE' => 'fp.17.0.default',
		'IBLOCK_MODE' => 'single',
		'IBLOCK_TYPE' => $arParams['PRODUCTS_IBLOCK_TYPE'],
		'IBLOCK_ID' => $arParams['PRODUCTS_IBLOCK_ID'],
		'SHOW_FROM_SECTION' => 'N',
		'SECTION_ID' => '', //isset($GLOBALS['CATALOG_CURRENT_SECTION_ID']) ? $GLOBALS['CATALOG_CURRENT_SECTION_ID'] : 0,
		'SECTION_CODE' => '',
		// чтобы не показывался товар на собственной странице карточки товара
		'SECTION_ELEMENT_ID' => isset($GLOBALS['CATALOG_CURRENT_ELEMENT_ID']) ? $GLOBALS['CATALOG_CURRENT_ELEMENT_ID'] : 0,
		'SECTION_ELEMENT_CODE' => '',
		'DEPTH' => '5',
		'HIDE_NOT_AVAILABLE' => 'N',
		'HIDE_NOT_AVAILABLE_OFFERS' => 'N',
		'CACHE_TYPE' => 'N',
		'CACHE_TIME' => '3600',
		'CACHE_GROUPS' => 'N',
		// чтобы через дефолтные параметры умники не баловались
		'ACTION_VARIABLE' => 'asdajkasdjkjkleqwrjkeqrjkoerjkler',//'action_cpv',
		'PRODUCT_ID_VARIABLE' => 'xcvnmxcvmxcvmxcvmxcvkl',//'id',
		'PRICE_CODE' => [],
		'USE_PRICE_COUNT' => 'N',
		'SHOW_PRICE_COUNT' => '1',
		'PRICE_VAT_INCLUDE' => 'Y',
		'CONVERT_CURRENCY' => 'N',
		'BASKET_URL' => '',
		'USE_PRODUCT_QUANTITY' => 'N',
		'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
		'ADD_PROPERTIES_TO_BASKET' => 'N',
		'PRODUCT_PROPS_VARIABLE' => 'prop',
		'PARTIAL_PRODUCT_PROPERTIES' => 'N',
		'DISPLAY_COMPARE' => 'N',
		'PROPERTY_CODE_'.$arParams['PRODUCTS_IBLOCK_ID'] => [
		    'BRAND'
        ],
		'CART_PROPERTIES_'.$arParams['PRODUCTS_IBLOCK_ID'] => [],
		'ADDITIONAL_PICT_PROP_'.$arParams['PRODUCTS_IBLOCK_ID'] => '',
		'LABEL_PROP_'.$arParams['PRODUCTS_IBLOCK_ID'] => [],
		'PROPERTY_CODE_'.$arParams['OFFERS_IBLOCK_ID'] => [
		    'IMG'
        ],
		'CART_PROPERTIES_'.$arParams['OFFERS_IBLOCK_ID'] => [],
		'ADDITIONAL_PICT_PROP_'.$arParams['OFFERS_IBLOCK_ID'] => '',
		'OFFER_TREE_PROPS_'.$arParams['OFFERS_IBLOCK_ID'] => [],
        'PAGE_ELEMENT_COUNT' => 10,
	],
	null,
	[
		'HIDE_ICONS' => 'Y'
	]
);
