<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Web\Uri;

$arResult['BASE_URI'] =
    $arResult['sUrlPath'] . $arResult['NavQueryString'] !== '' ? '?' . $arResult['NavQueryString'] : '';

if ($arResult['NavPageNomer'] > 1) {
    $uri = new Uri($arResult['BASE_URI']);
    $uri->addParams(['PAGEN_' . $arResult['NavNum'] => $arResult['NavPageNomer'] - 1]);
    
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

if ($arResult['NavPageNomer'] < $arResult['NavPageCount']) {
    $uri = new Uri($arResult['BASE_URI']);
    $uri->addParams(['PAGEN_' . $arResult['NavNum'] => $arResult['NavPageNomer'] + 1]);
    $arResult['NEXT_URL'] = $uri->getUri();
}

$arResult['URLS'] = [];
$NavRecordGroup = 1;
while ($NavRecordGroup <= $arResult['NavPageCount']) {
    $uri = new Uri($arResult['BASE_URI']);
    $uri->addParams(['PAGEN_' . $arResult['NavNum'] => $NavRecordGroup]);
    $arResult['URLS'][$NavRecordGroup] = $uri->getUri();
    
    if ($NavRecordGroup === 2 && $arResult['nStartPage'] > 3
        && $arResult['nStartPage'] - $NavRecordGroup > 1) {
        $NavRecordGroup = $arResult['nStartPage'];
    } elseif ($NavRecordGroup === $arResult['nEndPage']
              && $arResult['nEndPage'] < ($arResult['NavPageCount'] - 2)) {
        $NavRecordGroup = $arResult['NavPageCount'] - 1;
    } else {
        $NavRecordGroup++;
    }
}