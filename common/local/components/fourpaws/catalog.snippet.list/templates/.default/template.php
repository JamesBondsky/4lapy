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
}

if (0 === $component->getProductCollection()->count()) {
    return;
}

if ($arResult['ECOMMERCE_VIEW_SCRIPT']) {
    echo $arResult['ECOMMERCE_VIEW_SCRIPT'];
} ?>
<div class="b-container">
    <section class="b-common-section" data-url="/ajax/catalog/product-info/">
        <div class="b-common-section__title-box b-common-section__title-box--sale">
            <h2 class="b-title b-title--sale"><?= $arParams['TITLE'] ?></h2>
            <?php
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
             */
            ?>
        </div>
        <div class="b-common-section__content b-common-section__content--sale b-common-section__content--main-sale js-popular-product">
            <?php 
            $i = 0;
            foreach ($component->getProductCollection() as $key => $product) {
                $APPLICATION->IncludeComponent(
                    'fourpaws:catalog.element.snippet',
                    'vertical',
                    [
                        'PRODUCT' => $product,
                        'OFFER_FILTER' => $arParams['OFFER_FILTER'] ?? [],
                        'COUNTER' => $i,
                    ],
                    $component,
                    [
                        'HIDE_ICONS' => 'Y',
                    ]
                );
                $i++;
            }
            ?>
        </div>
    </section>
</div>
