<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arResult['HIDDEN'] = [];
$page = 1;
$i                  = 0;
while ($page <= (int)$arResult['END_PAGE']) {
    $i++;
    if ($i > 3 && (int)$arResult['START_PAGE'] <= 1) {
        $arResult['HIDDEN'][$page] = ' hidden';
    }
    if ((int)$arResult['START_PAGE']  > 1 && (int)$arResult['END_PAGE'] < ((int)$arResult['CURRENT_PAGE'] - 1)
        && ($page === (int)$arResult['START_PAGE']  || $page === (int)$arResult['END_PAGE'])) {
        $arResult['HIDDEN'][$page] = ' hidden';
    }
    if ($page > 1
        && $page <= (int)$arResult['CURRENT_PAGE'] - 3
        && (int)$arResult['END_PAGE'] >= ((int)$arResult['CURRENT_PAGE'] - 1)) {
        $arResult['HIDDEN'][$page] = ' hidden';
    }
    
    if ($page === 1 && (int)$arResult['START_PAGE']  > 1
        && (int)$arResult['START_PAGE']  - $page >= 0) {
        $page = (int)$arResult['START_PAGE'] ;
        $i              = 0;
    } elseif ($page === (int)$arResult['END_PAGE']
              && (int)$arResult['END_PAGE'] < ((int)$arResult['CURRENT_PAGE'] - 1)) {
        $page = (int)$arResult['CURRENT_PAGE'];
        $i              = 0;
    } else {
        $page++;
    }
}
