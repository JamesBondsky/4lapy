<?php

use Doctrine\Common\Collections\ArrayCollection;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var ArrayCollection $closedOrders */
$closedOrders = $arResult['CLOSED_ORDERS'];
/** @var ArrayCollection $activeOrders */
$activeOrders = $arResult['ACTIVE_ORDERS'];
if ($closedOrders->isEmpty() && $activeOrders->isEmpty()) {
    return;
}
?>
<div class="b-account__accordion">
    <div class="b-account__title">Текущие</div>
    <ul class="b-account__accordion-order-list">
        <?php foreach ($activeOrders as $order) {
            require_once 'include/order.php';
        } ?>
    </ul>
</div>
<div class="b-account__accordion b-account__accordion--last">
    <div class="b-account__title">Завершенные</div>
    <ul class="b-account__accordion-order-list">
        <?php foreach ($closedOrders as $order) {
            require_once 'include/order.php';
        } ?>
    </ul>
</div>
<? /** постраничная навигация */ ?>
<div class="b-pagination b-pagination--account">
    <ul class="b-pagination__list">
        <li class="b-pagination__item b-pagination__item--prev b-pagination__item--disabled"><span
                    class="b-pagination__link">Назад</span>
        </li>
        <li class="b-pagination__item"><a class="b-pagination__link" href="javascript:void(0);" title="1">1</a>
        </li>
        <li class="b-pagination__item"><a class="b-pagination__link" href="javascript:void(0);" title="2">2</a>
        </li>
        <li class="b-pagination__item b-pagination__item--next"><a class="b-pagination__link" href="javascript:void(0);"
                                                                   title="Вперед">Вперед</a>
        </li>
    </ul>
</div>