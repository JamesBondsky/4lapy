<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;

?>

<div class="fashion-category-rungo">
    <? foreach($arResult['ELEMENTS'] as $i => $element) { ?>
        <div class="item-category-fashion item-category-fashion--rungo active" data-item-filter-category-fashion="<?=$i?>" data-url="/ajax/catalog/product-info/">
            <div class="b-container">
                <a href="<?= $component->getSectionLink($element['ID']) ?>" class="item-category-fashion__title" target="_blank"><?=$element['NAME']?></a>
                <div class="item-category-fashion__content">
                    <div class="item-category-fashion__img" style="background-image: url(<?=$arResult['IMAGES'][$element['PROPERTIES']['IMAGE']['VALUE']]?>)">
                        <a href="<?= $component->getSectionLink($element['ID']) ?>" class="item-category-fashion__more" target="_blank">
                            В категорию
                        </a>
                    </div>

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
