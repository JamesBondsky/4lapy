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
    // контролы
    case 'initial':
        include __DIR__.'/stage.initial.php';
        break;
    // создание и редактирование
    case 'step1':
        include __DIR__. '/header.php';
        include __DIR__. '/stage.step1.php';
        include __DIR__. '/footer.php';
        break;
    case 'step2':
        include __DIR__. '/stage.step2.php';
        break;
    // возобновление подписки
    case 'renewal':
        include __DIR__. '/include/subscribe-delivery.php';
        break;
    // шаблон товара
    case 'item':
        foreach($component->getBasket() as $basketItem){
            include __DIR__. '/include/basketItem.php';
        }
        break;
    // ошибка
    case 'error':
        include __DIR__. '/header.php';
        include __DIR__. '/error.php';
        include __DIR__. '/footer.php';
        break;
}


