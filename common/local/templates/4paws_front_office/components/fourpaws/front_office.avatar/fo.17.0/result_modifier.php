<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global \CMain                 $APPLICATION
 * @var array                     $arParams
 * @var array                     $arResult
 * @var \CBitrixComponentTemplate $this
 */

$arParams['USE_AJAX'] = isset($arParams['USE_AJAX']) && $arParams['USE_AJAX'] === 'N' ? 'N' : 'Y';
$arResult['WAS_POSTED'] = $arResult['ACTION'] !== 'initialLoad' && !empty($arResult['FIELD_VALUES']) ? 'Y' : 'N';
$arParams['LOGOUT_URL'] = isset($arParams['LOGOUT_URL']) && $arParams['LOGOUT_URL'] !== '' ? $arParams['LOGOUT_URL'] : 'logout.php';

$arResult['USE_AJAX']        = $arParams['USE_AJAX'];
$arResult['IS_AJAX_REQUEST'] = isset($arResult['FIELD_VALUES']['ajaxContext']) ? 'Y' : 'N';
if ($arResult['USE_AJAX'] === 'Y' && $arResult['IS_AJAX_REQUEST'] !== 'Y') {
    $signer                           = new \Bitrix\Main\Security\Sign\Signer();
    $arResult['JS']['signedTemplate'] = $signer->sign($this->GetName(), 'front_office.avatar');
    $arResult['JS']['signedParams']   =
        $signer->sign(base64_encode(serialize($arResult['ORIGINAL_PARAMETERS'])), 'front_office.avatar');
}

// Запрашиваемое представление страницы
$arResult['CURRENT_STAGE'] = 'initial';
if ($arResult['WAS_POSTED'] === 'Y') {
    $arResult['CURRENT_STAGE'] = 'user_list';
    if ($arResult['FIELD_VALUES']['action'] === 'userAuth') {
        $arResult['CURRENT_STAGE'] = 'user_auth';
    }
}

//
// Метаданные полей формы
//
$arResult['STEP']        = 1;
$arResult['POSTED_STEP'] = 0;
if ($arResult['WAS_POSTED'] === 'Y') {
    $arResult['POSTED_STEP'] = 1;
}

$firstStepFields = [
    'cardNumber',
    'phone',
    'firstName',
    'secondName',
    'lastName',
    'birthDay',
];

$printFields = $firstStepFields;

$arResult['PRINT_FIELDS'] = [];
foreach ($printFields as $fieldName) {
    $arResult['PRINT_FIELDS'][$fieldName] = [
        'VALUE'    => '',
        'ERROR'    => null,
        'READONLY' => false,
    ];
}
// заполнение выводимых полей формы значениями результата отправки формы
foreach ($printFields as $fieldName) {
    if (isset($arResult['FIELD_VALUES'][$fieldName])) {
        if (is_scalar($arResult['FIELD_VALUES'][$fieldName])) {
            $arResult['PRINT_FIELDS'][$fieldName]['VALUE'] = trim($arResult['FIELD_VALUES'][$fieldName]);
        }
    }
}
foreach ($printFields as $fieldName) {
    $error = null;
    if ($arResult['POSTED_STEP'] >= 1 && in_array($fieldName, $firstStepFields)) {
        if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
            $error = $arResult['ERROR']['FIELD'][$fieldName];
        }
    }
    if ($error) {
        $arResult['PRINT_FIELDS'][$fieldName]['ERROR'] = $error;
    }
}

$this->getComponent()->arParams = $arParams;
