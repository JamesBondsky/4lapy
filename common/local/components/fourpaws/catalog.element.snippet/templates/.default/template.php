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

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$product = $arResult['PRODUCT'];
$offers = $product->getOffers();
$firstOffer = $offers->first();

?>

<div class="b-common-item b-common-item--catalog-item js-product-item">
    <!--    <span class="b-common-item__sticker-wrap" style="background-color:;data-background:;"-->
    <!--    ><img class="b-common-item__sticker" src="images/inhtml/s-15proc.svg" alt="" role="presentation"/></span>-->
    <a class="b-common-item__image-wrap" href="<?= $product->getDetailPageUrl() ?>">
        <img
                class="b-common-item__image js-weight-img"
                src="<?= $firstOffer->getResizeImages(240, 240)->first() ?>"
                alt="<?= $firstOffer->getName() ?>"
                title="<?= $firstOffer->getName() ?>"
        />
    </a>
    <div class="b-common-item__info-center-block">
        <a class="b-common-item__description-wrap" href="<?= $product->getDetailPageUrl() ?>" title="">
            <span class="b-clipped-text b-clipped-text--three"
            ><span><strong><?= $product->getBrand()->getName() ?>  </strong><?= $product->getName() ?></span></span>
        </a>
        <div class="b-common-item__rank">
            <div class="b-rating">
                <div class="b-rating__star-block">
                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                </div>
                <div class="b-rating__star-block">
                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                </div>
                <div class="b-rating__star-block">
                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                </div>
                <div class="b-rating__star-block">
                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                </div>
                <div class="b-rating__star-block">
                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                </div>
            </div>
            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
            <div class="b-common-item__rank-wrapper">
                <span class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span>
                <span class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
            </div>
        </div>
        <?php
        if ($offers->count() > 1) {
            ?>
            <div class="b-common-item__variant">
                Варианты фасовки
            </div>
            <div class="b-weight-container b-weight-container--list">
                <a
                        class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                        href="javascript:void(0);"
                        title=""
                ></a>

                <ul class="b-weight-container__list">
                    <?php
                    foreach ($offers as $offer) {
                        ?>
                        <li class="b-weight-container__item">
                            <span
                                    class="b-weight-container__link js-price <?= $firstOffer->getId() === $offer->getId() ? 'active-link' : '' ?>"
                                    data-price="<?= $offer->getPrice() ?>"
                                    data-image="<?= $offer->getResizeImages(240, 240)->first() ?>"
                            >4 кг</span>
                        </li>
                        <?php
                    } ?>
                </ul>
            </div>
            <?php
        }
        ?>
        <div class="b-common-item__moreinfo">
            <div class="b-common-item__packing">
                Упаковка <strong>8шт.</strong>
            </div>
            <div class="b-common-item__country">
                Страна производства <strong>Нидерланды</strong>
            </div>
            <div class="b-common-item__order">
                Только под заказ
            </div>
            <div class="b-common-item__pickup">
                Самовызов
            </div>
        </div>
        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title="">
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
</div>