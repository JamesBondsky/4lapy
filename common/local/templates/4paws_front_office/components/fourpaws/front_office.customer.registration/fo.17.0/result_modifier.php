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

// если найдены зарегистрированные пользователи
$arResult['PRINT_USER_LIST'] = [];
if (isset($arResult['ALREADY_REGISTERED_USERS']) && $arResult['ALREADY_REGISTERED_USERS']) {
    foreach ($arResult['ALREADY_REGISTERED_USERS'] as $user) {
        /** @var \FourPaws\UserBundle\Entity\User $user */
        $arResult['PRINT_USER_LIST'][] = [
            'FULL_NAME' => $user->getFullName(),
            'PHONE' => $user->getNormalizePersonalPhone(),
            'CARD_NUMBER' => $user->getDiscountCardNumber(),
            'BIRTHDAY' => trim($user->getBirthday()),
            'USER_ID' => $user->getId(),
            'CONTACT_ID' => 0,
        ];
    }
}
// если найдены контакты в Манзане
$arResult['PRINT_CONTACT_LIST'] = [];
if (isset($arResult['CONTACT_DATA']['USER']) && $arResult['CONTACT_DATA']['USER']) {
    foreach ($arResult['CONTACT_DATA']['USER'] as $contactData) {
        $arResult['PRINT_CONTACT_LIST'][] = [
            'FULL_NAME' => $contactData['_FULL_NAME_'],
            'PHONE' => $contactData['_PHONE_NORMALIZED_'],
            'CARD_NUMBER' => $contactData['CARD_NUMBER'],
            'BIRTHDAY' => $contactData['_BIRTHDAY_FORMATTED_'],
            'USER_ID' => 0,
            'CONTACT_ID' => $contactData['CONTACT_ID'],
        ];
    }
}

$arResult['SELECTED_CONTACT_ID'] = $arResult['FIELD_VALUES']['contactId'] ?? 'n0';
$arResult['SELECTED_CONTACT_ID'] = trim($arResult['SELECTED_CONTACT_ID']) === '' ? 'n0' : $arResult['SELECTED_CONTACT_ID'];

$firstStepFields = [
    'phone',
];
$secondStepFields = [
    'contactId',
];
$thirdStepFields = [
    'lastName',
    'firstName',
    'secondName',
    'genderCode',
    'birthDay',
    'email',
];
$fourthStepFields = [];
$printFields = array_merge($firstStepFields, $secondStepFields, $thirdStepFields, $fourthStepFields);

$printFieldsSet = ['n0', $arResult['SELECTED_CONTACT_ID']];
/*
if (isset($arResult['CONTACT_DATA']['USER']) && $arResult['CONTACT_DATA']['USER']) {
    $printFieldsSet = array_merge(
        $printFieldsSet,
        array_keys($arResult['CONTACT_DATA']['USER'])
    );
}
*/
$printFieldsSet = array_unique($printFieldsSet);

$arResult['PRINT_FIELDS'] = [];
foreach ($printFieldsSet as $setKey) {
    foreach ($printFields as $fieldName) {
        $arResult['PRINT_FIELDS'][$setKey][$fieldName] = [
            'VALUE' => '',
            'ERROR' => null,
            'READONLY' => false,
        ];
    }
}

/**
 * [LP22-275]:
 *  Если учетки нет, но номер телефона найден в Manzana:
 *   - отобразить данные покупателя.
 *  <s>Если учетки нет и номер телефона не найден в Manzana/в Manzana найдено более 1 номера:
 *  - выводится форма регистрации нового покупателя</s>
 * [LP23-182]:
 *  - если найдены данные в манзане, то сначала выбирается контакт для последующей регистрации
 */
