<?php
/**
 * @var CatalogSaleListComponent $component
 *
 * @var CMain                    $APPLICATION
 * @var array                    $arParams
 * @var array                    $arResult
 */


use FourPaws\Components\CatalogSaleListComponent;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-container">
    <section class="b-common-section">
        <div class="b-common-section__title-box b-common-section__title-box--sale">
            <h2 class="b-title b-title--sale">Распродажа</h2>
            <?
            /**
             * Ссылка "Показать все" потребуется в случае добавления отдельной страницы с распродажей
             *
             * <a class="b-link b-link--title b-link--title" href="javascript:void(0)"
             * title="Показать все"><span class="b-link__text b-link__text--title">Показать все</span><span
             * class="b-link__mobile b-link__mobile--title">Все</span><span class="b-icon">
             * <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
             * <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-right" href="icons.svg#icon-arrow-right">
             * </use>
             * </svg></span></a>
             */ ?>
        </div>
        <div class="b-common-section__content b-common-section__content--sale js-popular-product">
            <?php foreach ($component->getProductCollection() as $product) {
                /**
                 * @todo исключать по фильтру офферы, не относящиеся к распродаже
                 */
                $APPLICATION->IncludeComponent(
                    'fourpaws:catalog.element.snippet',
                    'vertical',
                    [
                        'PRODUCT' => $product,
                    ],
                    $component,
                    [
                        'HIDE_ICONS' => 'Y',
                    ]
                );
            }
            ?>
        </div>
    </section>
</div>
