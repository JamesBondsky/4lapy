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

// контролы
if($arResult['CURRENT_STAGE'] == 'initial'){
    include __DIR__.'/stage.initial.php';
    return;
}

// форма
include __DIR__. '/header.php';

switch ($arResult['CURRENT_STAGE']) {
    case 'step1':
        // страница с описанием
        include __DIR__.'/stage.step1.php';
        break;
    case 'error':
        // страница с описанием
        include __DIR__.'/error.php';
        break;
}

include __DIR__. '/footer.php';
