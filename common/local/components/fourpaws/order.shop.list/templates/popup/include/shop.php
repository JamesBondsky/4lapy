<?php

use FourPaws\Decorators\SvgDecorator;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\DeliveryBundle\Entity\StockResult;

/**
 * @var array $arResult
 * @var StockResultCollection $stockResult
 * @var Store $shop
 */

$available = $stockResult->getAvailable();
$delayed = $stockResult->getDelayed();

$metro = $arResult['METRO'][$shop->getMetro()];
?>
<li class="b-delivery-list__item">
    <a class="b-delivery-list__link js-shop-link b-active"
       id="shop_id<?= $shop->getXmlId() ?>"
       data-shop-id="<?= $shop->getXmlId() ?>"
       href="javascript:void(0);"
       title="">
        <span class="b-delivery-list__col b-delivery-list__col--addr">
            <?php if ($metro) { ?>
                <span class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--<?= $metro['BRANCH']['UF_CLASS'] ?>"></span>
            <?php } ?>
            <?= $shop->getAddress() ?>
        </span>
        <span class="b-delivery-list__col b-delivery-list__col--time"><?= $shop->getSchedule() ?></span>
        <?php if (!$available->isEmpty()) { ?>
            <span class="b-delivery-list__col b-delivery-list__col--self-picked">
                <?php
                $deliveryDate = $available->getDeliveryDate();
                $str = getDateDiffString($currentDate, $deliveryDate);
                ?>
                заказ можно забрать <?= $str ?>
            </span>
        <?php } else { ?>
            <span class="b-delivery-list__col b-delivery-list__col--self-picked">
                полный заказ будет доступен <?= mb_strtolower(
                    FormatDate(
                        'd.m (D) с H:00',
                        $stockResult->getDeliveryDate()->getTimestamp()
                    )
                ) ?>
            </span>
        <?php } ?>
    </a>
    <div class="b-order-info-baloon">
        <a class="b-link b-link--popup-back b-link--order b-link--desktop js-close-order-baloon"
           href="javascript:void(0);"
           title="">
            <span class="b-icon b-icon--back-long b-icon--balloon">
                <?= new SvgDecorator('icon-back-form', 13, 11) ?>
            </span>
            Вернуться к списку
        </a>
        <a class="b-link b-link--popup-back b-link--baloon js-close-order-baloon"
           href="javascript:void(0);">Пункт самовывоза</a>
        <div class="b-order-info-baloon__content js-order-info-baloon-scroll">
            <ul class="b-delivery-list">
                <li class="b-delivery-list__item b-delivery-list__item--myself">
                    <span class="b-delivery-list__link b-delivery-list__link--myself">
                        <?php if ($metro) { ?>
                            <span class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--<?= $metro['BRANCH']['UF_CLASS'] ?>"></span>
                        <?php } ?>
                        <?= $shop->getAddress() ?>
                    </span>
                </li>
            </ul>
            <div class="b-input-line b-input-line--myself">
                <div class="b-input-line__label-wrapper">
                    <span class="b-input-line__label">Время работы</span>
                </div>
                <div class="b-input-line__text-line b-input-line__text-line--myself">
                    <?= $shop->getSchedule() ?>
                </div>
            </div>
            <?php if (!$available->isEmpty()) { ?>
                <div class="b-input-line b-input-line--myself">
                    <div class="b-input-line__label-wrapper">
                        <? $str = getDateDiffString(
                            $currentDate,
                            $available->getDeliveryDate()
                        ) ?>
                        <?php if ($delayed->isEmpty()) { ?>
                            <span class="b-input-line__label"> Можно забрать <?= $str ?></span>
                        <?php } else { ?>
                            <span class="b-input-line__label"> Можно забрать <?= $str ?>
                                , кроме </span>
                            <ol class="b-input-line__text-list">
                                <?php /** @var StockResult $delayedItem */ ?>
                                <?php foreach ($delayed as $delayedItem) { ?>
                                    <li class="b-input-line__text-item">
                                        <?php $delayedItem->getOffer()
                                                          ->getProduct()
                                                          ->getName() ?>
                                        <?php if ($delayedItem->getAmount() > 1) { ?>
                                            (<?= $delayedItem->getAmount() ?> шт)
                                        <?php } ?>
                                    </li>
                                <?php } ?>
                            </ol>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <?php if (!$delayed->isEmpty()) { ?>
                <div class="b-input-line b-input-line--myself">
                    <div class="b-input-line__label-wrapper">
                        <span class="b-input-line__label"> Полный заказ будет доступен </span>
                    </div>
                    <div class="b-input-line__text-line">
                        <?= mb_strtolower(
                            FormatDate(
                                'd.m (D) с H:00',
                                $stockResult->getDeliveryDate()->getTimestamp()
                            )
                        ) ?>
                    </div>
                </div>
            <?php } ?>
            <div class="b-input-line b-input-line--myself">
                <div class="b-input-line__label-wrapper">
                    <span class="b-input-line__label"> Оплата в магазине </span>
                </div>
                <div class="b-input-line__text-line">
                    <span class="b-input-line__pay-type">
                        <span class="b-icon b-icon--icon-cash">
                            <?= new SvgDecorator('icon-cash', 16, 12) ?>
                        </span>
                        наличными
                    </span>
                    <span class="b-input-line__pay-type">
                        <span class="b-icon b-icon--icon-bank">
                            <?= new SvgDecorator('icon-bank-card', 16, 12) ?>
                        </span>
                        банковской картой
                    </span>
                </div>
            </div>
            <div class="b-input-line b-input-line--pin">
                <a class="b-link b-link--pin js-shop-link"
                   href="javascript:void(0);"
                   title="">
                    <span class="b-icon b-icon--pin">
                        <?= new SvgDecorator('icon-geo', 16, 16) ?>
                    </span>
                    Показать на карте
                </a>
            </div>
            <a class="b-button b-button--order-balloon js-shop-myself"
               href="javascript:void(0);"
               title=""
               data-shopId="<?= $shop->getXmlId() ?>"
               data-url="json/mapobjects-order-shop.json">
                Выбрать этот пункт самовывоза
            </a>
        </div>
    </div>
</li>
