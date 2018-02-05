<?php
/**
 * Created by PhpStorm.
 * Date: 05.02.2018
 * Time: 17:52
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */
if ($arResult['CURRENT']['DELIVERY']['FREE_FROM']) {
    $diff = (float)$arResult['CURRENT']['DELIVERY']['FREE_FROM'] - (float)$arParams['BASKET_PRICE'];
    if ($diff > 0.01) {
        ?>
        <p class="b-information-order__additional-info">
            До бесплатной доставки осталось <?= number_format($diff, 2, '.', ' '); ?> ₽
        </p>
        <?php
    } else {
        ?>
        <p class="b-information-order__additional-info">
            Бесплатная доставка
        </p>
    <?php }
}