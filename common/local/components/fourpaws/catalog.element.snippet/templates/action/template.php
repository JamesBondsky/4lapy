<?php

use Bitrix\Main\Web\Uri;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\Components\CatalogElementSnippet;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\EcommerceBundle\Service\RetailRocketService;
use FourPaws\Helpers\HighloadHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var                       $ecommerceService    GoogleEcommerceService
 * @var                       $retailRocketService RetailRocketService
 *
 * @var array                 $arParams
 * @var array                 $arResult
 *
 * @var CatalogElementSnippet $component
 *
 * @var Product               $product
 * @var OfferCollection       $offers
 * @var Offer                 $offer
 * @var Offer                 $currentOffer
 *
 * @global CMain              $APPLICATION
 */
$ecommerceService = $component->getEcommerceService();
$retailRocketService = $component->getRetailRocketService();
$getOnClick = function (Offer $offer) use ($ecommerceService, $arParams) {
    return \str_replace(
        '"', '\'', $ecommerceService->renderScript($ecommerceService->buildClickFromOffer($offer, $arParams['GOOGLE_ECOMMERCE_TYPE']))
    );
};
$getOnMouseDown = function (Offer $offer) use ($retailRocketService) {
    return \str_replace(
        '"', '\'', $retailRocketService->renderAddToBasket($offer->getXmlId())
    );
};

/** @var Product $product */
$product = $arResult['PRODUCT'];
$product->setOffers($product->getOffers(true, $arParams['OFFER_FILTER'] ?? []));
$offers = $product->getOffersSorted();
/** @var Offer $currentOffer */
$currentOffer = $arResult['CURRENT_OFFER'];
$offerWithImages = $currentOffer;
if (!$currentOffer->getImagesIds()) {
    /** @var Offer $offer */
    foreach ($offers as $offer) {
        if (!$offer->getImagesIds()) {
            continue;
        }
        $offerWithImages = $offer;
    }
}

