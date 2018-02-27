<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок на главной странице: распродажа
 */

/** @global $APPLICATION */
$APPLICATION->IncludeComponent('fourpaws:catalog.snippet.list', '', [
    'COUNT'        => 12,
    'OFFER_FILTER' => [
        '!PROPERTY_IS_SALE' => false,
    ],
    'TITLE' => 'Распродажа',
], false, ['HIDE_ICONS' => 'Y']);