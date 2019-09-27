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
 * @var CStampsProgressBar $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */
dump($arResult);
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
                <?php foreach ($arResult['PROGRESS_BAR'] as $item){ ?>
                    <?php if ($item['BONUS']) { ?>
                        - <?= $item['BONUS'] ?>%
                    <?php } ?>

                    <div class="<?= ($item['BONUS']) ? 'bonus' : '' ?>">
                        <?php if ($item['AVAILABLE']) { ?>
                            кружок марки
                        <?php } else { ?>
                            <?php if ($item['BONUS']) { ?>
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
</div>
