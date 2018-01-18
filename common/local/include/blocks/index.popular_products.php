<?php
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок на главной странице: популярные товары
 * @updated: 15.01.2018
 */

/** @global $APPLICATION */

$APPLICATION->IncludeComponent(
    'fourpaws:catalog.popular.products',
    'fp.17.0.homepage',
    [],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);
