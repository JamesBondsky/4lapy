<?php

/** @var CCatalogSectionSlider $component */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
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

                    <?php
                    $APPLICATION->IncludeComponent(
                        'fourpaws:catalog.snippet.list',
                        'stamps',
                        [
                            'OFFER_FILTER' => [
                                '=XML_ID' => $element['PROPERTIES']['PRODUCTS']['VALUE'],
                            ],
                            'COUNT' => 500,
                            'TITLE' => 'Товары, участвующие в акции',
                            'CACHE_TIME' => 3600000,
                            'ONLY_PRODUCTS_XML_ID' => $element['PROPERTIES']['PRODUCTS']['VALUE'], // показывать только указанные ТП, а не все в родительских товарах
                        ]
                    );
                    ?>
                </div>
            </div>
        </div>
    <? } ?>
</div>
