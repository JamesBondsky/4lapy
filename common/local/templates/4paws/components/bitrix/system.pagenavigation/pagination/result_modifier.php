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

$countItemsBetweenDot = 5;
$leftCount = 2;
$noneHiddenCount = 3;

$curPage = (int)$arResult['NavPageNomer'];
$countPages = (int)$arResult['NavPageCount'];

$pageParameter = $arParams['PAGE_PARAMETER'] ?? 'PAGEN_' . $arResult['NavNum'];

/** юрл предыдущей страницы */
if ($curPage > 1) {
    $uri = new Uri($arResult['BASE_URI']);
    $uri->addParams([$pageParameter => $curPage - 1]);

    if ($arResult['bSavePage']) {
        $arResult['PREV_URL'] = $uri->getUri();
    } else {
        if ($curPage > 2) {
            $arResult['PREV_URL'] = $uri->getUri();
        } else {
            $arResult['PREV_URL'] = $arResult['BASE_URI'];
        }
    }
}

/** юрл следующей страницы */
if ($curPage < $countPages) {
    $uri = new Uri($arResult['BASE_URI']);
    $uri->addParams([$pageParameter => $curPage + 1]);
    $arResult['NEXT_URL'] = $uri->getUri();
}

$arResult['URLS'] = [];
$arResult['HIDDEN'] = [];
$navRecordGroup = 1;
$i = 0;

$arResult['START_BETWEEN_BEGIN'] = 0;
$arResult['START_BETWEEN_END'] = 0;
$arResult['END_BETWEEN_BEGIN'] = 0;
$arResult['END_BETWEEN_END'] = 0;

while ($navRecordGroup <= $countPages) {
    $i++;

    /** установка юрлов */
    $uri = new Uri($arResult['BASE_URI']);
    $uri->addParams([$pageParameter => $navRecordGroup]);
    $arResult['URLS'][$navRecordGroup] = $uri->getUri();

    /** установка хидденов*/
    if ($i > $noneHiddenCount && $navRecordGroup !== $curPage) {
        $arResult['HIDDEN'][$navRecordGroup] = ' hidden';
    }

    /** установка метки точек */
    if ($countPages > $countItemsBetweenDot + 1) {
        if ($navRecordGroup === 1) {
            if ($curPage >= ($countItemsBetweenDot - 1)) {
                $arResult['START_BETWEEN_BEGIN'] = 1;
                $arResult['START_BETWEEN_END'] = $navRecordGroup = $curPage - $leftCount;
                $i = 0;
            } elseif ($curPage >= ($countPages - $leftCount)) {
                $arResult['START_BETWEEN_BEGIN'] = 1;
                $arResult['START_BETWEEN_END'] = $navRecordGroup = $countPages - ($countItemsBetweenDot - 1);
                $arResult['END_BETWEEN_BEGIN'] = $arResult['END_BETWEEN_END'] = -1;
                $i = 0;
                continue;
            }
        } elseif ($navRecordGroup === $countItemsBetweenDot && $curPage < ($countItemsBetweenDot - 1)) {
            $arResult['START_BETWEEN_BEGIN'] = $navRecordGroup;
            $arResult['START_BETWEEN_END'] = $navRecordGroup = $countPages;
            $arResult['END_BETWEEN_BEGIN'] = $arResult['END_BETWEEN_END'] = -1;
            $i = 0;
            continue;
        }

        if ($navRecordGroup === ($curPage + $leftCount) && $curPage >= ($countItemsBetweenDot - 1)
            && $navRecordGroup !== $countPages && $arResult['END_BETWEEN_BEGIN'] === 0) {

            $arResult['END_BETWEEN_BEGIN'] = $navRecordGroup;
            $arResult['END_BETWEEN_END'] = $navRecordGroup = $countPages;
            $i = 0;
        }
    }
    $navRecordGroup++;
}
