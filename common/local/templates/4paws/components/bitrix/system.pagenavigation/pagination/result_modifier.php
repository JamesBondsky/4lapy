<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Web\Uri;

$arResult['NavQueryString'] = html_entity_decode($arResult['NavQueryString']);
$arResult['BASE_URI'] = $arResult['sUrlPath'];
$arResult['BASE_URI'] .= $arResult['NavQueryString'] !== '' ? '?' . $arResult['NavQueryString'] : '';

$pageParameter = $arParams['PAGE_PARAMETER'] ?? 'PAGEN_' . $arResult['NavNum'];

if ($arResult['NavPageNomer'] > 1) {
    $uri = new Uri($arResult['BASE_URI']);
    $uri->addParams([$pageParameter => (int)$arResult['NavPageNomer'] - 1]);
    
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
    $uri->addParams([$pageParameter => (int)$arResult['NavPageNomer'] + 1]);
    $arResult['NEXT_URL'] = $uri->getUri();
}

$arResult['URLS']   = [];
$arResult['HIDDEN'] = [];
$navRecordGroup     = 1;
$i                  = 0;
while ($navRecordGroup <= (int)$arResult['NavPageCount']) {
    $i++;
    $uri = new Uri($arResult['BASE_URI']);
    $uri->addParams([$pageParameter => $navRecordGroup]);
    $arResult['URLS'][$navRecordGroup] = $uri->getUri();
    if ($i > 3 && (int)$arResult['nStartPage'] <= 1) {
        $arResult['HIDDEN'][$navRecordGroup] = ' hidden';
    }
    if ((int)$arResult['nStartPage'] > 1 && (int)$arResult['nEndPage'] < ((int)$arResult['NavPageCount'] - 1)
        && ($navRecordGroup === (int)$arResult['nStartPage'] || $navRecordGroup === (int)$arResult['nEndPage'])) {
        $arResult['HIDDEN'][$navRecordGroup] = ' hidden';
    }
    if ($navRecordGroup > 1
        && $navRecordGroup <= (int)$arResult['NavPageCount'] - 3
        && (int)$arResult['nEndPage'] >= ((int)$arResult['NavPageCount'] - 1)) {
        $arResult['HIDDEN'][$navRecordGroup] = ' hidden';
    }
    
    if ($navRecordGroup === 1 && (int)$arResult['nStartPage'] > 1
        && (int)$arResult['nStartPage'] - $navRecordGroup >= 0) {
        $navRecordGroup = (int)$arResult['nStartPage'];
        $i              = 0;
    } elseif ($navRecordGroup === (int)$arResult['nEndPage']
              && (int)$arResult['nEndPage'] < ((int)$arResult['NavPageCount'] - 1)) {
        $navRecordGroup = (int)$arResult['NavPageCount'];
        $i              = 0;
    } else {
        $navRecordGroup++;
    }
}
