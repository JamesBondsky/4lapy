<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\WordHelper;

/**
 * @var array $pickup
 */

/**
 * @var PickupResult $pickupResult
 */
$pickupResult = $pickup['RESULT'];

?>

<li class="b-product-information__item">
    <div class="b-product-information__title-info">Самовывоз</div>
    <div class="b-product-information__value b-product-information__value--link disabled js-open-tab-link" data-tab="availability">
        <?php if ($pickup['CODE'] === DeliveryService::INNER_PICKUP_CODE) {
            $totalCount = $pickup['SHOP_COUNT']['TOTAL'];
            $availableCount = $pickup['SHOP_COUNT']['AVAILABLE'];
            $hasToday = $pickup['SHOP_COUNT']['HAS_TODAY'];
            $unavailableCount = $pickup['SHOP_COUNT']['TOTAL'] - $pickup['SHOP_COUNT']['AVAILABLE'];
            if ($availableCount) {
                if ($hasToday) { ?>
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
                        'SHOW_TIME'   => $hasToday,
                    ]);
                } else { ?>
                    <?= DeliveryTimeHelper::showByDate($pickup['DELIVERY_DATE'], 0, [
                        'DATE_FORMAT' => 'XX',
                        'SHOW_TIME'   => $hasToday,
                    ]); ?> из <?= $availableCount . ' ' . WordHelper::declension(
                        (int)$availableCount,
                        [
                            'магазина',
                            'магазинов',
                            'магазинов',
                        ]
                    );
                }
                if ($unavailableCount) { ?>
                    <br>
                    и из <?= $unavailableCount ?> <?= WordHelper::declension(
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
    <div class="b-product-information__value">
        <?=$pickupResult->getTextForOffer()?>
    </div>
</li>
