<?
/**
 * @var CFashionProductFooter $component
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator; ?>



<section class="fashion-category">
    <div class="fashion-category-header-mobile">
        <div class="b-container">
            <div class="fashion-category-header-mobile__content">
                <div class="fashion-category-header-mobile__info">
                    <div class="fashion-category-header-mobile__title">Категории</div>
                    <div class="fashion-category-header-mobile__count-select">Выбрано (<span data-count-select-category-fashion="true"><?=(count($arResult['ELEMENTS']) > 2 ? '3' : count($arResult['ELEMENTS']))?></span>)</div>
                </div>
                <div class="fashion-category-header-mobile__open-filter" data-open-filter-category-fashion="true">
                    <span class="b-icon b-icon--open-filter">
                        <?= new SvgDecorator('icon-open-filter', 19, 14) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="fashion-category__filter" data-content-filter-category-fashion="true">
        <div class="fashion-category-header-mobile">
            <div class="b-container">
                <div class="fashion-category-header-mobile__content">
                    <div class="fashion-category-header-mobile__info">
                        <div class="fashion-category-header-mobile__back" data-close-filter-category-fashion="true"></div>
                        <div class="fashion-category-header-mobile__title">Категории</div>
                        <div class="fashion-category-header-mobile__count-select">Выбрано (<span data-count-select-category-fashion="true"><?=(count($arResult['ELEMENTS']) > 2 ? '3' : count($arResult['ELEMENTS']))?></span>)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-container">
            <div class="fashion-category-filter">
                <? foreach($arResult['ELEMENTS'] as $i => $element) { ?>
                    <div class="fashion-category-filter__item <?=($i < 3) ? 'active' : ''?>" data-type-filter-category-fashion="<?=$i?>">
                        <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                            <div class="fashion-category-filter__img" style="background-image: url(<?=$arResult['TITLE_IMAGES'][$element['PROPERTIES']['TITLE_IMAGE']['VALUE']]?>)"></div>
                        </div>
                        <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true"><?=$element['NAME']?></div>
                    </div>
                <? } ?>
            </div>
        </div>
    </div>


    <div class="fashion-category-list">
        <? foreach($arResult['ELEMENTS'] as $i => $element) { ?>
        <div class="item-category-fashion <?=($i < 3) ? 'active' : ''?>" data-item-filter-category-fashion="<?=$i?>" data-url="/ajax/catalog/product-info/">
            <div class="b-container">
                <a href="<?=$component->getSectionUrl($element['PROPERTIES']['SECTION']['VALUE']) ?>" class="item-category-fashion__title" target="_blank"><?=$element['NAME']?></a>
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

