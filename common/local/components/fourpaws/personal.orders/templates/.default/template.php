<?php

use Doctrine\Common\Collections\ArrayCollection;

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

/** @var ArrayCollection $orders */
$orders = $arResult['ORDERS'];

if ($orders->isEmpty()) {
    include __DIR__ . '/stage.empty.php';
} else {
    include __DIR__ . '/stage.list.php';
}
