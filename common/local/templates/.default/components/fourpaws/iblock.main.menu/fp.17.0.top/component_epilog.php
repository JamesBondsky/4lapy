<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Главное меню сайта
 * component_epilog.php
 *
 * @updated: 28.12.2017
 */

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

if ($arResult['header_dropdown_menu']) {
    $APPLICATION->AddViewContent('header_dropdown_menu', $arResult['header_dropdown_menu']);
}
