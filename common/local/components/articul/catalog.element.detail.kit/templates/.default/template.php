<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

if (!$arResult['HIDE_BLOCK']) {
    /** @var Product $product */
    $product = $arResult['PRODUCT'];
    /** @var Offer $offer */
    $offer = $arResult['OFFER'];
    /** @var Offer $pedestal */
    $pedestal = $arResult['PEDESTAL'];
    /** @var OfferCollection $externalFilters */
    $externalFilters = $arResult['EXTERNAL_FILTERS'];
    /** @var Offer $externalFilterFirst */
    $externalFilterFirst = $externalFilters->first();
    /** @var OfferCollection $internalFilters */
    $internalFilters = $arResult['INTERNAL_FILTERS'];
    /** @var Offer $internalFilterFirst */
    $internalFilterFirst = $internalFilters->first();
    /** @var OfferCollection $lamps */
    $lamps = $arResult['LAMPS'];
    /** @var Offer $lamp */
    $lamp = $lamps->first();
    $totalPrice = $offer->getPrice() + $pedestal->getPrice() + $internalFilterFirst->getPrice() + $externalFilterFirst->getPrice() + $lamp->getPrice();
    ?>
    <div class="b-product-card__complect">
        <div class="b-product-card-complect">
            <div class="b-product-card-complect__title">Аквариум под ключ</div>
            <div class="b-product-card-complect__row">
                <div class="b-product-card-complect__slider" data-product-complect-container="true">
                    <div class="b-product-card-complect__list js-product-complect js-advice-list">
                        <div class="b-product-card-complect__list-item slide">
                            <div class="b-common-item js-product-complect-item js-advice-item" data-offerid="<?= $offer->getId(); ?>_1" data-offerprice="<?= $offer->getPrice(); ?>" data-product-info='{"productid": <?= $product->getId(); ?>, "offerid": <?= $offer->getId(); ?>, "offerprice": <?= $offer->getPrice(); ?>}' tabindex="0">
                                <div class="b-common-item__image-wrap">
                                    <div class="b-common-item__image-link">
                                        <img class="b-common-item__image" src="<?= $offer->getResizeImages(240, 240)->first() ?>" alt="<?= $offer->getName() ?>" title="">
                                    </div>
                                </div>
                                <div class="b-common-item__info-center-block">
                                    <div class="b-common-item__description-wrap">
                                    <span class="b-clipped-text b-clipped-text--three">
                                        <span>
                                            <span class="span-strong"><?= $product->getBrandName() ?></span> <?= $offer->getName() ?>
                                        </span>
                                    </span>
                                    </div>
                                    <div class="b-common-item__info">
                                        <div class="b-common-item__property">
                                            <span class="b-common-item__property-value"><?= $offer->getVolumeReference()->getName() ?></span>
                                        </div>
                                        <div class="b-common-item__price">
                                            <span class="b-common-item__price-value"><?= $offer->getPrice(); ?></span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="b-product-card-complect__list-item slide">
                            <div class="b-common-item js-product-complect-item js-advice-item" data-offerid="<?= $pedestal->getId(); ?>_1" data-offerprice="<?= $pedestal->getPrice(); ?>" data-product-info='{"productid": <?= $pedestal->getProduct()->getId(); ?>, "offerid": <?= $pedestal->getId(); ?>, "offerprice": <?= $pedestal->getPrice(); ?>}' tabindex="0">
                                <div class="b-common-item__image-wrap">
                                    <a class="b-common-item__image-link js-item-link" href="<?= $pedestal->getDetailPageUrl(); ?>" tabindex="0">
                                        <img class="b-common-item__image" src="<?= $pedestal->getResizeImages(240, 240)->first(); ?>" alt="<?= $pedestal->getName(); ?>" title="">
                                    </a>
                                </div>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap" href="<?= $pedestal->getDetailPageUrl(); ?>" tabindex="0">
                                    <span class="b-clipped-text b-clipped-text--three">
                                        <span>
                                            <span class="span-strong"><?= $pedestal->getProduct()->getBrandName(); ?></span> <?= $pedestal->getName(); ?>
                                        </span>
                                    </span>
                                    </a>
                                    <div class="b-common-item__info">
                                        <div class="b-common-item__property">
                                            <span class="b-common-item__property-value"><?= WordHelper::showLengthNumber($pedestal->getCatalogProduct()->getLength()); ?>x<?= WordHelper::showLengthNumber($pedestal->getCatalogProduct()->getWidth()); ?>x<?= WordHelper::showLengthNumber($pedestal->getCatalogProduct()->getHeight()); ?> см</span>
                                        </div>
                                        <div class="b-common-item__price">
                                            <span class="b-common-item__price-value"><?= $pedestal->getPrice(); ?></span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="b-product-card-complect__list-item slide">
                            <div class="b-common-item js-product-complect-item js-advice-item" data-offerid="<?= $externalFilterFirst->getId(); ?>_1" data-offerprice="<?= $externalFilterFirst->getPrice(); ?>" data-product-info='{"productid": <?= $externalFilterFirst->getProduct()->getId(); ?>, "offerid": <?= $externalFilterFirst->getId(); ?>, "offerprice": <?= $externalFilterFirst->getPrice(); ?>, "groupid": 1}' data-product-group-title="Другие внешние фильтры" tabindex="0">
                                <div class="b-common-item__image-wrap">
                                    <a class="b-common-item__image-link js-item-link" href="<?= $externalFilterFirst->getDetailPageUrl(); ?>" tabindex="0">
                                        <img class="b-common-item__image" src="<?= $externalFilterFirst->getResizeImages(240, 240)->first(); ?>" alt="<?= $externalFilterFirst->getName(); ?>" title="">
                                    </a>
                                </div>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap" href="<?= $externalFilterFirst->getDetailPageUrl(); ?>" tabindex="0">
                                    <span class="b-clipped-text b-clipped-text--three">
                                        <span>
                                            <span class="span-strong"><?= $externalFilterFirst->getProduct()->getBrandName(); ?></span> <?= $externalFilterFirst->getName(); ?>
                                        </span>
                                    </span>
                                    </a>
                                    <div class="b-common-item__info">
                                        <? if ($externalFilterFirst->getProduct()->getPowerMax()) { ?>
                                            <div class="b-common-item__property">
                                                <span class="b-common-item__property-value"><?= $externalFilterFirst->getProduct()->getPowerMax(); ?> л/ч</span>
                                            </div>
                                        <? } ?>
                                        <div class="b-common-item__price">
                                            <span class="b-common-item__price-value"><?= $externalFilterFirst->getPrice(); ?></span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </div>
                                    </div>
                                    <? if ($externalFilters->count() > 1) { ?>
                                        <div class="b-common-item__replace">
                                            <a href="javascript:void(0)" class="b-common-item__replace-link js-product-complect-replace js-this-product-complect">
                                                <span class="b-common-item__replace-text js-product-complect-replace-text">Поменять</span>
                                                <span class="b-icon b-icon--replace-complect b-icon--left-3"><?= new SvgDecorator('icon-arrow-down', 10, 12) ?></span>
                                            </a>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>

                            <div class="b-product-card-complect__item-replace js-product-complect-replace-item">
                                <div class="js-product-item" data-productid="<?= $externalFilterFirst->getProduct()->getId(); ?>" style="width: auto">
                                                <span class="b-common-item__image-wrap">
                                                    <a class="b-common-item__image-link js-item-link" href="<?= $externalFilterFirst->getDetailPageUrl(); ?>">
                                                        <img class="b-common-item__image js-weight-img" src="<?= $externalFilterFirst->getResizeImages(240, 240)->first(); ?>" alt="<?= $externalFilterFirst->getName(); ?>" title="">
                                                    </a>
                                                </span>
                                    <div class="b-common-item__info-center-block">
                                        <a class="b-common-item__description-wrap js-item-link" href="<?= $externalFilterFirst->getDetailPageUrl(); ?>" title="">
                                                        <span class="b-clipped-text b-clipped-text--three">
                                                            <span>
                                                                <span class="span-strong"><?= $externalFilterFirst->getProduct()->getBrandName(); ?></span> <?= $externalFilterFirst->getName(); ?>
                                                            </span>
                                                        </span>
                                        </a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item">
                                                    <a href="javascript:void(0)"
                                                       class="b-weight-container__link js-price active-link"
                                                       data-oldprice="<?= $externalFilterFirst->getCatalogOldPrice() !== $externalFilterFirst->getCatalogPrice() ? $externalFilterFirst->getCatalogOldPrice() : '' ?>"
                                                       data-discount="<?= ($externalFilterFirst->getDiscountPrice() ?: '') ?>"
                                                       data-price="<?= $externalFilterFirst->getCatalogPrice() ?>"
                                                       data-offerid="<?= $externalFilterFirst->getId() ?>"
                                                       data-image="<?= $externalFilterFirst->getResizeImages(240, 240)->first(); ?>"
                                                       data-link="<?= $externalFilterFirst->getLink() ?>"
                                                       data-groupid="1"><?= $externalFilterFirst->getProduct()->getPowerMax(); ?> л/ч</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart js-complect-replace" href="javascript:void(0);" title="" data-offerid="<?= $externalFilterFirst->getId(); ?>">
                                                        <span class="b-common-item__wrapper-link">
                                                            <span class="b-cart">
                                                                <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                                            </span>
                                                            <span class="b-common-item__price js-price-block"><?= $externalFilterFirst->getPrice(); ?></span>
                                                            <span class="b-common-item__currency">
                                                                <span class="b-ruble">₽</span>
                                                            </span>
                                                        </span>
                                        </a>
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
                            </div>
                        </div>

                        <div class="b-product-card-complect__list-item slide">

                            <div class="b-common-item js-product-complect-item js-advice-item" data-offerid="<?= $internalFilterFirst->getId(); ?>_1" data-offerprice="<?= $internalFilterFirst->getPrice(); ?>" data-product-info='{"productid": <?= $internalFilterFirst->getProduct()->getId(); ?>, "offerid": <?= $internalFilterFirst->getId(); ?>, "offerprice": <?= $internalFilterFirst->getPrice(); ?>, "groupid": 2}' data-product-group-title="Другие внутренние фильтры" tabindex="0">
                                <div class="b-common-item__image-wrap">
                                    <a class="b-common-item__image-link js-item-link" href="<?= $internalFilterFirst->getDetailPageUrl(); ?>" tabindex="0">
                                        <img class="b-common-item__image" src="<?= $internalFilterFirst->getResizeImages(240, 240)->first(); ?>" alt="<?= $internalFilterFirst->getName(); ?>" title="">
                                    </a>
                                </div>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap" href="<?= $internalFilterFirst->getDetailPageUrl(); ?>" tabindex="0">
                                            <span class="b-clipped-text b-clipped-text--three">
                                                <span>
                                                    <span class="span-strong"><?= $internalFilterFirst->getProduct()->getBrandName(); ?></span> <?= $internalFilterFirst->getName(); ?>
                                                </span>
                                            </span>
                                    </a>
                                    <div class="b-common-item__info">
                                        <? if ($internalFilterFirst->getProduct()->getPowerMax()) { ?>
                                            <div class="b-common-item__property">
                                                <span class="b-common-item__property-value"><?= $internalFilterFirst->getProduct()->getPowerMax(); ?> л/ч</span>
                                            </div>
                                        <? } ?>
                                        <div class="b-common-item__price">
                                            <span class="b-common-item__price-value"><?= $internalFilterFirst->getPrice(); ?></span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </div>
                                    </div>
                                    <? if ($internalFilters->count() > 1) { ?>
                                        <div class="b-common-item__replace">
                                            <a href="javascript:void(0)" class="b-common-item__replace-link js-product-complect-replace js-this-product-complect">
                                                <span class="b-common-item__replace-text js-product-complect-replace-text">Поменять</span>
                                                <span class="b-icon b-icon--replace-complect b-icon--left-3"><?= new SvgDecorator('icon-arrow-down', 10, 12) ?></span>
                                            </a>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>

                            <div class="b-product-card-complect__item-replace js-product-complect-replace-item">
                                <div class="js-product-item" data-productid="<?= $internalFilterFirst->getProduct()->getId(); ?>" style="width: auto">
                                                <span class="b-common-item__image-wrap">
                                                    <a class="b-common-item__image-link js-item-link" href="<?= $internalFilterFirst->getDetailPageUrl(); ?>">
                                                        <img class="b-common-item__image js-weight-img" src="<?= $internalFilterFirst->getResizeImages(240, 240)->first(); ?>" alt="<?= $internalFilterFirst->getName(); ?>" title="">
                                                    </a>
                                                </span>
                                    <div class="b-common-item__info-center-block">
                                        <a class="b-common-item__description-wrap js-item-link" href="<?= $internalFilterFirst->getDetailPageUrl(); ?>" title="">
                                                        <span class="b-clipped-text b-clipped-text--three">
                                                            <span>
                                                                <span class="span-strong"><?= $internalFilterFirst->getProduct()->getBrandName(); ?></span> <?= $internalFilterFirst->getName(); ?>
                                                            </span>
                                                        </span>
                                        </a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item">
                                                    <a href="javascript:void(0)"
                                                       class="b-weight-container__link js-price active-link"
                                                       data-oldprice="<?= $internalFilterFirst->getCatalogOldPrice() !== $internalFilterFirst->getCatalogPrice() ? $internalFilterFirst->getCatalogOldPrice() : '' ?>"
                                                       data-discount="<?= ($internalFilterFirst->getDiscountPrice() ?: '') ?>"
                                                       data-price="<?= $internalFilterFirst->getCatalogPrice() ?>"
                                                       data-offerid="<?= $internalFilterFirst->getId() ?>"
                                                       data-image="<?= $internalFilterFirst->getResizeImages(240, 240)->first(); ?>"
                                                       data-link="<?= $internalFilterFirst->getLink() ?>"
                                                       data-groupid="2"><?= $internalFilterFirst->getProduct()->getPowerMax(); ?> л/ч</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart js-complect-replace"
                                           href="javascript:void(0);"
                                           title=""
                                           data-offerid="<?= $internalFilterFirst->getId(); ?>">
                                                <span class="b-common-item__wrapper-link">
                                                    <span class="b-cart">
                                                        <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                                    </span>
                                                    <span class="b-common-item__price js-price-block"><?= $internalFilterFirst->getPrice(); ?></span>
                                                    <span class="b-common-item__currency">
                                                        <span class="b-ruble">₽</span>
                                                    </span>
                                                </span>
                                        </a>
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
                            </div>
                        </div>

                        <div class="b-product-card-complect__list-item slide">
                            <div class="b-common-item js-product-complect-item js-advice-item" data-offerid="<?= $lamp->getId(); ?>_1" data-offerprice="<?= $lamp->getPrice(); ?>" data-product-info='{"productid": <?= $lamp->getProduct()->getId(); ?>, "offerid": <?= $lamp->getId(); ?>, "offerprice": <?= $lamp->getPrice(); ?>, "groupid": 3}' data-product-group-title="Другие лампы и светильники" tabindex="0">
                                <div class="b-common-item__image-wrap">
                                    <a class="b-common-item__image-link js-item-link" href="<?= $lamp->getDetailPageUrl(); ?>" tabindex="0">
                                        <img class="b-common-item__image" src="<?= $lamp->getResizeImages(240, 240)->first(); ?>" alt="<?= $lamp->getName(); ?>" title="">
                                    </a>
                                </div>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap" href="<?= $lamp->getDetailPageUrl(); ?>" tabindex="0">
                                                    <span class="b-clipped-text b-clipped-text--three">
                                                        <span>
                                                            <span class="span-strong"><?= $lamp->getProduct()->getBrandName(); ?></span> <?= $lamp->getName(); ?>
                                                        </span>
                                                    </span>
                                    </a>
                                    <div class="b-common-item__info">
                                        <?
                                        $weight = $lamp->getCatalogProduct()->getWeight();
                                        ?>
                                        <? if ($weight) { ?>
                                            <div class="b-common-item__property">
                                                <span class="b-common-item__property-value"><?= WordHelper::showWeight($weight, true); ?></span>
                                            </div>
                                        <? } ?>
                                        <div class="b-common-item__price">
                                            <span class="b-common-item__price-value"><?= $lamp->getPrice(); ?></span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </div>
                                    </div>
                                    <? if ($lamps->count() > 1) { ?>
                                        <div class="b-common-item__replace">
                                            <a href="javascript:void(0)" class="b-common-item__replace-link js-product-complect-replace js-this-product-complect">
                                                <span class="b-common-item__replace-text js-product-complect-replace-text">Поменять</span>
                                                <span class="b-icon b-icon--replace-complect b-icon--left-3"><?= new SvgDecorator('icon-arrow-down', 10, 12) ?></span>
                                            </a>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>
                            <div class="b-product-card-complect__item-replace js-product-complect-replace-item">
                                <div class="js-product-item" data-productid="<?= $lamp->getProduct()->getId(); ?>" style="width: auto">
                                                <span class="b-common-item__image-wrap">
                                                    <a class="b-common-item__image-link js-item-link" href="<?= $lamp->getDetailPageUrl(); ?>">
                                                        <img class="b-common-item__image js-weight-img" src="<?= $lamp->getResizeImages(240, 240)->first(); ?>" alt="<?= $lamp->getName(); ?>" title="">
                                                    </a>
                                                </span>
                                    <div class="b-common-item__info-center-block">
                                        <a class="b-common-item__description-wrap js-item-link" href="<?= $lamp->getDetailPageUrl(); ?>" title="">
                                                        <span class="b-clipped-text b-clipped-text--three">
                                                            <span>
                                                                <span class="span-strong"><?= $lamp->getProduct()->getBrandName(); ?></span> <?= $lamp->getName(); ?>
                                                            </span>
                                                        </span>
                                        </a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item">
                                                    <a href="javascript:void(0)"
                                                       class="b-weight-container__link js-price active-link"
                                                       data-oldprice="<?= $lamp->getCatalogOldPrice() !== $lamp->getCatalogPrice() ? $lamp->getCatalogOldPrice() : '' ?>"
                                                       data-discount="<?= ($lamp->getDiscountPrice() ?: '') ?>"
                                                       data-price="<?= $lamp->getCatalogPrice() ?>"
                                                       data-offerid="<?= $lamp->getId() ?>"
                                                       data-image="<?= $lamp->getResizeImages(240, 240)->first(); ?>"
                                                       data-link="<?= $lamp->getLink() ?>"
                                                       data-groupid="3"><?= WordHelper::showWeight($lamp->getCatalogProduct()->getWeight(), true) ?></a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart js-complect-replace"
                                           href="javascript:void(0);"
                                           title=""
                                           data-offerid="<?= $lamp->getId(); ?>">
                                                        <span class="b-common-item__wrapper-link">
                                                            <span class="b-cart">
                                                                <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                                            </span>
                                                            <span class="b-common-item__price js-price-block"><?= $lamp->getPrice(); ?></span>
                                                            <span class="b-common-item__currency">
                                                                <span class="b-ruble">₽</span>
                                                            </span>
                                                        </span>
                                        </a>
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
                            </div>
                        </div>

                    </div>
                </div>
                <div class="b-product-card-complect__result">
                    <div class="b-product-card-complect__summ">
                                    <span class="b-product-card-complect__price js-total-price-product-complect">
                                        <?= $totalPrice; ?>
                                    </span>
                        <span class="b-ruble b-ruble--product-information">&nbsp;₽</span>
                    </div>
                    <div class="b-product-card-complect__basket">
                        <a href="javascript:void(0)" class="b-product-card-complect__basket-link js-advice2basket-bundle" data-url="/ajax/sale/basket/bulkAddBundle/">
                            <span class="b-icon b-icon--advice"><?= new SvgDecorator('icon-cart', 20, 20) ?></span>
                            <span class="b-product-card-complect__basket-text">В корзину</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="b-product-card-complect hidden js-product-complect-other">
            <div class="b-product-card-complect__otherproducts">
                <div class="b-product-card-complect__title" data-product-complect-otherproducts-title="true"></div>
                <div class="b-common-section__content b-common-section__content--sale js-slider-product-complect-other">
                    <?
                    /** @var Offer $curOffer */
                    ?>
                    <? foreach ($externalFilters as $key => $curOffer) { ?>
                        <?
                        if ($key == 0) {
                            continue;
                        }
                        $curOfferImage = $curOffer->getResizeImages(240, 240)->first();
                        $value = $curOffer->getProduct()->getPowerMax() . ' л/ч';
                        $shares = $curOffer->getShare();
                        ?>
                        <div class="b-common-item" data-product-complect-groupid="1">
                            <div class="js-product-item" data-productid="<?= $curOffer->getProduct()->getId(); ?>" style="width: auto">
                                <? if ($shares->getTotalCount() > 0) { ?>
                                    <?= MarkHelper::getMark($curOffer, '', $shares->first()->getId()); ?>
                                <? } ?>
                                <span class="b-common-item__image-wrap">
                                                <a class="b-common-item__image-link js-item-link" href="<?= $curOffer->getDetailPageUrl(); ?>">
                                                    <img src="<?= $curOfferImage; ?>" alt="<?= $curOffer->getName(); ?>" class="b-common-item__image js-weight-img" title="">
                                                </a>
                                            </span>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap js-item-link" href="<?= $curOffer->getDetailPageUrl(); ?>">
                                                    <span class="b-clipped-text b-clipped-text--three">
                                                        <span>
                                                            <span class="span-strong"><?= $curOffer->getProduct()->getBrandName(); ?></span> <?= $curOffer->getName(); ?>
                                                        </span>
                                                    </span>
                                    </a>
                                    <div class="b-weight-container b-weight-container--list">
                                        <ul class="b-weight-container__list">
                                            <li class="b-weight-container__item">
                                                <a href="javascript:void(0)"
                                                   class="b-weight-container__link js-price active-link"
                                                   data-oldprice="<?= $curOffer->getCatalogOldPrice() !== $curOffer->getCatalogPrice() ? $curOffer->getCatalogOldPrice() : '' ?>"
                                                   data-discount="<?= ($curOffer->getDiscountPrice() ?: '') ?>"
                                                   data-price="<?= $curOffer->getCatalogPrice() ?>"
                                                   data-offerid="<?= $curOffer->getId() ?>"
                                                   data-image="<?= $curOfferImage ?>"
                                                   data-link="<?= $curOffer->getLink() ?>"
                                                   data-groupid="1"><?= $value ?></a>
                                            </li>
                                        </ul>
                                    </div>

                                    <a class="b-common-item__add-to-cart js-complect-replace" href="javascript:void(0);" data-offerid="<?= $curOffer->getId(); ?>">
                                        <span class="b-common-item__wrapper-link">
                                            <span class="b-cart">
                                                <span class="b-icon b-icon--cart">
                                                    <?php echo new SvgDecorator('icon-cart', 16, 16); ?>
                                                </span>
                                            </span>
                                            <span class="b-common-item__price js-price-block"><?= $curOffer->getCatalogPrice() ?></span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </span>
                                        <span class="b-common-item__incart">+1</span>
                                    </a>
                                    <? if ($curOffer->hasDiscount()) { ?>
                                        <div class="b-common-item__benefin js-sale-block">
                                            <span class="b-common-item__prev-price js-sale-origin">
                                                <?= $curOffer->getOldPrice() ?>
                                                <span class="b-ruble b-ruble--prev-price">₽</span>
                                            </span>
                                            <span class="b-common-item__discount">
                                                <span class="b-common-item__disc">Скидка</span>
                                                <span class="b-common-item__discount-price js-sale-sale"><?= $curOffer->getDiscountPrice() ?></span>
                                                <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span></span>
                                            </span>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>
                        </div>
                    <? } ?>
                    <? foreach ($internalFilters as $key => $curOffer) { ?>
                        <?
                        if ($key == 0) {
                            continue;
                        }
                        $curOfferImage = $curOffer->getResizeImages(240, 240)->first();
                        $value = $curOffer->getProduct()->getPowerMax() . ' л/ч';
                        $shares = $curOffer->getShare();
                        ?>
                        <div class="b-common-item" data-product-complect-groupid="2">
                            <div class="js-product-item" data-productid="<?= $curOffer->getProduct()->getId(); ?>" style="width: auto">
                                <? if ($shares->getTotalCount() > 0) { ?>
                                    <?= MarkHelper::getMark($curOffer, '', $shares->first()->getId()); ?>
                                <? } ?>
                                <span class="b-common-item__image-wrap">
                                                <a class="b-common-item__image-link js-item-link" href="<?= $curOffer->getDetailPageUrl(); ?>">
                                                    <img src="<?= $curOfferImage; ?>" alt="<?= $curOffer->getName(); ?>" class="b-common-item__image js-weight-img" title="">
                                                </a>
                                            </span>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap js-item-link" href="<?= $curOffer->getDetailPageUrl(); ?>">
                                                    <span class="b-clipped-text b-clipped-text--three">
                                                        <span>
                                                            <span class="span-strong"><?= $curOffer->getProduct()->getBrandName(); ?></span> <?= $curOffer->getName(); ?>
                                                        </span>
                                                    </span>
                                    </a>
                                    <div class="b-weight-container b-weight-container--list">
                                        <ul class="b-weight-container__list">
                                            <li class="b-weight-container__item">
                                                <a href="javascript:void(0)"
                                                   class="b-weight-container__link js-price active-link"
                                                   data-oldprice="<?= $curOffer->getCatalogOldPrice() !== $curOffer->getCatalogPrice() ? $curOffer->getCatalogOldPrice() : '' ?>"
                                                   data-discount="<?= ($curOffer->getDiscountPrice() ?: '') ?>"
                                                   data-price="<?= $curOffer->getCatalogPrice() ?>"
                                                   data-offerid="<?= $curOffer->getId() ?>"
                                                   data-image="<?= $curOfferImage ?>"
                                                   data-link="<?= $curOffer->getLink() ?>"
                                                   data-groupid="2"><?= $value ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart js-complect-replace" href="javascript:void(0);" data-offerid="<?= $curOffer->getId(); ?>">
                                        <span class="b-common-item__wrapper-link">
                                            <span class="b-cart">
                                                <span class="b-icon b-icon--cart">
                                                    <?php echo new SvgDecorator('icon-cart', 16, 16); ?>
                                                </span>
                                            </span>
                                            <span class="b-common-item__price js-price-block"><?= $curOffer->getCatalogPrice() ?></span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </span>
                                        <span class="b-common-item__incart">+1</span>
                                    </a>
                                    <? if ($curOffer->hasDiscount()) { ?>
                                        <div class="b-common-item__benefin js-sale-block">
                                            <span class="b-common-item__prev-price js-sale-origin">
                                                <?= $curOffer->getOldPrice() ?>
                                                <span class="b-ruble b-ruble--prev-price">₽</span>
                                            </span>
                                            <span class="b-common-item__discount">
                                                <span class="b-common-item__disc">Скидка</span>
                                                <span class="b-common-item__discount-price js-sale-sale"><?= $curOffer->getDiscountPrice() ?></span>
                                                <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span></span>
                                            </span>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>
                        </div>
                    <? } ?>
                    <? foreach ($lamps as $key => $curOffer) { ?>
                        <?
                        if ($key == 0) {
                            continue;
                        }
                        $curOfferImage = $curOffer->getResizeImages(240, 240)->first();
                        $weight = $curOffer->getCatalogProduct()->getWeight();
                        $shares = $curOffer->getShare();
                        ?>
                        <div class="b-common-item" data-product-complect-groupid="3">
                            <div class="js-product-item" data-productid="<?= $curOffer->getProduct()->getId(); ?>" style="width: auto">
                                <? if ($shares->getTotalCount() > 0) { ?>
                                    <?= MarkHelper::getMark($curOffer, '', $shares->first()->getId()); ?>
                                <? } ?>
                                <span class="b-common-item__image-wrap">
                                                <a class="b-common-item__image-link js-item-link" href="<?= $curOffer->getDetailPageUrl(); ?>">
                                                    <img src="<?= $curOfferImage; ?>" alt="<?= $curOffer->getName(); ?>" class="b-common-item__image js-weight-img" title="">
                                                </a>
                                            </span>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap js-item-link" href="<?= $curOffer->getDetailPageUrl(); ?>">
                                                    <span class="b-clipped-text b-clipped-text--three">
                                                        <span>
                                                            <span class="span-strong"><?= $curOffer->getProduct()->getBrandName(); ?></span> <?= $curOffer->getName(); ?>
                                                        </span>
                                                    </span>
                                    </a>
                                    <div class="b-weight-container b-weight-container--list">
                                        <ul class="b-weight-container__list">
                                            <li class="b-weight-container__item">
                                                <a href="javascript:void(0)"
                                                   class="b-weight-container__link js-price active-link"
                                                   data-oldprice="<?= $curOffer->getCatalogOldPrice() !== $curOffer->getCatalogPrice() ? $curOffer->getCatalogOldPrice() : '' ?>"
                                                   data-discount="<?= ($curOffer->getDiscountPrice() ?: '') ?>"
                                                   data-price="<?= $curOffer->getCatalogPrice() ?>"
                                                   data-offerid="<?= $curOffer->getId() ?>"
                                                   data-image="<?= $curOfferImage ?>"
                                                   data-link="<?= $curOffer->getLink() ?>"
                                                   data-groupid="3"><?= ($weight) ? WordHelper::showWeight($weight, true) : ''; ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart js-complect-replace" href="javascript:void(0);" data-offerid="<?= $curOffer->getId(); ?>">
                                        <span class="b-common-item__wrapper-link">
                                            <span class="b-cart">
                                                <span class="b-icon b-icon--cart">
                                                    <?php echo new SvgDecorator('icon-cart', 16, 16); ?>
                                                </span>
                                            </span>
                                            <span class="b-common-item__price js-price-block"><?= $curOffer->getCatalogPrice() ?></span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </span>
                                        <span class="b-common-item__incart">+1</span>
                                    </a>
                                    <? if ($curOffer->hasDiscount()) { ?>
                                        <div class="b-common-item__benefin js-sale-block">
                                            <span class="b-common-item__prev-price js-sale-origin">
                                                <?= $curOffer->getOldPrice() ?>
                                                <span class="b-ruble b-ruble--prev-price">₽</span>
                                            </span>
                                            <span class="b-common-item__discount">
                                                <span class="b-common-item__disc">Скидка</span>
                                                <span class="b-common-item__discount-price js-sale-sale"><?= $curOffer->getDiscountPrice() ?></span>
                                                <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span></span>
                                            </span>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>
                        </div>
                    <? } ?>
                </div>
            </div>
        </div>
    </div>
<? } ?>
