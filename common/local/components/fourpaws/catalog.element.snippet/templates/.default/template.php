<?php
/**
 * @var array $arParams
 * @var array $arResult
 *
 * @var CatalogElementSnippet $component
 *
 * @var Product $product
 * @var OfferCollection $offers
 * @var Offer $offer
 * @var Offer $currentOffer
 *
 * @global \CMain $APPLICATION
 */

use Bitrix\Main\Web\Uri;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Components\CatalogElementSnippet;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$product = $arResult['PRODUCT'];
$offers = $product->getOffers();
/** @var Offer $currentOffer */
$currentOffer = $arResult['CURRENT_OFFER']; ?>

<div class="b-common-item <?= $arParams['NOT_CATALOG_ITEM_CLASS'] !== 'Y' ? ' b-common-item--catalog-item' : '' ?> js-product-item"
     data-productid="<?= $product->getId() ?>">
    <?= $component->getMarkService()->getMark($currentOffer) ?>
    <?php if ($currentOffer->getImages()->count() > 0) { ?>
        <span class="b-common-item__image-wrap">
            <a class="b-common-item__image-link js-item-link" href="<?= $product->getDetailPageUrl() ?>">
                <img class="b-common-item__image js-weight-img"
                     src="<?= $currentOffer->getResizeImages(240, 240)->first() ?>"
                     alt="<?= $currentOffer->getName() ?>"
                     title="<?= $currentOffer->getName() ?>"/>
            </a>
        </span>
    <?php } ?>
    <div class="b-common-item__info-center-block">
        <a class="b-common-item__description-wrap js-item-link" href="<?= $product->getDetailPageUrl() ?>" title="">
            <span class="b-clipped-text b-clipped-text--three">
                <span>
                    <?php if ($product->getBrand()) { ?>
                        <strong><?= $product->getBrand()->getName() ?></strong>
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
                    'HL_ID' => HighloadHelper::getIdByName('Comments'),
                    'OBJECT_ID' => $productId,
                    'SORT_DESC' => 'Y',
                    'ITEMS_COUNT' => 5,
                    'ACTIVE_DATE_FORMAT' => 'd j Y',
                    'TYPE' => 'catalog',
                    'ITEM_LINK' => (new Uri($product->getDetailPageUrl()))->addParams(['new-review' => 'y'])->getUri(),
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
        <?php if ($offers->count() > 0 && $product->isFood()) {

            $mainCombinationType = '';
            if ($currentOffer->getClothingSize()) {
                $mainCombinationType = 'SIZE';
            } else {
                $mainCombinationType = 'VOLUME';
            }

            if ($mainCombinationType === 'SIZE') {
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
                                if($weight > 0) {
                                    $value = WordHelper::showWeight($weight);
                                }
                            }
                        }

                        if (!empty($value)) { ?>
                            <li class="b-weight-container__item">
                                <a href="javascript:void(0)"
                                   class="b-weight-container__link js-price<?= $currentOffer->getId() === $offer->getId() ? ' active-link' : '' ?><?= $i >= 4 ? ' mobile-hidden' : '' ?>"
                                   data-price="<?= ceil($offer->getPrice()) ?>" data-offerid="<?= $offer->getId() ?>"
                                   data-image="<?= $offer->getResizeImages(240, 240)->first() ?>"
                                   data-link="<?= $offer->getLink() ?>"><?= $value ?></a>
                            </li>
                        <?php } else { ?>
                            <li class="b-weight-container__item" style="display: none">
                                <a href="javascript:void(0)"
                                   class="b-weight-container__link js-price active-link"
                                   data-price="<?= ceil($offer->getPrice()) ?>" data-offerid="<?= $offer->getId() ?>"
                                   data-image="<?= $offer->getResizeImages(240, 240)->first() ?>"
                                   data-link="<?= $offer->getLink() ?>"></a>
                            </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
                <div class="b-weight-container__dropdown-list__wrapper<?= $offers->count() > 3 ? ' _active' : '' ?>">
                    <?php if ($offers->count() > 3) { ?>
                        <p class="js-show-weight">Еще <?= $offers->count() - 3 ?></p>
                    <?php } ?>
                    <div class="b-weight-container__dropdown-list"></div>
                </div>
            </div>
            <?php
        } else { ?>
            <div class="b-weight-container b-weight-container--list">
                <ul class="b-weight-container__list">
                    <li class="b-weight-container__item" style="display: none">
                        <a href="javascript:void(0)"
                           class="b-weight-container__link js-price active-link"
                           data-price="<?= ceil($currentOffer->getPrice()) ?>" data-offerid="<?= $currentOffer->getId() ?>"
                           data-image="<?= $currentOffer->getResizeImages(240, 240)->first() ?>"
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
        if ($offerId > 0) { ?>
            <a class="b-common-item__add-to-cart js-basket-add"
               href="javascript:void(0);"
               title=""
               data-url="/ajax/sale/basket/add/"
               data-offerid="<?= $offerId ?>">
                <span class="b-common-item__wrapper-link">
                    <span class="b-cart">
                        <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 12, 12) ?></span>
                    </span>
                    <span class="b-common-item__price js-price-block"><?= ceil($currentOffer->getPrice()) ?></span>
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
                    <span class="b-common-item__price js-price-block"><?= ceil($currentOffer->getPrice()) ?></span>
                    <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                    </span>
                </span>
                <span class="b-common-item__incart">+1</span>
            </a>
        <?php }
        //
        // Информация об особенностях покупки товара
        //
        ?>
        <div class="b-common-item__additional-information">
            <div class="b-common-item__benefin js-sale-block">
                <span class="b-common-item__prev-price js-sale-origin">
                    <span class="b-ruble b-ruble--prev-price"></span>
                </span>
                <span class="b-common-item__discount">
                    <span class="b-common-item__disc"></span>
                    <span class="b-common-item__discount-price js-sale-sale"></span>
                    <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount"></span>
                    </span>
                </span>
            </div>
        </div>
    </div>
</div>
