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

/** @var ArrayCollection $closedOrders */
$closedOrders = $arResult['CLOSED_ORDERS'] ?? null;
/** @var ArrayCollection $activeOrders */
$activeOrders = $arResult['ACTIVE_ORDERS'] ?? null;

if (!$closedOrders || !$activeOrders) {
    // какая-то ошибка произошла в компоненте
    return;
}

if ($closedOrders->isEmpty() && $activeOrders->isEmpty()) {
    include __DIR__ . '/stage.empty.php';
    return;
} else {
    include __DIR__ . '/stage.list.php';
}