?>
<div class="b-common-item <?= $arParams['NOT_CATALOG_ITEM_CLASS'] !== 'Y' ? ' b-common-item--catalog-item' : '' ?> js-product-item" data-productid="<?= $product->getId() ?>">
    <?= MarkHelper::getMark($currentOffer, '', $arParams['SHARE_ID']) ?>
    <span class="b-common-item__image-wrap">
        <a class="b-common-item__image-link js-item-link" href="<?= $currentOffer->getLink() ?>"
           onclick="<?= $getOnClick($currentOffer) ?>">
            <img class="b-common-item__image js-weight-img"
                 src="<?= $offerWithImages->getResizeImages(240, 240)->first() ?>"
                 alt="<?= $product->getName() ?>"
                 title="<?= $product->getName() ?>"/>
        </a>
    </span>
    <div class="b-common-item__info-center-block">
        <a class="b-common-item__description-wrap js-item-link" href="<?= $currentOffer->getLink() ?>"
           onclick="<?= $getOnClick($currentOffer) ?>" title="">
            <span class="b-clipped-text b-clipped-text--three">
                <span>
                    <?php if ($product->getBrand()) { ?>
                        <span class="span-strong"><?= $product->getBrand()->getName() ?></span>
                    <?php } ?>
                    <?= $product->getName() ?>
                </span>
            </span>
        </a>
        <?php
        $productId = $product->getId();
        if ($productId > 0) {
            $APPLICATION->IncludeComponent(
                'fourpaws:comments',
                'catalog.snippet',
                [
                    'HL_ID'              => HighloadHelper::getIdByName('Comments'),
                    'OBJECT_ID'          => $productId,
                    'SORT_DESC'          => 'Y',
                    'ITEMS_COUNT'        => 5,
                    'ACTIVE_DATE_FORMAT' => 'd j Y',
                    'TYPE'               => 'catalog',
                    'ITEM_LINK'          => (new Uri($currentOffer->getLink()))->addParams(['new-review' => 'y'])
                        ->getUri(),
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );
        } ?>
        <div class="b-common-item__rank-wrapper">
            &nbsp
            <?php
            if ($currentOffer->isNew()) {
                ?>
                <span class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span>
            <?php }
            if ($currentOffer->isShare()) {
                /** @var IblockElement $share */
                $share = $currentOffer->getShare()->first(); ?>
                <span class="b-common-item__rank-text b-common-item__rank-text--red"><?= $share->getName() ?></span>
            <?php } ?>
        </div>
        <?php
        if ($offers->count() > 0) {

            /** @noinspection PhpUnhandledExceptionInspection */
            if ($currentOffer->getPackageLabelType() === Offer::PACKAGE_LABEL_TYPE_SIZE) {
                ?>
                <div class="b-common-item__variant">Размеры</div>
                <?php
            } else {
                ?>
                <div class="b-common-item__variant">Варианты фасовки</div>
                <?php
            }
            ?>
            <div class="b-weight-container b-weight-container--list">
                <?php
                /** @noinspection PhpUnhandledExceptionInspection */
                $value = $currentOffer->getPackageLabel(true, 999);
                ?>
                <a class="b-weight-container__link <?= ($offers->count() > 1) ? ' b-weight-container__link--mobile ' : '' ?> js-mobile-select js-select-mobile-package"
                   href="javascript:void(0);"
                   title=""><?= $value ?></a>
                <div class="b-weight-container__dropdown-list__wrapper">
                    <div class="b-weight-container__dropdown-list"></div>
                </div>
                <ul class="b-weight-container__list">
                    <?php
                    foreach ($offers as $offer) {
                        if ($currentOffer->getId() === $offer->getId()) {
                            /** @noinspection PhpUnhandledExceptionInspection */
                            $value = $offer->getPackageLabel(true, 999);
                            $offerImage = $offer->getImagesIds() ? $offer->getResizeImages(240, 240)->first() : $offerWithImages->getResizeImages(240, 240)->first();
                            ?>
                            <li class="b-weight-container__item">
                                <a href="javascript:void(0)"
                                   class="b-weight-container__link js-price<?= $currentOffer->getId()
                                   === $offer->getId() ? ' active-link' : '' ?>"
                                   data-oldprice="<?= $offer->getCatalogOldPrice()
                                   !== $offer->getCatalogPrice() ? $offer->getCatalogOldPrice() : '' ?>"
                                   data-mark-src="<?= ($offer->getMarkOffer() ?: '') ?>"
                                   data-discount="<?= ($offer->getDiscountPrice() ?: '') ?>"
                                   data-price="<?= $offer->getCatalogPrice() ?>"
                                   data-subscribePrice="<?= \round($offer->getSubscribePrice()) ?>"
                                   data-offerid="<?= $offer->getId() ?>"
                                   data-onclick="<?= $getOnClick($offer) ?>"
                                   data-onmousedown="<?= $getOnMouseDown($offer) ?>"
                                   data-image="<?= $offerImage ?>"
                                   data-link="<?= $offer->getLink() ?>"><?= $value ?></a>
                            </li>
                            <?php
                        }
                    } ?>
                </ul>
            </div>
            <?php
        } else { ?>
            <div class="b-weight-container b-weight-container--list">
                <ul class="b-weight-container__list">
                    <li class="b-weight-container__item">
                        <a href="javascript:void(0)"
                           class="b-weight-container__link js-price active-link"
                           data-oldprice="<?= $currentOffer->getOldPrice()
                           !== $currentOffer->getCatalogPrice() ? $currentOffer->getOldPrice() : '' ?>"
                           data-mark-src="<?= ($currentOffer->getMarkOffer() ?: '') ?>"
                           data-price="<?= $currentOffer->getCatalogPrice() ?>"
                           data-subscribePrice="<?= \round($offer->getSubscribePrice()) ?>"
                           data-discount="<?= ($currentOffer->getDiscountPrice() ?: '') ?>"
                           data-offerid="<?= $currentOffer->getId() ?>"
                           data-image="<?= $currentOffer->getResizeImages(240, 240)->first() ?>"
                           data-onclick="<?= $getOnClick($currentOffer) ?>"
                           data-onmousedown="<?= $getOnMouseDown($currentOffer) ?>"
                           data-link="<?= $currentOffer->getLink() ?>"></a>
                    </li>
                </ul>
            </div>
        <?php } ?>
        <div class="b-common-item__moreinfo">
            <?php if ($currentOffer->getMultiplicity() > 1) { ?>
                <div class="b-common-item__packing">
                    Упаковка <strong><?= $currentOffer->getMultiplicity() ?>шт.</strong>
                </div>
                <?php
            }

            if ($product->getCountry()) {
                ?>
                <div class="b-common-item__country">
                    Страна производства <strong><?= $product->getCountry()->getName() ?></strong>
                </div>
            <?php } ?>
        </div>
        <?php $offerId = $currentOffer->getId();
        if ($arParams['IS_POPUP']) { ?>
            <a class="b-common-item__add-to-cart b-common-item__add-to-cart--subscribe js-basket-add"
               href="javascript:void(0);"
               title=""
               data-offerid="<?= $offerId ?>">
                <span class="b-common-item__wrapper-link">
                    <span class="b-common-item__price js-price-block"><?= $currentOffer->getCatalogPrice() ?></span>
                    <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                    </span>
                    <span class="b-common-item__subscribe">В подписку</span>
                </span>
                <span class="b-common-item__incart">+1</span>
            </a>
        <? } else {
            if ($offerId > 0) { ?>
                <a class="b-common-item__add-to-cart js-basket-add"
                   href="javascript:void(0);"
                   onmousedown="<?= $getOnMouseDown($currentOffer) ?>"
                   title=""
                   data-url="/ajax/sale/basket/add/"
                   data-offerid="<?= $offerId ?>">
                <span class="b-common-item__wrapper-link">
                    <span class="b-cart">
                        <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 12, 12) ?></span>
                    </span>
                    <span class="b-common-item__price js-price-block"><?= $currentOffer->getCatalogPrice() ?></span>
                    <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                    </span>
                </span>
                    <span class="b-common-item__incart">+1</span>
                </a>
            <?php } else { ?>
                <a class="b-common-item__add-to-cart" href="javascript:void(0);" title="">
                <span class="b-common-item__wrapper-link">
                    <span class="b-cart">
                        <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 12, 12) ?></span>
                    </span>
                    <span class="b-common-item__price js-price-block"><?= $currentOffer->getCatalogPrice() ?></span>
                    <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                    </span>
                </span>
                    <span class="b-common-item__incart">+1</span>
                </a>
            <?php }
        }
        //
        // Информация об особенностях покупки товара
        //
        ?>
        <div class="b-common-item__additional-information">
            <? if ($currentOffer->getSubscribePrice() < $currentOffer->getPrice()): ?>
                <a class="b-common-item__price-subscribe" href="<?= $currentOffer->getLink() ?>">
                    <span class="logo-subscr"><?= new SvgDecorator('icon-logo-subscription', 20, 18) ?></span>
                    <span class="b-common-item__price js-price-subscribe-block"><?= \round($currentOffer->getSubscribePrice()) ?></span>
                    <span class="b-ruble">₽</span>
                    <span class="title-subscr">Подписка</span>
                </a>
            <? elseif($currentOffer->hasDiscount()): ?>
                <div class="b-common-item__benefin js-sale-block">
                    <span class="b-common-item__prev-price js-sale-origin">
                        <?= $currentOffer->getOldPrice() ?>
                        <span class="b-ruble b-ruble--prev-price">₽</span>
                    </span>
                    <span class="b-common-item__discount">
                        <span class="b-common-item__disc">Скидка</span>
                        <span class="b-common-item__discount-price js-sale-sale"><?= $currentOffer->getDiscountPrice() ?></span>
                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span>
                        </span>
                    </span>
                </div>
            <? else: ?>
                <div class="b-common-item__benefin js-sale-block">
                    <span class="b-common-item__prev-price js-sale-origin">
                        <span class="b-ruble b-ruble--prev-price"></span>
                    </span>
                    <span class="b-common-item__discount">
                        <span class="b-common-item__disc"></span>
                        <span class="b-common-item__discount-price js-sale-sale"></span>
                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span>
                        </span>
                    </span>
                </div>
            <? endif; ?>
        </div>
    </div>
</div>
