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

switch ($arResult['CURRENT_STAGE']) {
    case 'step1':
        include __DIR__. '/header.php';
        include __DIR__. '/stage.step1.php';
        include __DIR__. '/footer.php';
        break;
    case 'step2':
        include __DIR__.'/stage.step2.php';
        break;
    case 'error':
        include __DIR__. '/header.php';
        include __DIR__. '/error.php';
        include __DIR__. '/footer.php';
        break;
}


