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
$arResult['POSTED_STEP'] = 0;

$arResult['WAS_POSTED'] = $arResult['ACTION'] !== 'initialLoad';

$arResult['PRINT_FIELDS'] = [];

$firstStepFields = ['cardNumber'];
$secondStepFields = ['lastName', 'firstName', 'secondName', 'genderCode', 'birthDay'];
$thirdStepFields = ['phone'];
$fourthStepFields = ['email'];

$printFields = array_merge($firstStepFields, $secondStepFields, $thirdStepFields, $fourthStepFields);
foreach ($printFields as $fieldName) {
    $arResult['PRINT_FIELDS'][$fieldName] = [
        'VALUE' => '',
        'ERROR' => null,
        'READONLY' => false,
    ];
}

// если номер карты прошел проверку, то по умолчанию заполним данными этой карты
if (!empty($arResult['CARD_DATA']['USER'])) {
    $arResult['PRINT_FIELDS']['lastName']['VALUE'] = $arResult['CARD_DATA']['USER']['LAST_NAME'];
    $arResult['PRINT_FIELDS']['firstName']['VALUE'] = $arResult['CARD_DATA']['USER']['FIRST_NAME'];
    $arResult['PRINT_FIELDS']['secondName']['VALUE'] = $arResult['CARD_DATA']['USER']['SECOND_NAME'];
    $arResult['PRINT_FIELDS']['birthDay']['VALUE'] = $arResult['CARD_DATA']['USER']['BIRTHDAY'];
    $arResult['PRINT_FIELDS']['genderCode']['VALUE'] = $arResult['CARD_DATA']['USER']['GENDER_CODE'];
    $arResult['PRINT_FIELDS']['phone']['VALUE'] = $arResult['CARD_DATA']['USER']['PHONE'];
    if (strpos($arResult['PRINT_FIELDS']['phone']['VALUE'], '7') === 0) {
        $arResult['PRINT_FIELDS']['phone']['VALUE'] = substr($arResult['PRINT_FIELDS']['phone']['VALUE'], 1);
    }
}

// заполним значениями результата отправки формы
foreach ($printFields as $fieldName) {
    if (isset($arResult['FIELD_VALUES'][$fieldName])) {
        if (in_array($fieldName, $firstStepFields)) {
            $arResult['POSTED_STEP'] = 1;
        } elseif (in_array($fieldName, $secondStepFields)) {
            $arResult['POSTED_STEP'] = 2;
        } elseif (in_array($fieldName, $thirdStepFields)) {
            $arResult['POSTED_STEP'] = 3;
        } elseif (in_array($fieldName, $fourthStepFields)) {
            $arResult['POSTED_STEP'] = 4;
        }

        if (is_scalar($arResult['FIELD_VALUES'][$fieldName])) {
            $arResult['PRINT_FIELDS'][$fieldName]['VALUE'] = trim($arResult['FIELD_VALUES'][$fieldName]);
        }
    }
}

// определение текущего шага
if ($arResult['WAS_POSTED']) {
    $exactStep = 0;
    if(!$exactStep && $arResult['POSTED_STEP'] >= 1) {
        $arResult['STEP'] = 2;
        foreach ($firstStepFields as $fieldName) {
            if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
                $exactStep = 1;
                break;
            }
        }
    }
    if(!$exactStep && $arResult['POSTED_STEP'] >= 2) {
        $arResult['STEP'] = 3;
        foreach ($secondStepFields as $fieldName) {
            if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
                $exactStep = 2;
                break;
            }
        }
    }
    if(!$exactStep && $arResult['POSTED_STEP'] >= 3) {
        $arResult['STEP'] = 4;
        foreach ($thirdStepFields as $fieldName) {
            if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
                $exactStep = 3;
                break;
            }
        }
    }
    if(!$exactStep && $arResult['POSTED_STEP'] >= 4) {
        $arResult['STEP'] = 5;
        foreach ($fourthStepFields as $fieldName) {
            if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
                $exactStep = 4;
                break;
            }
        }
    }
    if ($exactStep) {
        $arResult['STEP'] = $exactStep;
    }
}

foreach ($printFields as $fieldName) {
    $readonly = false;
    if ($arResult['STEP'] > 1 && in_array($fieldName, $firstStepFields)) {
        $readonly = true;
    }
    if ($arResult['STEP'] > 2 && in_array($fieldName, $secondStepFields)) {
        $readonly = true;
    }
    if ($arResult['STEP'] > 3 && in_array($fieldName, $thirdStepFields)) {
        $readonly = true;
    }
    if ($arResult['STEP'] > 4 && in_array($fieldName, $fourthStepFields)) {
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
    if ($arResult['POSTED_STEP'] >= 2 && in_array($fieldName, $secondStepFields)) {
        if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
            $error = $arResult['ERROR']['FIELD'][$fieldName];
        }
    }
    if ($arResult['POSTED_STEP'] >= 3 && in_array($fieldName, $thirdStepFields)) {
        if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
            $error = $arResult['ERROR']['FIELD'][$fieldName];
        }
    }
    if ($arResult['POSTED_STEP'] >= 4 && in_array($fieldName, $fourthStepFields)) {
        if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
            $error = $arResult['ERROR']['FIELD'][$fieldName];
        }
    }
    if ($error) {
        $arResult['PRINT_FIELDS'][$fieldName]['ERROR'] = $error;
    }
}
