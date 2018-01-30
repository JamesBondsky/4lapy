<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global \CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var \CBitrixComponentTemplate $this
 */

$arResult['WAS_POSTED'] = $arResult['ACTION'] !== 'initialLoad';

$arParams['USE_AJAX'] = 'N';
$arResult['USE_AJAX'] = isset($arParams['USE_AJAX']) && $arParams['USE_AJAX'] === 'N' ? 'N' : 'Y';
$arResult['IS_AJAX_REQUEST'] = isset($arResult['FIELD_VALUES']['ajaxContext']) ? 'Y' : 'N';
if ($arResult['USE_AJAX'] === 'Y' && $arResult['IS_AJAX_REQUEST'] !== 'Y') {
    $signer = new \Bitrix\Main\Security\Sign\Signer();
    $arResult['JS']['signedTemplate'] = $signer->sign($this->GetName(), 'front_office.card.registration');
    $arResult['JS']['signedParams'] = $signer->sign(base64_encode(serialize($arResult['ORIGINAL_PARAMETERS'])), 'front_office.card.registration');
}

$firstStepFields = ['cardNumberForHistory'];

$printFields = $firstStepFields;

$arResult['PRINT_FIELDS'] = [];
foreach ($printFields as $fieldName) {
    $arResult['PRINT_FIELDS'][$fieldName] = [
        'VALUE' => '',
        'ERROR' => null,
        'READONLY' => false,
    ];
}

$arResult['STEP'] = 1;
$arResult['POSTED_STEP'] = 0;
if ($arResult['WAS_POSTED']) {
    $arResult['POSTED_STEP'] = 1;
}

// заполним значениями результата отправки формы
foreach ($printFields as $fieldName) {
    if (isset($arResult['FIELD_VALUES'][$fieldName])) {
        if (is_scalar($arResult['FIELD_VALUES'][$fieldName])) {
            $arResult['PRINT_FIELDS'][$fieldName]['VALUE'] = trim($arResult['FIELD_VALUES'][$fieldName]);
        }
    }
}

foreach ($printFields as $fieldName) {
    $readonly = false;
    if ($arResult['STEP'] > 1 && in_array($fieldName, $firstStepFields)) {
        $readonly = true;
    }
    if ($readonly) {
        $arResult['PRINT_FIELDS'][$fieldName]['READONLY'] = true;
    }

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

// данные по запрошенной карте
$arResult['CURRENT_CARD'] = [];
if (!empty($arResult['CARD_DATA']['CONTACT_CARDS'])) {
    foreach ($arResult['CARD_DATA']['CONTACT_CARDS'] as $card) {
        if ($card['IS_CURRENT'] === 'Y') {
            $arResult['CURRENT_CARD'] = $card;
            break;
        }
    }
}

if ($arResult['CHEQUES']) {
    $arResult['CHEQUES'] = array_reverse($arResult['CHEQUES'], false);
}