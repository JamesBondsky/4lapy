<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */

// контроль данных: в PRODUCTS откуда-то появляются boolean
$tmp = isset($arResult['PRODUCTS']) && is_array($arResult['PRODUCTS']) ? $arResult['PRODUCTS'] : [];
$arResult['PRODUCTS'] = [];
if ($tmp) {
    foreach ($tmp as $key => $item) {
        if ($item instanceof \FourPaws\Catalog\Model\Product) {
            $arResult['PRODUCTS'][] = $item;
        }
    }
}
