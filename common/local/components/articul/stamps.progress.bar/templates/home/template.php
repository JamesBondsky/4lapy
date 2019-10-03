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

$marksDeclension = new Declension('марка', 'марки', 'марок');
?>

<section class="section-marks-comfortable-living">
    <div class="b-container">
        <div class="section-marks-comfortable-living__content">
            <div class="marks-comfortable-living">
                <? foreach($arResult['PROGRESS_BAR'] as $bar) { ?>
                    <div class="item <?=($bar['AVAILABLE']) ? 'item_active' : ''?> <?=(!empty($bar['BONUS'])) ? 'item_discount' : ''?>">
                        <? if(!empty($bar['BONUS'])) { ?>
                            <div class="item__title">-<?=$bar['BONUS']?>%</div>
                        <? } ?>
                        <div class="item__mark"></div>
                    </div>
                <? } ?>
            </div>
            <div class="balance-comfortable-living">
                <div class="balance-comfortable-living__info">
                    <?if ($USER->IsAuthorized()) {?>
                        <div class="balance-comfortable-living__user-mark">
                            <span>Мои марки</span>
                            <span class="count"><?=$arResult['ACTIVE_STAMPS_COUNT']?></span>
                            <span class="b-icon b-icon--mark">
                                <?= new SvgDecorator('icon-mark', 24, 24) ?>
                            </span>
                        </div>
                        <div class="balance-comfortable-living__discount">Моя скидка - <?=$arResult['CURRENT_DISCOUNT']?>%</div>
                    <? } else { ?>
                        <div class="balance-comfortable-living__text">Узнайте ваш баланс марок</div>
                        <div class="balance-comfortable-living__btn js-open-popup" data-popup-id="authorization">Войти</div>
                    <? } ?>
                </div>
                <? if ($USER->IsAuthorized()) { ?>
                    <div class="balance-comfortable-living__primary">
                        <? if ($arResult['NEXT_DISCOUNT'] > 0) { ?>
                            До скидки -<?= $arResult['NEXT_DISCOUNT'] ?>% осталось <?= $arResult['NEXT_DISCOUNT_STAMPS_NEED'] ?> <?= $marksDeclension->get($arResult['NEXT_DISCOUNT_STAMPS_NEED']) ?>
                        <? } else { ?>
                            Доступна максимальная скидка
                        <? } ?>
                    </div>
                <? } ?>
            </div>
        </div>
    </div>
</section>
