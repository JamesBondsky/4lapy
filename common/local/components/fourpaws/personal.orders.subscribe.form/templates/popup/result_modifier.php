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

//$this->getComponent()->arParams = $arParams;

// Запрашиваемое представление страницы
$arResult['CURRENT_STAGE'] = 'initial';
