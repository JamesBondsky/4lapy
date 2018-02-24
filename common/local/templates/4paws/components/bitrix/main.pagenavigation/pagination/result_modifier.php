<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$countItemsBetweenDot = 5;
$leftCount = 2;
$noneHiddenCount = 3;

$arResult['HIDDEN'] = [];
$page = 1;
$i                  = 0;

$arResult['START_BETWEEN_BEGIN'] = 0;
$arResult['START_BETWEEN_END'] = 0;
$arResult['END_BETWEEN_BEGIN'] = 0;
$arResult['END_BETWEEN_END'] = 0;

while ($page <= (int)$arResult['END_PAGE']) {
    $i++;
    /** установка хидденов*/
    if ($i > $noneHiddenCount && $page !== $arResult['CURRENT_PAGE']) {
        $arResult['HIDDEN'][$page] = ' hidden';
    }

    /** установка метки точек */
    if ($arResult['END_PAGE'] > $countItemsBetweenDot + 1) {
        if ($page === 1) {
            if ($arResult['CURRENT_PAGE'] >= ($countItemsBetweenDot - 1)) {
                $arResult['START_BETWEEN_BEGIN'] = 1;
                $arResult['START_BETWEEN_END'] = $page = $arResult['CURRENT_PAGE'] - $leftCount;
                $i = 0;
            } elseif ($arResult['CURRENT_PAGE'] >= ($arResult['END_PAGE'] - $leftCount)) {
                $arResult['START_BETWEEN_BEGIN'] = 1;
                $arResult['START_BETWEEN_END'] = $page = $arResult['END_PAGE'] - ($countItemsBetweenDot - 1);
                $arResult['END_BETWEEN_BEGIN'] = $arResult['END_BETWEEN_END'] = -1;
                $i = 0;
                continue;
            }
        } elseif ($page === $countItemsBetweenDot && $arResult['CURRENT_PAGE'] < ($countItemsBetweenDot - 1)) {
            $arResult['START_BETWEEN_BEGIN'] = $page;
            $arResult['START_BETWEEN_END'] = $page = $arResult['END_PAGE'];
            $arResult['END_BETWEEN_BEGIN'] = $arResult['END_BETWEEN_END'] = -1;
            $i = 0;
            continue;
        }

        if ($page === ($arResult['CURRENT_PAGE'] + $leftCount) && $arResult['CURRENT_PAGE'] >= ($countItemsBetweenDot - 1)
            && $page !== $arResult['END_PAGE'] && $arResult['END_BETWEEN_BEGIN'] === 0) {

            $arResult['END_BETWEEN_BEGIN'] = $page;
            $arResult['END_BETWEEN_END'] = $page = $arResult['END_PAGE'];
            $i = 0;
        }
    }
    $page++;
}
