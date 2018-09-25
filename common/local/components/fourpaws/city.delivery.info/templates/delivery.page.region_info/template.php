<?php

use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 * @var array                     $templateData
 *
 * @var string                    $componentPath
 * @var string                    $templateName
 * @var string                    $templateFile
 * @var string                    $templateFolder
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
 */


$realZoneToZone = [
    DeliveryService::ZONE_1 => 1,
    DeliveryService::ZONE_5 => 2,
    DeliveryService::ZONE_6 => 3,
];

$delivery = $arResult['DELIVERY'];
$deliveryZone = $arResult['ZONE'];
/** @var DeliveryResultInterface[] $resultsByZone */
$resultsByZone = $arResult['RESULTS_BY_ZONE'];

if (isset($resultsByZone[$deliveryZone])) {
    $currentResult = $resultsByZone[$deliveryZone];
    unset($resultsByZone[$deliveryZone]);
    ?>
    <div class="b-delivery__info">
        <div class="b-delivery__info-item">
            <div class="b-delivery__info-icon"><img src="/static/build/images/inhtml/delivery-icon-01.png"></div>
            <div class="b-delivery__info-text">
                <strong>Бесплатно</strong><span>при заказе от <?= $currentResult->getFreeFrom() ?></span> ₽*
            </div>
        </div>
        <div class="b-delivery__info-item">
            <div class="b-delivery__info-icon"><img src="/static/build/images/inhtml/delivery-icon-02.png"></div>
            <div class="b-delivery__info-text"><strong>Далеко везём</strong><span>до 85 км от мкад</span></div>
        </div>
        <div class="b-delivery__info-item">
            <div class="b-delivery__info-icon"><img src="/static/build/images/inhtml/delivery-icon-03.png"></div>
            <div class="b-delivery__info-text"><strong>любой заказ</strong><span>на сумму от 1</span> ₽</div>
        </div>
        <div class="b-delivery__info-item">
            <div class="b-delivery__info-icon"><img src="/static/build/images/inhtml/delivery-icon-04.png"></div>
            <div class="b-delivery__info-text"><strong>Самовывоз</strong><span>210+ магазинов</span></div>
        </div>
    </div>
    <div class="b-delivery__town-content">
        <div class="b-delivery__town-map"><img src="/static/build/images/inhtml/delivery-map.png"></div>
        <div class="b-delivery__town-data">
            <div class="b-delivery__region">
                <div class="b-delivery__region-section b-delivery__region-section--border b-delivery__region-section--type2">
                    <div class="b-delivery__region-title"><img class="b-delivery__region-title-icon"
                                                               src="/static/build/images/inhtml/delivery-mark.gif">
                        Ваш город
                    </div>
                    <?php $APPLICATION->IncludeComponent(
                        'fourpaws:city.selector',
                        'delivery.page.moscow',
                        [
                            'CACHE_TIME'    => 3600,
                            'LOCATION_CODE' => $arParams['LOCATION_CODE'],
                        ],
                        false,
                        ['HIDE_ICONS' => 'Y']
                    );
                    $deliveryResult = $currentResult;
                    ?>
                    <div class="b-delivery__region-item b-delivery__region-item--type<?= $realZoneToZone[$deliveryResult->getDeliveryZone()] ?>">
                        <strong
                                class="b-delivery__region-item-count"><?= $realZoneToZone[$deliveryResult->getDeliveryZone()] ?></strong>
                        <?php
                        $deliveryResult = $currentResult;
                        $showHeader = true;
                        include 'delivery_info.php';
                        ?>
                    </div>
                    <a class="b-delivery__region-more" href="#more">Подробнее</a>
                </div>
                <div class="b-delivery__region-section">
                    <?php foreach ($resultsByZone as $deliveryResult) { ?>
                        <div class="b-delivery__region-item b-delivery__region-item--type<?= $realZoneToZone[$deliveryResult->getDeliveryZone()] ?>">
                            <strong
                                    class="b-delivery__region-item-count"><?= $realZoneToZone[$deliveryResult->getDeliveryZone()] ?></strong>
                            <?php
                            $showHeader = false;
                            include 'delivery_info.php';
                            ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php } else { ?>
    <?php $APPLICATION->IncludeComponent(
        'fourpaws:city.selector',
        'delivery.page',
        [
            'CACHE_TIME'    => 3600,
            'LOCATION_CODE' => $arParams['LOCATION_CODE'],
        ],
        false,
        ['HIDE_ICONS' => 'Y']
    ); ?>
<? } ?>
