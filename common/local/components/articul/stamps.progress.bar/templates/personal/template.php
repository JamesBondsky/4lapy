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

    <div class="b-container" style="100%">
        <div class="section-marks-comfortable-living__content">
            <div class="info-comfortable-living__img" style="background-image: url('/home/img/steps-info.jpg')"></div>
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

        <div class="balance-comfortable-living">
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
            <?php if ($arResult['NEXT_DISCOUNT'] > 0) { ?>
                <div class="balance-comfortable-living__primary">
                    до - <?= $arResult['NEXT_DISCOUNT'] ?>% осталось <?= $arResult['NEXT_DISCOUNT_STAMPS_NEED'] ?> марки
                </div>
            <?php } ?>
        </div>
    </div>

<?php
/**
 *

 вывод уровня марок:

 <?php foreach ($arResult['STAMP_LEVELS'] as $stamps => $discount) { ?>
 <?= $stamps ?> - <?= $discount ?>%
 <?php } ?>

 Моя скидка - <?= $arResult['CURRENT_DISCOUNT'] ?>%

 <?= $arResult['ACTIVE_STAMPS_COUNT'] ?>

 1 за <?= StampService::MARK_RATE ?> ₽ в чеке

 <?php if ($arResult['NEXT_DISCOUNT']) { ?>
 <div>
 До скидки -<?= $arResult['NEXT_DISCOUNT'] ?>% осталось <?= $arResult['NEXT_DISCOUNT_STAMPS_NEED'] . ' ' . $marksDeclension->get($arResult['NEXT_DISCOUNT_STAMPS_NEED']) ?>
 </div>
 <?php } ?>


 *
 */
