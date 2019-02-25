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
    'fourpaws:personal.piggybank',
    '',
    ['UPGRADE_COUPON' => 'Y'],
    $component,
    ['HIDE_ICONS' => 'Y']
);
