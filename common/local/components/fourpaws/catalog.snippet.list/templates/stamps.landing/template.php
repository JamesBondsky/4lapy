<?php
/**
 * @var CatalogSaleListComponent $component
 *
 * @var CMain                    $APPLICATION
 * @var array                    $arParams
 * @var array                    $arResult
 */


use FourPaws\Catalog\Model\Offer;
use FourPaws\Components\CatalogSaleListComponent;
use FourPaws\PersonalBundle\Service\StampService;

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
            <h2 class="toys-landing__check-header"><?= $arParams['TITLE'] ?></h2>
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
            <?if(!empty($arParams['ALL_LINK'])):?>
                <a class="b-link b-link--title b-link--title" href="<?=$arParams['ALL_LINK'];?>"  title="Показать все">
                    <span class="b-link__text b-link__text--title">Показать все</span>
                    <span class="b-link__mobile b-link__mobile--title">Все</span><span class="b-icon">
                        <svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
                            <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-right" href="icons.svg#icon-arrow-right"></use>
                        </svg>
                    </span>
                </a>
            <?endif;?>
        </div>
        <div class="b-common-section__content b-common-section__content--sale b-common-section__content--main-sale js-popular-product">
            <?php 
            $i = 0;
            $onlyProductsXmlIds = $arParams['ONLY_PRODUCTS_XML_ID'] ?? false;
            foreach ($component->getProductCollection() as $key => $product) {
                $show = !\in_array($product->getXmlId(), StampService::FIRST_PRODUCT_XML_ID);

                foreach ($product->getOffers() as $offer) {
                    if (\in_array($offer->getXmlId(), StampService::FIRST_PRODUCT_XML_ID)) {
                        $show = false;
                    }
                }

                if ($show) { //todo в карусели на лендинге не должно быть товаров с лендинга
                    if ($onlyProductsXmlIds) {
                        $product->setOffers(
                            $product->getOffers()->filter(static function (Offer $item) use ($onlyProductsXmlIds) {
                                return in_array($item->getXmlId(), $onlyProductsXmlIds, false);
                            })
                        );
                    }

                    $APPLICATION->IncludeComponent(
                        'fourpaws:catalog.element.snippet',
                        'stamps',
                        [
                            'PRODUCT' => $product,
                            'OFFER_FILTER' => $arParams['OFFER_FILTER'] ?? [],
                            'COUNTER' => $i,
                            'CACHE_TIME' => 0
                        ],
                        $component,
                        [
                            'HIDE_ICONS' => 'Y',
                        ]
                    );
                    $i++;
                }
            }
            ?>
        </div>
    </section>
</div>
