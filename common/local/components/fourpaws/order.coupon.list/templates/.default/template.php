<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<? if ($arResult['SHOW'] && count($arResult['COUPONS']) > 0) { ?>
    <div class="b-stock__coupons" data-basket-coupons>
        <button type="button" class="b-stock__coupons-btn" data-basket-coupons-toogle>Мои купоны</button>
        <div class="b-stock__coupons-popup loading" data-basket-coupons-popup>
            <div class="b-stock__coupons-list">
                <div class="b-stock__coupons-item">
                    <div class="b-stock__coupon">
                        <div class="b-stock__coupon-caption">
                            Скидка 50% на Maelfeal
                        </div>
                        <button type="button" class="b-stock__coupon-btn" data-basket-coupon-toogle="id_coupon1">Применить</button>
                    </div>
                </div>
                <div class="b-stock__coupons-item">
                    <div class="b-stock__coupon">
                        <div class="b-stock__coupon-caption">
                            Скидка 20% на Royal Canine
                        </div>
                        <button type="button" class="b-stock__coupon-btn" data-basket-coupon-toogle="id_coupon2">Применить</button>
                    </div>
                </div>
                <div class="b-stock__coupons-item">
                    <div class="b-stock__coupon">
                        <div class="b-stock__coupon-caption">
                            Скидка 35% на Euikanuba
                        </div>
                        <button type="button" class="b-stock__coupon-btn" data-basket-coupon-toogle="id_coupon3">Применить</button>
                    </div>
                </div>
                <div class="b-stock__coupons-item">
                    <div class="b-stock__coupon">
                        <div class="b-stock__coupon-caption">
                            Скидка 12% на все сумки
                        </div>
                        <button type="button" class="b-stock__coupon-btn" data-basket-coupon-toogle="id_coupon4">Применить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<? } ?>