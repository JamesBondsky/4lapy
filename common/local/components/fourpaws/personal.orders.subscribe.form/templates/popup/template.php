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
    case 'initial':
        // страница с контролами
        include __DIR__.'/stage.initial.php';
        break;
    case 'step1':
        // страница с описанием
        include __DIR__.'/stage.step1.php';
        break;
    case 'error':
        // страница с описанием
        include __DIR__.'/error.php';
        break;
}
