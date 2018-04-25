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
/** @var ArrayCollection $activeOrders */

if (!$activeOrders->isEmpty()) {
    ?>
    <div class="b-account__accordion">
        <div class="b-account__title">Текущие</div>
        <ul class="b-account__accordion-order-list">
            <?php
            foreach ($activeOrders as $order) {
                $APPLICATION->IncludeComponent(
                    'fourpaws:personal.order.item',
                    '',
                    [
                        'ORDER' => $order,
                    ],
                    $component,
                    [
                        'HIDE_ICONS' => 'Y'
                    ]
                );
            }
            ?>
        </ul>
    </div>
    <?php
}

if (!$closedOrders->isEmpty()) {
    ?>
    <div class="b-account__accordion b-account__accordion--last">
        <div class="b-account__title">Завершенные</div>
        <ul class="b-account__accordion-order-list">
            <?php
            foreach ($closedOrders as $order) {
                $APPLICATION->IncludeComponent(
                    'fourpaws:personal.order.item',
                    '',
                    [
                        'ORDER' => $order,
                    ],
                    $component,
                    [
                        'HIDE_ICONS' => 'Y'
                    ]
                );
            }
            ?>
        </ul>
    </div>
    <?php
    if (!empty($arResult['NAV'])) {
        ?>
        <div class="b-pagination b-pagination--account">
            <?php
            $APPLICATION->IncludeComponent(
                'bitrix:main.pagenavigation',
                'pagination',
                [
                    'NAV_OBJECT' => $arResult['NAV'],
                    'SEF_MODE' => 'N',
                    'AJAX_MODE' => 'N',
                ],
                $component,
                [
                    'HIDE_ICONS' => 'Y'
                ]
            );
            ?>
        </div>
        <?php
    }
}
