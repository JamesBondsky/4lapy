<?php
/**
 * @var array           $arParams
 * @var array           $arResult
 * @var Product         $product
 * @var OfferCollection $offers
 * @var Offer           $offer
 * @var Offer           $firstOffer
 */

use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\HighloadHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$product    = $arResult['PRODUCT'];
$offers     = $product->getOffers();
$firstOffer = $offers->first();

?>

<div class="b-common-item b-common-item--catalog-item js-product-item">
    <!--    <span class="b-common-item__sticker-wrap" style="background-color:;data-background:;"-->
    <!--    ><img class="b-common-item__sticker" src="images/inhtml/s-15proc.svg" alt="" role="presentation"/></span>-->
    <a class="b-common-item__image-wrap" href="<?= $product->getDetailPageUrl() ?>">
        <img class="b-common-item__image js-weight-img"
             src="<?= $firstOffer->getResizeImages(240, 240)->first() ?>"
             alt="<?= $firstOffer->getName() ?>"
             title="<?= $firstOffer->getName() ?>" />
    </a>
    <div class="b-common-item__info-center-block">
        <a class="b-common-item__description-wrap" href="<?= $product->getDetailPageUrl() ?>" title="">
            <span class="b-clipped-text b-clipped-text--three"
            ><span><strong><?= $product->getBrand()->getName() ?>  </strong><?= $product->getName() ?></span></span>
        </a>
        <?php /** @noinspection PhpUnhandledExceptionInspection */
        $APPLICATION->IncludeComponent(
            'fourpaws:comments',
            'catalog.snippet',
            [
                'HL_ID'              => HighloadHelper::getIdByName('Comments'),
                'OBJECT_ID'          => $product->getId(),
                'SORT_DESC'          => 'Y',
                'ITEMS_COUNT'        => 5,
                'ACTIVE_DATE_FORMAT' => 'd j Y',
                'TYPE'               => 'catalog',
            ],
            $component,
            ['HIDE_ICONS' => 'Y']
        );?>
        <div class="b-common-item__rank-wrapper">
            <span class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span>
            <span class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
        </div>
        <?php if ($offers->count() > 1) { ?>
            <?php
            $mainCombinationType = '';
            if ($firstOffer->getClothingSize()) {
                $mainCombinationType = 'SIZE';
            } elseif ($firstOffer->getVolumeReference()) {
                $mainCombinationType = 'VOLUME';
            }
            ?>
            <?php if ($mainCombinationType === 'SIZE') { ?>
                <div class="b-common-item__variant">Размеры</div>
            <?php } else { ?>
                <div class="b-common-item__variant">Варианты фасовки</div>
            <?php } ?>
            <div class="b-weight-container b-weight-container--list">
                <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                   href="javascript:void(0);"
                   title=""></a>
                <ul class="b-weight-container__list">
                    <?php
                    foreach ($offers as $offer) {
                        if ($mainCombinationType === 'SIZE') {
                            $value = $offer->getClothingSize();
                        } else {
                            $value = $offer->getVolumeReference();
                        }
                        if (!$value) {
                            continue;
                        }
                        ?>
                        <li class="b-weight-container__item">
                                <span class="b-weight-container__link js-price <?= $firstOffer->getId() === $offer->getId(
                                ) ? 'active-link' : '' ?>"
                                      data-price="<?= $offer->getPrice() ?>" data-offerid="<?= $offer->getId() ?>"
                                      data-image="<?= $offer->getResizeImages(240, 240)->first() ?>"
                                ><?= $value->getName() ?></span>
                        </li>
                        <?php
                    } ?>
                </ul>
            </div>
        <?php } ?>
        <div class="b-common-item__moreinfo">
            <?php if ($firstOffer->getMultiplicity() > 1) { ?>
                <div class="b-common-item__packing">
                    Упаковка <strong><?= $firstOffer->getMultiplicity() ?>шт.</strong>
                </div>
            <?php } ?>
            <?php if ($product->getCountry()) { ?>
                <div class="b-common-item__country">
                    Страна производства <strong><?= $product->getCountry()->getName() ?></strong>
                </div>
            <?php } ?>
            <?php if ($firstOffer->isByRequest()) { ?>
                <div class="b-common-item__order">
                    Только под заказ
                </div>
            <?php } ?>
            <?php /* @todo инфо о доставке/самовывозе */ ?>
            <div class="b-common-item__pickup">
                Самовывоз
            </div>
        </div>
    </div>
    <a class="b-common-item__add-to-cart js-basket-add"
       href="javascript:void(0);"
       title=""
       data-url="/ajax/sale/basket/add/"
       data-offerid="<?= $firstOffer->getId() ?>">
            <span class="b-common-item__wrapper-link"
            ><span class="b-cart"
                ><span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 12, 12) ?></span></span><span
                        class="b-common-item__price js-price-block"><?= $firstOffer->getPrice() ?></span><span
                        class="b-common-item__currency"> <span class="b-ruble">₽</span></span></span></a>
    <!--        <div class="b-common-item__additional-information">-->
    <!--            <div class="b-common-item__benefin">-->
    <!--                <span class="b-common-item__prev-price">-->
    <?php //= $firstOffer->getPrice()?><!-- <span-->
    <!--                            class="b-ruble b-ruble--prev-price">₽</span></span>-->
    <!--                <span class="b-common-item__discount"><span class="b-common-item__disc">Скидка</span><span-->
    <!--                            class="b-common-item__discount-price">200</span><span-->
    <!--                            class="b-common-item__currency"> <span-->
    <!--                                class="b-ruble b-ruble--discount">₽</span></span></span>-->
    <!--            </div>-->
    <!--        </div>-->
</div>
