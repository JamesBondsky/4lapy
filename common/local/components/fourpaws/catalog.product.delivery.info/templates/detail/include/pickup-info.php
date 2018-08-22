<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\WordHelper;

/**
 * @var array $pickup
 */

?>

<li class="b-product-information__item">
    <div class="b-product-information__title-info">Самовывоз</div>
    <div class="b-product-information__value">
        <?php if ($pickup['CODE'] === DeliveryService::INNER_PICKUP_CODE) { ?>
            <?php
            $totalCount = $pickup['SHOP_COUNT']['TOTAL'];
            $availableCount = $pickup['SHOP_COUNT']['AVAILABLE'];
            $unavailableCount = $pickup['SHOP_COUNT']['TOTAL'] - $pickup['SHOP_COUNT']['AVAILABLE'];
            if ($availableCount) { ?>
                из <?= $availableCount . ' ' . WordHelper::declension(
                    (int)$availableCount,
                    [
                        'магазина',
                        'магазинов',
                        'магазинов',
                    ]
                ); ?>
                <?= DeliveryTimeHelper::showByDate($pickup['DELIVERY_DATE'], 0, [
                    'DATE_FORMAT' => 'XX',
                    'SHOW_TIME'   => $pickup['SHOP_COUNT']['HAS_TODAY'],
                ]);
                if ($unavailableCount) { ?>
                    <br>
                    и из <?= $unavailableCount?> <?= WordHelper::declension(
                        (int)$unavailableCount,
                        [
                            'магазина',
                            'магазинов',
                            'магазинов',
                        ]
                    ); ?> позже
                    <?php
                }
            } else { ?>
                из <?= $totalCount . ' ' . WordHelper::declension(
                    (int)$totalCount,
                    [
                        'магазина',
                        'магазинов',
                        'магазинов',
                    ]
                ); ?>
                <?= DeliveryTimeHelper::showByDate($pickup['DELIVERY_DATE'], 0, [
                    'DATE_FORMAT' => 'XX',
                    'SHOW_TIME'   => false,
                ]) ?>
            <?php } ?>
        <?php } else { ?>
            <?= DeliveryTimeHelper::showByDate($pickup['DELIVERY_DATE'], 0, ['DATE_FORMAT' => 'XX']) ?>
        <?php } ?>
    </div>
</li>
