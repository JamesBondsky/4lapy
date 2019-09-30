<?php

use Bitrix\Main\Grid\Declension;
use FourPaws\Decorators\SvgDecorator;
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

$marksDeclension = new Declension('марку', 'марки', 'марок');
?>

<div class="b-container pers-marks-container" style="width: 100%;">
    <div class="section-marks-comfortable-living__content section-marks-comfortable-living__content--profile">
        <div class="info-comfortable-living__img" style="background-image: url('/home/img/stamps_banner@2x.png')"></div>
        <div class="marks-comfortable-living">
            <?php foreach ($arResult['PROGRESS_BAR'] as $bar) { ?>
                <div class="item <?= ($bar['AVAILABLE']) ? 'item_active' : '' ?> <?= (!empty($bar['BONUS'])) ? 'item_discount' : '' ?>">
                    <?php if (!empty($bar['BONUS'])) { ?>
                        <div class="item__title">-<?= $bar['BONUS'] ?>%</div>
                    <?php } ?>
                    <div class="item__mark"></div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="balance-comfortable-living balance-comfortable-living--profile">
        <div class="balance-comfortable-living__info">
            <div class="balance-comfortable-living__user-mark">
                <span>Мои марки</span>
                <span class="count"><?= $arResult['ACTIVE_STAMPS_COUNT'] ?></span>
                <span class="b-icon b-icon--mark">
                        <?= new SvgDecorator('icon-mark', 24, 24) ?>
                    </span>
            </div>
            <div class="balance-comfortable-living__discount">Моя скидка - <?= $arResult['CURRENT_DISCOUNT'] ?>%</div>
        </div>
        <div class="balance-comfortable-living__primary" style="color: #f15e3a;">
            <?php if ($arResult['NEXT_DISCOUNT'] > 0) { ?>
                До скидки -<?= $arResult['NEXT_DISCOUNT'] ?>% осталось <?= $arResult['NEXT_DISCOUNT_STAMPS_NEED'] ?> <?= $marksDeclension->get($arResult['NEXT_DISCOUNT_STAMPS_NEED']) ?>
            <?php } else { ?>
                Доступна максимальная скидка
            <?php } ?>
        </div>

	    <div class="balance-comfortable-living__buy-dates">
		    <b>Копи марки</b> c 1 октября до 30 ноября 2019 г.<br>
		    <div class="balance-comfortable-living-legend__item">
			    <div class="balance-comfortable-living-legend__num balance-comfortable-living-legend__text">1</div>
			    <div class="balance-comfortable-living-legend__icon">
                            <span class="b-icon b-icon--mark">
                                <?= new SvgDecorator('icon-mark', 24, 24) ?>
                            </span>
			    </div>
			    <div class="balance-comfortable-living-legend__discount balance-comfortable-living-legend__text">за <?= StampService::MARK_RATE ?> ₽ в чеке</div>
		    </div>
	    </div>

        <div class="balance-comfortable-living__buy-dates">
	        <b>Покупай домики, лежаки и когтеточки со скидкой до -<?= $arResult['MAX_DISCOUNT'] ?>%</b>
	        <br>
	        с 1 октября до 15 декабря 2019 г.
        </div>

        <div class="balance-comfortable-living__legend balance-comfortable-living-legend">
            <?php foreach ($arResult['STAMP_LEVELS'] as $stamps => $discount) { ?>
                <div class="balance-comfortable-living-legend__item">
                    <div class="balance-comfortable-living-legend__num balance-comfortable-living-legend__text"><?= $stamps ?></div>
                    <div class="balance-comfortable-living-legend__icon">
                        <span class="b-icon b-icon--mark">
                            <?= new SvgDecorator('icon-mark', 24, 24) ?>
                        </span>
                    </div>
                    <div class="balance-comfortable-living-legend__discount balance-comfortable-living-legend__text">= <?= $discount ?>%</div>
                </div>
            <?php } ?>
        </div>

        <div class="balance-comfortable-living__conditions-wrap">
            <a href="/home/" target="_blank" class="balance-comfortable-living__conditions">Подробные условия</a>
        </div>
    </div>
</div>
