<?php

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;

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

        /** @var OrderSubscribe $orderSubscribe */
        foreach ($subscriptions as $orderSubscribe) {
            $APPLICATION->IncludeComponent(
                'fourpaws:personal.order.item',
                'subscribe',
                [
                    'ORDER' => $orderSubscribe->getOrder(),
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
