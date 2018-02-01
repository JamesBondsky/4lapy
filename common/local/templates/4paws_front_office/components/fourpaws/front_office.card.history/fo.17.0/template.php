<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var FourPawsFrontOfficeCardRegistrationComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

switch ($arResult['CURRENT_STAGE']) {
    case 'initial':
        // стартовая страница
        include __DIR__.'/stage.initial.php';
        break;

    case 'history':
        // запрос истории по карте (ajax)
        include __DIR__.'/stage.history.php';
        break;

    case 'cheque_details':
        // запрос детализации чека (ajax)
        include __DIR__.'/stage.cheque_details.php';
        break;

    case 'print':
        // запрос полной версии для печати
        include __DIR__.'/stage.print.php';
        break;
}
