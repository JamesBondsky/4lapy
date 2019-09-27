<?php

use Bitrix\Main\Grid\Declension;
use FourPaws\PersonalBundle\Service\StampService;


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

$marksDeclension = new Declension('марку', 'марки', 'марок');
?>

<div class="b-kopilka b-kopilka--exchange-discount">
    <h2 class="b-title b-kopilka__title">Марки</h2>
    <?php
    $APPLICATION->IncludeComponent('articul:stamps.progress.bar', 'personal', []);
    ?>
</div>

<hr class="b-hr"/>

<?php
$APPLICATION->IncludeComponent('articul:catalog.section.slider', 'stamps', [
    'SECTION_CODE' => 'stamps'
]);
?>

<div>
    картинка

    1. Делай любые покупки, копи марки 1 (значок марки) = <?= StampService::MARK_RATE ?>₽
    2. Отслеживай баланс марок: на чеке, в личном кабинете и в приложении
    3. Покупай со скидкой до - <?= $arResult['MAX_DISCOUNT'] ?>

    - на сайте и в приложении: добавь товар в корзину, нажми "списать марки"
    - в магазине: предъяви буклет или сообщи кассиру номер телефона подробнее
</div>
