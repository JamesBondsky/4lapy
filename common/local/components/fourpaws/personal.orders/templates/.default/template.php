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
    /** @todo нужен шаблон пустых заказов */
    return;
}
?>
<?php if (!$activeOrders->isEmpty()) { ?>
    <div class="b-account__accordion">
        <div class="b-account__title">Текущие</div>
        <ul class="b-account__accordion-order-list">
            <?php foreach ($activeOrders as $order) {
                require_once 'include/order.php';
            } ?>
        </ul>
    </div>
<?php } ?>
<?php if (!$closedOrders->isEmpty()) { ?>
    <div class="b-account__accordion b-account__accordion--last">
        <div class="b-account__title">Завершенные</div>
        <ul class="b-account__accordion-order-list">
            <?php foreach ($closedOrders as $order) {
                require_once 'include/order.php';
            } ?>
        </ul>
    </div>
    <?php if (!empty($arResult['NAV'])) { ?>
        <div class="b-pagination b-pagination--account">
            <?php
            $APPLICATION->IncludeComponent(
                'bitrix:main.pagenavigation',
                'pagination',
                [
                    'NAV_OBJECT' => $arResult['NAV'],
                    'SEF_MODE'   => 'Y',
                ],
                $component,
                ['HIDE_ICONS' => 'Y']
            );
            ?>
        </div>
    <?php } ?>
<?php } ?>
