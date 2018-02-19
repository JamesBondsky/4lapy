<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 */

// Запрашиваемое представление страницы
$arResult['CURRENT_STAGE'] = 'initial';

$this->getComponent()->arParams = $arParams;

$arResult['TIME_VARIANTS'] = [
    [
        'VALUE' => 'TIME_1',
        'TEXT' => '10:00—16:00',
    ],
    [
        'VALUE' => 'TIME_2',
        'TEXT' => '16:00—16:00',
    ],
    [
        'VALUE' => 'TIME_3',
        'TEXT' => '18:00—20:00',
    ],
];

$arResult['FREQUENCY_VARIANTS'] = [
    [
        'VALUE' => 'WEEK_1',
        'TEXT' => 'Раз в неделю',
    ],
    [
        'VALUE' => 'WEEK_2',
        'TEXT' => 'Раз в две недели',
    ],
    [
        'VALUE' => 'WEEK_3',
        'TEXT' => 'Раз в три недели',
    ],
    [
        'VALUE' => 'MONTH_1',
        'TEXT' => 'Раз в месяц',
    ],
    [
        'VALUE' => 'MONTH_2',
        'TEXT' => 'Раз в два месяца',
    ],
    [
        'VALUE' => 'MONTH_3',
        'TEXT' => 'Раз в три месяца',
    ],
];
