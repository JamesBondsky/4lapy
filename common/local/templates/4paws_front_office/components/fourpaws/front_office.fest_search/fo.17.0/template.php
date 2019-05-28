<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain                                     $APPLICATION
 * @var array                                        $arParams
 * @var array                                        $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate                     $this
 * @var string                                       $templateName
 * @var string                                       $componentPath
 */

switch ($arResult['CURRENT_STAGE']) {
    case 'initial':
        // стартовая страница
        include __DIR__ . '/stage.initial.php';
        break;
    
    case 'user_search':
        // поиск участника (ajax)
        include __DIR__ . '/stage.user_search.php';
        break;
    
    case 'user_update':
        // апдейт данных участника (ajax, json response)
        include __DIR__ . '/stage.user_update.php';
        break;
}
