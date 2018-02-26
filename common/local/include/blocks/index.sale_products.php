<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок на главной странице: распродажа
 */

/** @global $APPLICATION */

$APPLICATION->IncludeComponent('fourpaws:catalog.popular.list', '', ['COUNT' => 12], false, ['HIDE_ICONS' => 'Y']);