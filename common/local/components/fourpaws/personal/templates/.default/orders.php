<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

$APPLICATION->IncludeComponent(
    'fourpaws:personal.orders',
    '',
    [
        'CACHE_TYPE' => 'N',
    ],
    $component,
    ['HIDE_ICONS' => 'Y']
);
