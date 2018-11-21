<?php
/**
 * @var CatalogSaleListComponent $component
 *
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
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
<div class="b-container we_recommend__wrapper">
    <section class="b-common-section">
        <div class="b-common-section__title-box b-common-section__title-box--popular">
            <h2 class="b-title b-title--popular"><?= $arParams['TITLE'] ?></h2>
        </div>
        <div class="b-common-section__content b-common-section__content--popular js-popular-product">
            <?$i = 0;?>
            <?php foreach ($component->getProductCollection() as $product) {
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
