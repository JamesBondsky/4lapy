<?php
/**
 * Created by PhpStorm.
 * Date: 05.02.2018
 * Time: 17:52
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */
echo 1488;
if ($arResult['CURRENT']['DELIVERY']['FREE_FROM']) {
    ?>
    <p class="b-information-order__additional-info">
        До бесплатной доставки осталось <?= $arResult['CURRENT']['DELIVERY']['FREE_FROM'] ?> ₽
    </p>
<?php
}