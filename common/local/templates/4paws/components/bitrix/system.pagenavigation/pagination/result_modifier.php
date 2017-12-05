<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Web\Uri;

$arResult['BASE_URI'] = $arResult['sUrlPath'];
$arResult['BASE_URI'] .= $arResult['NavQueryString'] !== '' ? '?' . $arResult['NavQueryString'] : '';

if ($arResult['NavPageNomer'] > 1) {
    $uri = new Uri($arResult['BASE_URI']);
    $uri->addParams(['PAGEN_' . $arResult['NavNum'] => (int)$arResult['NavPageNomer'] - 1]);
    
    if ($arResult['bSavePage']) {
        $arResult['PREV_URL'] = $uri->getUri();
    } else {
        if ($arResult['NavPageNomer'] > 2) {
            $arResult['PREV_URL'] = $uri->getUri();
        } else {
            $arResult['PREV_URL'] = $arResult['BASE_URI'];
        }
    }
}

if ((int)$arResult['NavPageNomer'] < (int)$arResult['NavPageCount']) {
    $uri = new Uri($arResult['BASE_URI']);
    $uri->addParams(['PAGEN_' . $arResult['NavNum'] => (int)$arResult['NavPageNomer'] + 1]);
    $arResult['NEXT_URL'] = $uri->getUri();
}

$arResult['URLS']   = [];
$arResult['HIDDEN'] = [];
$NavRecordGroup     = 1;
$i                  = 0;
while ($NavRecordGroup <= (int)$arResult['NavPageCount']) {
    $i++;
    $uri = new Uri($arResult['BASE_URI']);
    $uri->addParams(['PAGEN_' . $arResult['NavNum'] => $NavRecordGroup]);
    $arResult['URLS'][$NavRecordGroup] = $uri->getUri();
    if ($i > 3 && (int)$arResult['nStartPage'] <= 1) {
        $arResult['HIDDEN'][$NavRecordGroup] = ' hidden';
    }
    if ((int)$arResult['nStartPage'] > 1 && (int)$arResult['nEndPage'] < ((int)$arResult['NavPageCount'] - 1)
        && ($NavRecordGroup === (int)$arResult['nStartPage'] || $NavRecordGroup === (int)$arResult['nEndPage'])) {
        $arResult['HIDDEN'][$NavRecordGroup] = ' hidden';
    }
    if ($NavRecordGroup > 1
        && $NavRecordGroup <= (int)$arResult['NavPageCount'] - 3
                   && (int)$arResult['nEndPage'] >= ((int)$arResult['NavPageCount'] - 1)) {
        $arResult['HIDDEN'][$NavRecordGroup] = ' hidden';
    }
    
    if ($NavRecordGroup === 1 && (int)$arResult['nStartPage'] > 1
        && (int)$arResult['nStartPage'] - $NavRecordGroup >= 0) {
        $NavRecordGroup = (int)$arResult['nStartPage'];
        $i              = 0;
    } elseif ($NavRecordGroup === (int)$arResult['nEndPage']
              && (int)$arResult['nEndPage'] < ((int)$arResult['NavPageCount'] - 1)) {
        $NavRecordGroup = (int)$arResult['NavPageCount'];
        $i              = 0;
    } else {
        $NavRecordGroup++;
    }
}