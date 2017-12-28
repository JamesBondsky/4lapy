<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
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

$this->setFrameMode(true);

if ($arResult['CURRENT']['PICKUP']) {
    $pickup = $arResult['CURRENT']['PICKUP'];
    include __DIR__ . '/include/pickup-info.php';
}
if ($arResult['CURRENT']['DELIVERY']) {
    $delivery = $arResult['CURRENT']['DELIVERY'];
    include __DIR__ . '/include/delivery-info.php';
}
