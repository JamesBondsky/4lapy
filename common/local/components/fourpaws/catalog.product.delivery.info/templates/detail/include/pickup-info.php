<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\WordHelper;

/**
 * @var array $pickup
 */

?>

<li class="b-product-information__item">
    <div class="b-product-information__title-info">Самовывоз
    </div>
    <div class="b-product-information__value">
        <?php if ($pickup['CODE'] == DeliveryService::INNER_PICKUP_CODE) { ?>
            #PICKUP_DATE#
            <?php if ($pickup['SHOP_COUNT']) { ?>
                из <?= $pickup['SHOP_COUNT'] . ' ' . WordHelper::declension(
                    (int)$pickup['SHOP_COUNT'],
                    ['магазина', 'магазинов', 'магазинов']
                ); ?>
            <?php } ?>
        <?php } else { ?>
            В течение <?= $pickup['PERIOD_FROM'] ?>&nbsp;<?php WordHelper::declension(
                $pickup['PERIOD_FROM'],
                ['день', 'дня', 'дней']
            ) ?>
        <?php } ?>
    </div>
</li>
