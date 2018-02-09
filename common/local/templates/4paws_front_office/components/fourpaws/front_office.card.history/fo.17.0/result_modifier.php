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

$arParams['LAST_CHEQUES_CNT'] = isset($arParams['LAST_CHEQUES_CNT']) ? (int) $arParams['LAST_CHEQUES_CNT'] : 10;
$arParams['USE_AJAX'] = isset($arParams['USE_AJAX']) && $arParams['USE_AJAX'] === 'N' ? 'N' : 'Y';

$arResult['WAS_POSTED'] = $arResult['ACTION'] !== 'initialLoad' && !empty($arResult['FIELD_VALUES']) ? 'Y' : 'N';

$arResult['USE_AJAX'] = $arParams['USE_AJAX'];
$arResult['IS_AJAX_REQUEST'] = isset($arResult['FIELD_VALUES']['ajaxContext']) ? 'Y' : 'N';
if ($arResult['USE_AJAX'] === 'Y' && $arResult['IS_AJAX_REQUEST'] !== 'Y') {
    $signer = new \Bitrix\Main\Security\Sign\Signer();
    $arResult['JS']['signedTemplate'] = $signer->sign($this->GetName(), 'front_office.card.history');
    $arResult['JS']['signedParams'] = $signer->sign(base64_encode(serialize($arResult['ORIGINAL_PARAMETERS'])), 'front_office.card.history');
}

// Запрашиваемое представление страницы
$arResult['CURRENT_STAGE'] = 'initial';
if ($arResult['WAS_POSTED'] === 'Y') {
    $arResult['CURRENT_STAGE'] = 'history';
}
if ($arResult['IS_AJAX_REQUEST'] === 'Y' && isset($arResult['FIELD_VALUES']['getChequeItems'])) {
    if ($arResult['FIELD_VALUES']['getChequeItems'] === 'Y') {
        $arResult['CURRENT_STAGE'] = 'cheque_details';
    }
}
if ($arResult['WAS_POSTED'] === 'Y' && isset($arResult['FIELD_VALUES']['print'])) {
    if ($arResult['FIELD_VALUES']['print'] === 'Y') {
        $arResult['CURRENT_STAGE'] = 'print';
    }
}

//
// Метаданные полей формы
//
$arResult['STEP'] = 1;
$arResult['POSTED_STEP'] = 0;
if ($arResult['WAS_POSTED'] === 'Y') {
    $arResult['POSTED_STEP'] = 1;
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
// заполнение выводимых полей формы значениями результата отправки формы
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

//
// Данные по запрошенной карте
//
$arResult['CURRENT_CARD'] = [];
if (!empty($arResult['CONTACT_CARDS'])) {
    foreach ($arResult['CONTACT_CARDS'] as $card) {
        if ($card['NUMBER'] === $arResult['CARD_DATA']['NUMBER']) {
            $arResult['CURRENT_CARD'] = $card;
            break;
        }
    }
}

//
// Список последних n чеков по запрошенной карте
//
if (!empty($arResult['CHEQUES'])) {
    $arResult['CHEQUES'] = array_reverse($arResult['CHEQUES'], true);
    if ($arResult['CURRENT_STAGE'] === 'print') {
        // для печатной версии расставим флаги текущий/предыдущий месяц, текущая/предыдущая неделя
        $curDate = new \DateTime();
        $prevWeekDate = new \DateTime('-1 week');
        $prevMonthDate = new \DateTime('-1 month');
        $curMonth = $curDate->format('n');
        $curYear = $curDate->format('Y');
        $curWeek = $curDate->format('W');
        $prevWeek = $prevWeekDate->format('W');
        $prevWeekYear = $prevWeekDate->format('Y');
        $prevMonth = $prevWeekDate->format('n');
        $prevMonthYear = $prevWeekDate->format('Y');
        foreach ($arResult['CHEQUES'] as &$cheque) {
            /** @var \DateTimeImmutable $chequeDate */
            $chequeDate = $cheque['DATE'];
            $chequeYear = $chequeDate->format('Y');
            $chequeMonth = $chequeDate->format('n');
            $chequeWeek = $chequeDate->format('W');
            $cheque['IS_CUR_MONTH'] = $chequeYear == $curYear && $chequeMonth == $curMonth ? 'Y' : 'N';
            $cheque['IS_CUR_WEEK'] = $chequeYear == $curYear && $chequeWeek == $curWeek ? 'Y' : 'N';
            $cheque['IS_PREV_WEEK'] = $chequeYear == $prevWeekYear && $chequeWeek == $prevWeek ? 'Y' : 'N';
            $cheque['IS_PREV_MONTH'] = $chequeYear == $prevMonthYear && $chequeWeek == $prevMonth ? 'Y' : 'N';
        }
        unset($cheque);
    } elseif ($arParams['LAST_CHEQUES_CNT'] > 0) {
        $arResult['CHEQUES'] = array_slice($arResult['CHEQUES'], 0, $arParams['LAST_CHEQUES_CNT'], true);
    }
}

$this->getComponent()->arParams = $arParams;
