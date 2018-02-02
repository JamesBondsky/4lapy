<?php
/**
 * @var array           $arParams
 * @var array           $arResult
 * @var Product         $product
 * @var OfferCollection $offers
 * @var Offer           $offer
 * @var Offer           $currentOffer
 */

use FourPaws\App\Templates\MediaEnum;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$product = $arResult['PRODUCT'];
$offers  = $product->getOffers();

if (!empty($arParams['CURRENT_OFFER']) && $arParams['CURRENT_OFFER'] instanceof Offer) {
    $currentOffer = $arParams['CURRENT_OFFER'];
} else {
    /**
     * @todo hotfix. Вынести в компонент. Завязать текущий оффер на фильтр.
     */
    foreach ($offers as $offer) {
        if ($offer->getImages()->count() >= 1 && $offer->getImages()->first() !== MediaEnum::NO_IMAGE_WEB_PATH) {
            $currentOffer = $offer;
        }
    }
    
    if (!$currentOffer) {
        $currentOffer = $offers->first();
    }
} ?>

<div class="b-common-item b-common-item--catalog-item js-product-item">
    <?php if ($currentOffer->getImages()->count() > 0) { ?>
        <a class="b-common-item__image-wrap" href="<?= $product->getDetailPageUrl() ?>">
            <img class="b-common-item__image js-weight-img"
                 src="<?= $currentOffer->getResizeImages(240, 240)->first() ?>"
                 alt="<?= $currentOffer->getName() ?>"
                 title="<?= $currentOffer->getName() ?>" />
        </a>
    <?php } ?>
    <div class="b-common-item__info-center-block">
        <a class="b-common-item__description-wrap" href="<?= $product->getDetailPageUrl() ?>" title="">
            <span class="b-clipped-text b-clipped-text--three">
                <span>
                    <?php $brand = $product->getBrand(); ?>
                    <?php if (!empty($brand)) { ?>
                        <strong><?= $product->getBrand()->getName() ?>  </strong><?php } ?>
    
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
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );
        } ?>
        <div class="b-common-item__rank-wrapper">
            &nbsp
            <?php /**
             * @todo new; shares
             * <span class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span>
             * <span class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
             */ ?>
        </div>
        <?php if ($offers->count() > 1) { ?>
            <?php
            $mainCombinationType = '';
            if ($currentOffer->getClothingSize()) {
                $mainCombinationType = 'SIZE';
            } else {
                $mainCombinationType = 'VOLUME';
            } ?>
            <?php if ($mainCombinationType === 'SIZE') {
                ?>
                <div class="b-common-item__variant">Размеры</div>
                <?php
            } else {
                ?>
                <div class="b-common-item__variant">Варианты фасовки</div>
                <?php
            } ?>
            <div class="b-weight-container b-weight-container--list">
                <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                   href="javascript:void(0);"
                   title=""></a>
                <ul class="b-weight-container__list">
                    <?php
                    $i = 0;
                    foreach ($offers as $offer) {
                        $i++;
                        $value = null;
                        if ($mainCombinationType === 'SIZE') {
                            if ($offer->getClothingSize()) {
                                $value = $offer->getClothingSize()->getName();
                            }
                        } else {
                            if ($offer->getVolumeReference()) {
                                $value = $offer->getVolumeReference()->getName();
                            } elseif ($weight = $offer->getCatalogProduct()->getWeight()) {
                                $value = WordHelper::showWeight($weight);
                            }
                        }
                        if (!$value) {
                            continue;
                        } ?>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)"
                               class="b-weight-container__link js-price<?= $currentOffer->getId() === $offer->getId(
                               ) ? ' active-link' : '' ?><?= $i >= 4 ? ' mobile-hidden' : '' ?>"
                               data-price="<?= $offer->getPrice() ?>" data-offerid="<?= $offer->getId() ?>"
                               data-image="<?= $offer->getResizeImages(240, 240)->first() ?>"
                            ><?= $value ?></a>
                        </li>
                        <?php
                    } ?>
                </ul>
                <div class="b-weight-container__dropdown-list__wrapper<?= $offers->count() > 3 ? ' _active' : '' ?>">
                    <?if($offers->count() > 3){?>
                        <p class="js-show-weight">Еще <?= $offers->count() - 3 ?></p>
                    <?}?>
                    <div class="b-weight-container__dropdown-list"></div>
                </div>
            </div>
            <?php
        } ?>
        <div class="b-common-item__moreinfo">
            <?php if ($currentOffer->getMultiplicity() > 1) { ?>
                <div class="b-common-item__packing">
                    Упаковка <strong><?= $currentOffer->getMultiplicity() ?>шт.</strong>
                </div>
                <?php
            } ?>
            <?php if ($product->getCountry()) {
                ?>
                <div class="b-common-item__country">
                    Страна производства <strong><?= $product->getCountry()->getName() ?></strong>
                </div>
            <?php } ?>
            <?php if ($currentOffer->isByRequest()) { ?>
                <div class="b-common-item__order">
                    Только под заказ
                </div>
                <?php
            } ?>
            <?php /* @todo инфо о доставке/самовывозе */ ?>
            <div class="b-common-item__pickup">
                Самовывоз
            </div>
        </div>
        <?php $offerId = $currentOffer->getId();
        if ($offerId > 0) {
            ?>
            <a class="b-common-item__add-to-cart js-basket-add"
               href="javascript:void(0);"
               title=""
               data-url="/ajax/sale/basket/add/"
               data-offerid="<?= $offerId ?>">
        <span class="b-common-item__wrapper-link">
            <span class="b-cart">
                <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 12, 12) ?></span>
            </span>
            <span class="b-common-item__price js-price-block"><?= $currentOffer->getPrice() ?></span>
            <span class="b-common-item__currency">
                <span class="b-ruble">₽</span>
            </span>
        </span>
            </a>
        <?php } else {
            ?>
            <a class="b-common-item__add-to-cart" href="javascript:void(0);"
               title="">
                <span class="b-common-item__wrapper-link">
                    <span class="b-cart">
                        <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 12, 12) ?></span>
                    </span>
                    <span class="b-common-item__price js-price-block"><?= $currentOffer->getPrice() ?></span>
                    <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                    </span>
                </span>
            </a>
        <?php }
        //
        // Информация об особенностях покупки товара
        //
        ob_start();
        
        /** @todo инфо о скидке */
        
        if ($currentOffer->isByRequest()) { ?>
            <div class="b-common-item__info-wrap">
                <span class="b-common-item__text">Только под заказ</span>
            </div>
        <?php }
        
        /** @todo инфо о доставке/самовывозе */
        $addInfo = ob_get_clean();
        if (!empty($addInfo)) {
            echo '<div class="b-common-item__additional-information">' . $addInfo . '</div>';
        }
        ?>
    </div>
</div>