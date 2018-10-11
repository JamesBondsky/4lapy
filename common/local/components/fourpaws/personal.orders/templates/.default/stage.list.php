<?php

use Doctrine\Common\Collections\ArrayCollection;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain                 $APPLICATION
 * @var array                    $arParams
 * @var array                    $arResult
 * @var CBitrixComponent         $component
 * @var CBitrixComponentTemplate $this
 * @var string                   $templateName
 * @var string                   $componentPath
 * @var ArrayCollection          $orders
 */

if (!$orders->isEmpty()) {
    ?>
    <div class="b-account__accordion b-account__accordion--last">
        <div class="b-account__title">Мои заказы</div>
        <ul class="b-account__accordion-order-list">
            <?php
            foreach ($orders as $order) {
                $APPLICATION->IncludeComponent(
                    'fourpaws:personal.order.item',
                    '',
                    [
                        'ORDER' => $order,
                    ],
                    $component,
                    [
                        'HIDE_ICONS' => 'Y',
                    ]
                );
            }
            ?>
        </ul>
    </div>
<?php }
if ($orders->count() < $arResult['TOTAL_ORDER_COUNT']) { ?>
    <div class="b-pagination b-pagination--account">
        <ul class="b-pagination__list">
            <li class="b-pagination__item b-pagination__item--next">
                <button class="b-pagination__link js-orders-more" href="javascript:void(0);" data-url="/ajax/personal/order/list/" data-page="2">Показать еще</button>
            </li>
        </ul>
    </div>
<?php } ?>
