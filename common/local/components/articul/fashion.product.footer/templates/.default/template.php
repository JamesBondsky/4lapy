<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

?>
<section class="fashion-category">
    <div class="b-container">
        <div class="fashion-category-filter">
            <? foreach($arResult['ELEMENTS'] as $i => $element) { ?>
            <div class="fashion-category-filter__item <?=($i < 4) ? 'active' : ''?>" data-type-filter-category-fashion="<?=$i?>">
                <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                    <div class="fashion-category-filter__img" style="background-image: url(<?=$arResult['TITLE_IMAGES'][$element['PROPERTIES']['TITLE_IMAGE']['VALUE']]?>)"></div>
                </div>
                <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true"><?=$element['NAME']?></div>
            </div>
            <? } ?>
        </div>
    </div>

    <div class="fashion-category-list">
        <? foreach($arResult['ELEMENTS'] as $i => $element) { ?>
        <div class="item-category-fashion <?=($i < 4) ? 'active' : ''?>" data-item-filter-category-fashion="<?=$i?>" data-url="/ajax/catalog/product-info/">
            <div class="b-container">
                <div class="item-category-fashion__title"><?=$element['NAME']?></div>
                <div class="item-category-fashion__content">
                    <div class="item-category-fashion__img" style="background-image: url(<?=$arResult['IMAGES'][$element['PROPERTIES']['IMAGE']['VALUE']]?>)"></div>
                    <div class="item-category-fashion__slider" data-slider-category-fashion="true">
                        <?php
                        foreach ($element['PROPERTIES']['PRODUCTS']['VALUE'] as $xmlId){
                            $product = $component->getProduct($xmlId);
                            $APPLICATION->IncludeComponent(
                                'fourpaws:catalog.element.snippet',
                                'fashion_slider',
                                [
                                    'PRODUCT'               => $product,
                                    'GOOGLE_ECOMMERCE_TYPE' => sprintf('Модная коллекция - %s', $element['NAME'])
                                ]
                            );
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <? } ?>
    </div>
</section>

