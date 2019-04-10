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

/** @var ArrayCollection $coupons */
$coupons = $arResult['COUPONS'];
/** @var ArrayCollection $offers */
$offers = $arResult['OFFERS'];

if ($coupons->isEmpty()) {
    include __DIR__ . '/offers.empty.php';
} else {
    include __DIR__ . '/offers.list.php';
}
