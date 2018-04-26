<?php

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
 */

?>
<div class="b-account__accordion b-account__accordion--subscribe">
    <ul class="b-account__accordion-order-list">
        <?php
        /** @var ArrayCollection $subscriptions */
        $subscriptions = $arResult['SUBSCRIPTIONS'];
        foreach ($arResult['ORDERS'] as $order) {
            /** @var Order $order */
            $orderSubscribe = $subscriptions->get($order->getId());
            if (!$orderSubscribe) {
                continue;
            }
            $APPLICATION->IncludeComponent(
                'fourpaws:personal.order.item',
                '',
                [
                    'ORDER' => $order,
                    'ORDER_SUBSCRIBE' => $orderSubscribe,
                    'METRO' => $arResult['METRO']
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
