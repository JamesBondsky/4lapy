<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global \CMain                 $APPLICATION
 * @var array                     $arParams
 * @var array                     $arResult
 * @var \CBitrixComponentTemplate $this
 */

// �������
$arParams['NOT_SHOW_LINKS']        = 'Y';
$arParams['NEW_USER_REGISTRATION'] = 'N';
$arParams['NOT_SHOW_LINKS']        = 'Y';
$arResult['AUTH_SERVICES']         = [];

$this->getComponent()->arParams = $arParams;
