<?php

/** @var CCatalogSectionSlider $component */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div class="landing-category-rungo">
    <? foreach($arResult['ELEMENTS'] as $i => $element) { ?>
        <div class="item-category-landing item-category-landing--rungo active" data-item-filter-category-landing="<?=$i?>" data-url="/ajax/catalog/product-info/">
            <div class="b-container">
                <a href="<?= $component->getSectionLink($element['ID']) ?>" class="item-category-landing__title" target="_blank"><?=$element['NAME']?></a>
                <div class="item-category-landing__content">
                    <div class="item-category-landing__img" style="background-image: url(<?=$arResult['IMAGES'][$element['PROPERTIES']['IMAGE']['VALUE']]?>)">
                        <a href="<?= $component->getSectionLink($element['ID']) ?>" class="item-category-landing__more" target="_blank">
                            Посмотреть все
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
