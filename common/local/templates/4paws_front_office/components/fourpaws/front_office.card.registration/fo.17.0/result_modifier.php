<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

$arResult['STEP'] = 1;

$arResult['WAS_POSTED'] = $arResult['ACTION'] !== 'initialLoad';

$arResult['PRINT_VALUES'] = [];
$printFields = ['cardNumber',];
foreach ($printFields as $fieldName) {
    $arResult['PRINT_VALUES'][$fieldName] = '';
    if (isset($arResult['FIELD_VALUES'][$fieldName])) {
        if (is_scalar($arResult['FIELD_VALUES'][$fieldName])) {
            $arResult['PRINT_VALUES'][$fieldName] = trim($arResult['FIELD_VALUES'][$fieldName]);
        }
    }
}
