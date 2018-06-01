<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var \CMain $APPLICATION */
/** @var array $arResult */

if (!$arResult['canEditSubscribe']) {
    return;
}

$APPLICATION->IncludeComponent(
    'fourpaws:expertsender.form',
    'profile',
    [],
    false,
    ['HIDE_ICONS' => 'Y']
);
