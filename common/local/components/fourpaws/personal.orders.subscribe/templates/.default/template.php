<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

switch ($arResult['CURRENT_STAGE']) {
    case 'intro':
        // страница с описанием
        include __DIR__.'/stage.intro.php';
        break;
    case 'list':
        // страница со списком подписанных заказов
        include __DIR__.'/stage.list.php';
        break;
}
