<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 * @var array                     $templateData
 *
 * @var string                    $componentPath
 * @var string                    $templateName
 * @var string                    $templateFile
 * @var string                    $templateFolder
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
 */

use FourPaws\Decorators\SvgDecorator;

?>
<div class="b-header-info__item b-header-info__item--cart">
    <a class="b-header-info__link" href="<?= $arParams['PATH_TO_BASKET'] ?>" title="Корзина">
        <span class="b-icon">
            <?= new SvgDecorator('icon-cart', 16, 16) ?>
        </span>
        <span class="b-header-info__inner">Корзина</span>
        <span class="b-header-info__number"><?= $arResult['NUM_PRODUCTS'] ?></span>
    </a>
    <div class="b-popover b-popover--cart">
        <div class="b-cart">
            <span class="b-cart__amount"><?= $arResult['NUM_PRODUCTS'] ?> <?= $arResult['PRODUCT(S)'] ?></span>
            <a class="b-link" href="<?= $arParams['PATH_TO_BASKET'] ?>" title="Редактировать">Редактировать</a>
            <a class="b-button" href="<?= $arParams['PATH_TO_ORDER'] ?>" title="Оформить заказ">Оформить заказ</a>
            <?php
            /**
             * @todo Реализовать список товаров
             */
            ?>
            <div class="b-cart-item">
                <a class="b-cart-item__name"
                   href="javascript:void(0);"
                   title="Роял Канин корм для собак крупных пород ма…">Роял Канин корм для собак
                                                                       крупных пород ма…</a>
                <span class="b-cart-item__weight">15 кг</span>
                <span class="b-cart-item__amount">(1 шт.)</span>
            </div>
        </div>
    </div>
</div>
