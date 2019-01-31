<?php

use Bitrix\Main\UI\PageNavigation;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\PersonalBundle\Entity\Order;

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
?>
<div class="b-account__accordion b-account__accordion--mobile-white personal_order_list" id="personal-order-list">
    <?
    if (!$orders->isEmpty()) {
        ?>
        <div class="b-account__accordion b-account__accordion--last">
            <?
            /** @var Order $firstOrder */
            $firstOrder = $orders->first();
            $firstOrderDateUpdate = \DateTime::createFromFormat('d.m.Y H:i:s', $firstOrder->getDateUpdate()->toString());
            $currentMinusMonthDate = (new \DateTime)->modify('-1 month');
            $activeTitleShow = false;
            ?>
            <?
            if ($firstOrderDateUpdate >= $currentMinusMonthDate){ ?>
            <div class="b-account__title">Текущие</div>
            <ul class="b-account__accordion-order-list">
                <?
                $activeTitleShow = true;
                ?>
                <? } ?>
                <?php
                $historyTitleShow = false;
                foreach ($orders

                as $order) {
                $orderDateUpdate = \DateTime::createFromFormat('d.m.Y H:i:s', $order->getDateUpdate()->toString());
                ?>
                <? if ($orderDateUpdate < $currentMinusMonthDate && !$historyTitleShow){ ?>
                <?
                $historyTitleShow = true;
                if ($activeTitleShow) { ?>
            </ul>
        <? } ?>
            <div class="b-account__title">История</div>
            <ul class="b-account__accordion-order-list">
                <? } ?>
                <?
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
                ?>
                <? } ?>
            </ul>
        </div>
    <?php }
    if ($arResult['NAV'] instanceof CDBResult) { ?>
        <div class="b-container b-container--personal-orders">
            <div class="b-pagination">
                <?php
                $APPLICATION->IncludeComponent(
                    'bitrix:system.pagenavigation',
                    'personal_order_pagination',
                    [
                        'NAV_TITLE' => '',
                        'NAV_RESULT' => $arResult['NAV'],
                        'SHOW_ALWAYS' => false,
                        'PAGE_PARAMETER' => 'page',
                        'AJAX_MODE' => 'N',
                    ],
                    null
                );
                ?>
            </div>
        </div>
    <?php } ?>
</div>