if (isset($arResult['CONTACT_DATA']['USER']) && $arResult['CONTACT_DATA']['USER']) {
    foreach ($arResult['CONTACT_DATA']['USER'] as $contactData) {
        if ($contactData['CONTACT_ID'] !== $arResult['SELECTED_CONTACT_ID']) {
            continue;
        }
        $setKey = $contactData['CONTACT_ID'];
        $arResult['PRINT_FIELDS'][$setKey]['contactId']['VALUE'] = htmlspecialcharsbx($contactData['CONTACT_ID']);
        $arResult['PRINT_FIELDS'][$setKey]['contactId']['READONLY'] = true;

        $arResult['PRINT_FIELDS'][$setKey]['lastName']['VALUE'] = htmlspecialcharsbx($contactData['LAST_NAME']);
        $arResult['PRINT_FIELDS'][$setKey]['lastName']['READONLY'] = $contactData['LAST_NAME'] !== '';

        $arResult['PRINT_FIELDS'][$setKey]['firstName']['VALUE'] = htmlspecialcharsbx($contactData['FIRST_NAME']);
        $arResult['PRINT_FIELDS'][$setKey]['firstName']['READONLY'] = $contactData['FIRST_NAME'] !== '';

        $arResult['PRINT_FIELDS'][$setKey]['secondName']['VALUE'] = htmlspecialcharsbx($contactData['SECOND_NAME']);
        $arResult['PRINT_FIELDS'][$setKey]['secondName']['READONLY'] = $contactData['SECOND_NAME'] !== '';

        $arResult['PRINT_FIELDS'][$setKey]['birthDay']['VALUE'] = htmlspecialcharsbx($contactData['_BIRTHDAY_FORMATTED_']);
        $arResult['PRINT_FIELDS'][$setKey]['birthDay']['READONLY'] = $contactData['_BIRTHDAY_FORMATTED_'] !== '';

        $arResult['PRINT_FIELDS'][$setKey]['genderCode']['VALUE'] = htmlspecialcharsbx($contactData['GENDER_CODE']);
        $arResult['PRINT_FIELDS'][$setKey]['genderCode']['READONLY'] = false;

        $arResult['PRINT_FIELDS'][$setKey]['phone']['VALUE'] = htmlspecialcharsbx($contactData['_PHONE_NORMALIZED_']);
        $arResult['PRINT_FIELDS'][$setKey]['phone']['READONLY'] = $contactData['_PHONE_NORMALIZED_'] !== '';

        $arResult['PRINT_FIELDS'][$setKey]['email']['VALUE'] = htmlspecialcharsbx($contactData['EMAIL']);
        //$arResult['PRINT_FIELDS'][$setKey]['email']['READONLY'] = $contactData['EMAIL'] !== '';
    }
}

// заполним значениями результата отправки формы
if (isset($arResult['FIELD_VALUES']) && $arResult['FIELD_VALUES']) {
    $setKey = $arResult['SELECTED_CONTACT_ID'];
    foreach ($printFields as $fieldName) {
        if (isset($arResult['FIELD_VALUES'][$fieldName])) {
            if (in_array($fieldName, $firstStepFields)) {
                $arResult['POSTED_STEP'] = 1;
            } elseif (in_array($fieldName, $secondStepFields)) {
                $arResult['POSTED_STEP'] = 2;
            } elseif (in_array($fieldName, $thirdStepFields)) {
                $arResult['POSTED_STEP'] = 3;
                /*
            } elseif (in_array($fieldName, $fourthStepFields)) {
                $arResult['POSTED_STEP'] = 4;
                */
            }

            if (is_scalar($arResult['FIELD_VALUES'][$fieldName])) {
                $arResult['PRINT_FIELDS'][$setKey][$fieldName]['VALUE'] = trim($arResult['FIELD_VALUES'][$fieldName]);
            }
        }
    }
}

// если нет пользователей в манзане, то сразу переходим на второй шаг
if ($arResult['POSTED_STEP'] == 1 && (!isset($arResult['CONTACT_DATA']['USER']) || !$arResult['CONTACT_DATA']['USER'])) {
    $arResult['POSTED_STEP'] = 2;
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
    if (!$exactStep && $arResult['POSTED_STEP'] >= 3) {
        $arResult['STEP'] = 4;
        foreach ($thirdStepFields as $fieldName) {
            if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
                $exactStep = 3;
                break;
            }
        }
    }
    /*
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

$setKey = $arResult['SELECTED_CONTACT_ID'];
foreach ($printFields as $fieldName) {
    $readonly = false;
    if ($arResult['STEP'] > 1 && in_array($fieldName, $firstStepFields)) {
        $readonly = true;
    }

    if ($arResult['STEP'] > 2 && in_array($fieldName, $secondStepFields)) {
        $readonly = true;
    }

    if ($fieldName !== 'email' && $fieldName !== 'genderCode' && $arResult['STEP'] > 3 && in_array($fieldName, $thirdStepFields)) {
        $readonly = true;
    }
    /*
    if ($arResult['STEP'] > 4 && in_array($fieldName, $fourthStepFields)) {
        $readonly = true;
    }
    */

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
    /*
    if ($arResult['POSTED_STEP'] >= 4 && in_array($fieldName, $fourthStepFields)) {
        if (!empty($arResult['ERROR']['FIELD'][$fieldName])) {
            $error = $arResult['ERROR']['FIELD'][$fieldName];
        }
    }
    */

    if ($readonly) {
        $arResult['PRINT_FIELDS'][$setKey][$fieldName]['READONLY'] = true;
    }

    if ($error) {
        $arResult['PRINT_FIELDS'][$setKey][$fieldName]['ERROR'] = $error;
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
