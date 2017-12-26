<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array $arParams
 * @var array $arResult
 * @var array $templateData
 *
 * @var string $componentPath
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 *
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

$cities = [];
foreach ($arResult['MOSCOW_CITIES'] as $city) {
    $letter = mb_strtoupper(mb_substr($city['NAME'], 0, 1));
    $cities[$letter][] = $city;
}
$arResult['MOSCOW_CITIES'] = $cities;

$cities = [];
foreach ($arResult['POPULAR_CITIES'] as $city) {
    $letter = mb_strtoupper(mb_substr($city['NAME'], 0, 1));
    $cities[$letter][] = $city;
}
$arResult['POPULAR_CITIES'] = $cities;
