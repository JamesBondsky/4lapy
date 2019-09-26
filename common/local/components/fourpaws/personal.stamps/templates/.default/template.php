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

    <img src="картинка слева">
    <div class="b-kopilka__details">
        <div class="b-kopilka__ticket-mark">
            <div>Мои марки</div>
            <div class="title-ticket-mark"><?= $arResult['ACTIVE_STAMPS_COUNT'] ?></div>
            <div class="descr-ticket-mark">
                Моя скидка - <?= $arResult['CURRENT_DISCOUNT'] ?>%
            </div>
        </div>
        <div class="b-kopilka__info">
            <?php if ($arResult['NEXT_DISCOUNT']) { ?>
                <div>
                    До скидки -<?= $arResult['NEXT_DISCOUNT'] ?>% осталось <?= $arResult['NEXT_DISCOUNT_STAMPS_NEED'] . ' ' . $marksDeclension->get($arResult['NEXT_DISCOUNT_STAMPS_NEED']) ?>
                </div>
            <?php } ?>
            <div class="descr-ticket-mark">
                С
                <nobr>1 окт</nobr>&nbsp;по
                <nobr>15 дек</nobr>
                2019 г
            </div>
            <div>
                Покупай домики, лежаки и когтеточки со скидкой до -<?= $arResult['MAX_DISCOUNT'] ?>
            </div>
            <div>
                <?php foreach ($arResult['STAMP_LEVELS'] as $stamps => $discount) { ?>
                    <?= $stamps ?> - <?= $discount ?>%
                <?php } ?>
            </div>
            <div>
                Копи марки с 1 окт до 30 ноября 2019
            </div>
            <div>
                1 за <?= StampService::MARK_RATE ?> ₽ в чеке
            </div>
            <a href="/home/">Пордобные условия</a>

            <div>
                <?php for ($i = 1; $i <= $arResult['MAX_STAMPS_COUNT']; $i++) { ?>
                    <?php $isBonus = isset($arResult['STAMP_LEVELS'][$i]); ?>
                    <?php $isAvailable = ($arResult['ACTIVE_STAMPS_COUNT'] >= $i) ?>

                    <?php if ($isBonus) { ?>
                        - <?= $arResult['STAMP_LEVELS'][$i] ?>%
                    <?php } ?>

                    <div class="<?= ($isBonus) ? 'bonus' : '' ?>">
                        <?php if ($isAvailable) { ?>
                            кружок марки
                        <?php } else { ?>
                            <?php if ($isBonus) { ?>
                                значок подарка
                            <?php } else { ?>
                                белый кружок
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
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
</div>
