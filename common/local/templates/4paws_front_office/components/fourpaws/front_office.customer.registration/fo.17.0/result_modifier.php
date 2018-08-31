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

$arResult['STEP'] = 1;
$arResult['POSTED_STEP'] = 0;

$arResult['WAS_POSTED'] = $arResult['ACTION'] !== 'initialLoad';

$arResult['USE_AJAX'] = isset($arParams['USE_AJAX']) && $arParams['USE_AJAX'] === 'N' ? 'N' : 'Y';
$arResult['IS_AJAX_REQUEST'] = isset($arResult['FIELD_VALUES']['ajaxContext']) ? 'Y' : 'N';
if ($arResult['USE_AJAX'] === 'Y' && $arResult['IS_AJAX_REQUEST'] !== 'Y') {
    $signer = new \Bitrix\Main\Security\Sign\Signer();
    $arResult['JS']['signedTemplate'] = $signer->sign(
        $this->GetName(),
        'front_office.customer.registration'
    );
    $arResult['JS']['signedParams'] = $signer->sign(
        base64_encode(serialize($arResult['ORIGINAL_PARAMETERS'])),
        'front_office.customer.registration'
    );
}

$firstStepFields = ['phone'];
$secondStepFields = [
    'lastName',
    'firstName',
    'secondName',
    'genderCode',
    'birthDay',
    'email',
];
$thirdStepFields = [];
$fourthStepFields = [];
$printFields = array_merge($firstStepFields, $secondStepFields, $thirdStepFields, $fourthStepFields);

$arResult['PRINT_FIELDS'] = [];
foreach ($printFields as $fieldName) {
    $arResult['PRINT_FIELDS'][$fieldName] = [
        'VALUE' => '',
        'ERROR' => null,
        'READONLY' => false,
    ];
}
/**
 * [LP22-275]:
 *  Если учетки нет, но номер телефона найден в Manzana:
 *   - отобразить данные покупателя.
 *  Если учетки нет и номер телефона не найден в Manzana/в Manzana найдено более 1 номера:
 *  - выводится форма регистрации нового покупателя
 */
if (!empty($arResult['CONTACT_DATA']['USER']) && count($arResult['CONTACT_DATA']['USER']) == 1) {
    $contactData = reset($arResult['CONTACT_DATA']['USER']);
    $arResult['PRINT_FIELDS']['lastName']['VALUE'] = htmlspecialcharsbx($contactData['LAST_NAME']);
    $arResult['PRINT_FIELDS']['lastName']['READONLY'] = $contactData['LAST_NAME'] !== '';

    $arResult['PRINT_FIELDS']['firstName']['VALUE'] = htmlspecialcharsbx($contactData['FIRST_NAME']);
    $arResult['PRINT_FIELDS']['firstName']['READONLY'] = $contactData[''] !== '';

    $arResult['PRINT_FIELDS']['secondName']['VALUE'] = htmlspecialcharsbx($contactData['SECOND_NAME']);
    $arResult['PRINT_FIELDS']['secondName']['READONLY'] = $contactData[''] !== '';

    $arResult['PRINT_FIELDS']['birthDay']['VALUE'] = '';
    if (is_object($contactData['BIRTHDAY'])) {
        /** @var \DateTimeImmutable $date */
        $date = $contactData['BIRTHDAY'];
        $arResult['PRINT_FIELDS']['birthDay']['VALUE'] = $date->format('d.m.Y');
        $arResult['PRINT_FIELDS']['birthDay']['READONLY'] = $arResult['PRINT_FIELDS']['birthDay']['VALUE'] !== '';
    }

    $arResult['PRINT_FIELDS']['genderCode']['VALUE'] = htmlspecialcharsbx($contactData['GENDER_CODE']);
    $arResult['PRINT_FIELDS']['genderCode']['READONLY'] = $contactData['GENDER_CODE'] !== '';

    $arResult['PRINT_FIELDS']['phone']['VALUE'] = htmlspecialcharsbx($contactData['_PHONE_NORMALIZED_']);
    $arResult['PRINT_FIELDS']['phone']['READONLY'] = $contactData['_PHONE_NORMALIZED_'] !== '';

    $arResult['PRINT_FIELDS']['email']['VALUE'] = htmlspecialcharsbx($contactData['EMAIL']);
    $arResult['PRINT_FIELDS']['email']['READONLY'] = $contactData['EMAIL'] !== '';
}

// заполним значениями результата отправки формы
foreach ($printFields as $fieldName) {
    if (isset($arResult['FIELD_VALUES'][$fieldName])) {
        if (in_array($fieldName, $firstStepFields)) {
            $arResult['POSTED_STEP'] = 1;
        } elseif (in_array($fieldName, $secondStepFields)) {
            $arResult['POSTED_STEP'] = 2;
            /*
        } elseif (in_array($fieldName, $thirdStepFields)) {
            $arResult['POSTED_STEP'] = 3;
        } elseif (in_array($fieldName, $fourthStepFields)) {
            $arResult['POSTED_STEP'] = 4;
            */
        }
    
        if (is_scalar($arResult['FIELD_VALUES'][$fieldName])) {
            $arResult['PRINT_FIELDS'][$fieldName]['VALUE'] = trim($arResult['FIELD_VALUES'][$fieldName]);
        }
    }
}

// определение текущего шага
if ($arResult['WAS_POSTED']) {
    $exactStep = 0;
    if (!$exactStep && $arResult['POSTED_STEP'] >= 1) {
        $arResult['STEP'] = 2;
        foreach ($firstStepFields as $fieldName) {
            if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
                $exactStep = 1;
                break;
            }
        }
    }
    if (!$exactStep && $arResult['POSTED_STEP'] >= 2) {
        $arResult['STEP'] = 3;
        foreach ($secondStepFields as $fieldName) {
            if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
                $exactStep = 2;
                break;
            }
        }
    }
    /*
    if (!$exactStep && $arResult['POSTED_STEP'] >= 3) {
        $arResult['STEP'] = 4;
        foreach ($thirdStepFields as $fieldName) {
            if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
                $exactStep = 3;
                break;
            }
        }
    }
    if (!$exactStep && $arResult['POSTED_STEP'] >= 4) {
        $arResult['STEP'] = 5;
        foreach ($fourthStepFields as $fieldName) {
            if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
                $exactStep = 4;
                break;
            }
        }
    }
    */
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
    /*
    if ($arResult['STEP'] > 3 && in_array($fieldName, $thirdStepFields)) {
        $readonly = true;
    }
    if ($arResult['STEP'] > 4 && in_array($fieldName, $fourthStepFields)) {
        $readonly = true;
    }
    */
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
    /*
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
    */
    if ($error) {
        $arResult['PRINT_FIELDS'][$fieldName]['ERROR'] = $error;
    }
}

$arResult['AVATAR_AUTH_PAGE'] = '/front-office/avatar/';
$arResult['AVATAR_AUTH_URL'] = '';
$arResult['REGISTERED_USER_ID'] = $arResult['REGISTERED_USER_ID'] ? (int)$arResult['REGISTERED_USER_ID'] : 0;
if ($arResult['REGISTERED_USER_ID'] > 0) {
    $queryParams = [
        'action' => 'forceAuth',
        'userId' => $arResult['REGISTERED_USER_ID']
    ];
    $arResult['AVATAR_AUTH_URL'] .= $arResult['AVATAR_AUTH_PAGE'] . '?' . http_build_query($queryParams);
}
