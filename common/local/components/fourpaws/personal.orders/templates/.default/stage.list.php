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
 * @var ArrayCollection $orders
 */

if (!$orders->isEmpty()) {
    ?>
    <div class="b-account__accordion b-account__accordion--last">
        <div class="b-account__title">Завершенные</div>
        <ul class="b-account__accordion-order-list">
            <?php
            foreach ($orders as $order) {
                $APPLICATION->IncludeComponent(
                    'fourpaws:personal.order.item',
                    '',
                    [
                        'ORDER' => $order
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
<?php }
