<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var Product $product
 * @var Offer $offer
 * @var Offer $additionalItem
 * @var Offer $currentOffer
 * @var OfferCollection $offerGroup
 */

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

if (!$arResult['HIDE_KIT_BLOCK']) {
    $arMessages = [
        'filters' => [
            'another' => 'Другие фильтры',
            'all' => 'Все фильтры'
        ],
        'decor' => [
            'another' => 'Другие декорации',
            'all' => 'Все декорации'
        ],
        'lamps' => [
            'another' => 'Другие лампы и светильники',
            'all' => 'Все лампы и светильники'
        ]
    ];
    $product = $arResult['PRODUCT'];
    $offer = $arResult['OFFER'];
    $additionalItem = $arResult['ADDITIONAL_ITEM'];
    $selectionOffers = $arResult['SELECTION_OFFERS'];
    /**
     * @var ArrayCollection $filters
     */
    $filters = $selectionOffers['filters'];
    /**
     * @var ArrayCollection $decor
     */
    $decor = $selectionOffers['decor'];
    /**
     * @var ArrayCollection $lamps
     */
    $lamps = $selectionOffers['lamps'];

    $totalPrice = $offer->getCatalogPrice() +
        (($additionalItem) ? $additionalItem->getCatalogPrice() : 0) +
        ((!$filters->isEmpty()) ? $filters->first()->getCatalogPrice() : 0) +
        ((!$decor->isEmpty()) ? $decor->first()->getCatalogPrice() : 0) +
        ((!$lamps->isEmpty()) ? $lamps->first()->getCatalogPrice() : 0);
    ?>
    <div class="b-product-card__complect">
        <div class="b-product-card-complect">
            <div class="b-product-card-complect__title">Аквариум под ключ</div>
            <div class="b-product-card-complect__row">
                <div class="b-product-card-complect__slider" data-product-complect-container="true">
                    <div class="b-product-card-complect__list js-product-complect js-advice-list">
                        <div class="b-product-card-complect__list-item js-list-item-card-complect slide">
                            <div class="b-common-item js-product-complect-item js-advice-item" data-offerid="<?= $offer->getId(); ?>_1" data-offerprice="<?= $offer->getCatalogPrice(); ?>" data-product-info='{"productid": <?= $product->getId(); ?>, "offerid": <?= $offer->getId(); ?>, "offerprice": <?= $offer->getCatalogPrice(); ?>}' tabindex="0">
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
                                            <span class="b-common-item__price-value"><?= $offer->getCatalogPrice(); ?></span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <? if (!empty($additionalItem)) { ?>
                            <div class="b-product-card-complect__list-item js-list-item-card-complect slide">
                                <div class="b-common-item js-product-complect-item js-advice-item" data-offerid="<?= $additionalItem->getId(); ?>_1" data-offerprice="<?= $additionalItem->getCatalogPrice(); ?>" data-product-info='{"productid": <?= $additionalItem->getProduct()->getId(); ?>, "offerid": <?= $additionalItem->getId(); ?>, "offerprice": <?= $additionalItem->getCatalogPrice(); ?>}' tabindex="0">
                                    <div class="b-common-item__delete-item-complect js-delete-item-complect"></div>
                                    <div class="b-common-item__image-wrap">
                                        <a class="b-common-item__image-link js-item-link" href="<?= $additionalItem->getDetailPageUrl(); ?>" tabindex="0">
                                            <img class="b-common-item__image" src="<?= $additionalItem->getResizeImages(240, 240)->first(); ?>" alt="<?= $additionalItem->getName(); ?>" title="">
                                        </a>
                                    </div>
                                    <div class="b-common-item__info-center-block">
                                        <a class="b-common-item__description-wrap" href="<?= $additionalItem->getDetailPageUrl(); ?>" tabindex="0">
                                    <span class="b-clipped-text b-clipped-text--three">
                                        <span>
                                            <span class="span-strong"><?= $additionalItem->getProduct()->getBrandName(); ?></span> <?= $additionalItem->getName(); ?>
                                        </span>
                                    </span>
                                        </a>
                                        <div class="b-common-item__info">
                                            <div class="b-common-item__property">
                                                <span class="b-common-item__property-value"><?= WordHelper::showLengthNumber($additionalItem->getCatalogProduct()->getLength()); ?>x<?= WordHelper::showLengthNumber($additionalItem->getCatalogProduct()->getWidth()); ?>x<?= WordHelper::showLengthNumber($additionalItem->getCatalogProduct()->getHeight()); ?> см</span>
                                            </div>
                                            <div class="b-common-item__price">
                                                <span class="b-common-item__price-value"><?= $additionalItem->getCatalogPrice(); ?></span>
                                                <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <? } ?>
                        <? foreach ($selectionOffers as $groupKey => $offerGroup) { ?>
                            <?
                            if (!$offerGroup->isEmpty()) {
                                $currentOffer = $offerGroup->first();

                                switch ($groupKey) {
                                    case 'filters':
                                        $propVal = $currentOffer->getProduct()->getPowerMax();
                                        $propUnit = ' л/ч';
                                        break;
                                    case 'lamps':
                                    case 'decor':
                                        $propVal = WordHelper::showWeightNumber($currentOffer->getCatalogProduct()->getWeight(), true);
                                        $propUnit = ' кг';
                                        break;
                                }
                                ?>
                                <div class="b-product-card-complect__list-item js-list-item-card-complect slide">
                                    <div class="b-common-item js-product-complect-item js-advice-item"
                                         data-offerid="<?= $currentOffer->getId(); ?>_1"
                                         data-offerprice="<?= $currentOffer->getCatalogPrice(); ?>"
                                         data-product-info='{"productid": <?= $currentOffer->getProduct()->getId(); ?>, "offerid": <?= $currentOffer->getId(); ?>, "offerprice": <?= $currentOffer->getCatalogPrice(); ?>, "groupid": "<?= $groupKey ?>"}'
                                         data-product-group-title="<?= $arMessages[$groupKey]['another']; ?>" tabindex="0">
                                        <div class="b-common-item__delete-item-complect js-delete-item-complect"></div>
                                        <div class="b-common-item__image-wrap">
                                            <a class="b-common-item__image-link js-item-link" href="<?= $currentOffer->getDetailPageUrl(); ?>" tabindex="0">
                                                <img class="b-common-item__image" src="<?= $currentOffer->getResizeImages(240, 240)->first(); ?>" alt="<?= $currentOffer->getName(); ?>" title="">
                                            </a>
                                        </div>
                                        <div class="b-common-item__info-center-block">
                                            <a class="b-common-item__description-wrap" href="<?= $currentOffer->getDetailPageUrl(); ?>" tabindex="0">
                                            <span class="b-clipped-text b-clipped-text--three">
                                                <span>
                                                    <span class="span-strong"><?= $currentOffer->getProduct()->getBrandName(); ?></span> <?= $currentOffer->getName(); ?>
                                                </span>
                                            </span>
                                            </a>
                                            <div class="b-common-item__info">
                                                <? if ($propVal) { ?>
                                                    <div class="b-common-item__property">
                                                        <span class="b-common-item__property-value"><?= $propVal . $propUnit ?></span>
                                                    </div>
                                                <? } ?>
                                                <div class="b-common-item__price">
                                                    <span class="b-common-item__price-value"><?= $currentOffer->getCatalogPrice(); ?></span>
                                                    <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                                </div>
                                            </div>
                                            <? if ($offerGroup->count() > 1) { ?>
                                                <div class="b-common-item__replace">
                                                    <a href="javascript:void(0)" class="b-common-item__replace-link js-product-complect-replace js-this-product-complect<?= ($groupKey == 'filters') ? ' hide-all-link' : '' ?>">
                                                    <span class="b-common-item__replace-text js-product-complect-replace-text"
                                                          data-link-text="<?= $arMessages[$groupKey]['all']; ?>"
                                                          data-link-href="<?= $currentOffer->getProduct()->getSection()->getSectionPageUrl(); ?>"
                                                    >Поменять</span>
                                                        <span class="b-icon b-icon--replace-complect b-icon--left-3"><?= new SvgDecorator('icon-arrow-down', 10, 12) ?></span>
                                                    </a>
                                                </div>
                                            <? } ?>
                                        </div>
                                    </div>
                                    <div class="b-product-card-complect__item-replace js-product-complect-replace-item">
                                        <div class="js-product-item" data-productid="<?= $currentOffer->getProduct()->getId(); ?>">
                                        <span class="b-common-item__image-wrap">
                                            <a class="b-common-item__image-link js-item-link" href="<?= $currentOffer->getDetailPageUrl(); ?>">
                                                <img class="b-common-item__image js-weight-img" src="<?= $currentOffer->getResizeImages(240, 240)->first(); ?>" alt="<?= $currentOffer->getName(); ?>" title="">
                                            </a>
                                        </span>
                                            <div class="b-common-item__info-center-block">
                                                <a class="b-common-item__description-wrap js-item-link" href="<?= $currentOffer->getDetailPageUrl(); ?>" title="">
                                                <span class="b-clipped-text b-clipped-text--three">
                                                    <span>
                                                        <span class="span-strong"><?= $currentOffer->getProduct()->getBrandName(); ?></span> <?= $currentOffer->getName(); ?>
                                                    </span>
                                                </span>
                                                </a>
                                                <div class="b-weight-container b-weight-container--list">
                                                    <ul class="b-weight-container__list">
                                                        <li class="b-weight-container__item">
                                                            <a href="javascript:void(0)"
                                                               class="b-weight-container__link js-price active-link"
                                                               data-oldprice="<?= $currentOffer->getCatalogOldPrice() !== $currentOffer->getCatalogPrice() ? $currentOffer->getCatalogOldPrice() : '' ?>"
                                                               data-discount="<?= ($currentOffer->getDiscountPrice() ?: '') ?>"
                                                               data-price="<?= $currentOffer->getCatalogPrice() ?>"
                                                               data-offerid="<?= $currentOffer->getId() ?>"
                                                               data-image="<?= $currentOffer->getResizeImages(240, 240)->first(); ?>"
                                                               data-link="<?= $currentOffer->getLink() ?>"
                                                               data-groupid="1"><?= $propVal . $propUnit ?></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <a class="b-common-item__add-to-cart js-complect-replace" href="javascript:void(0);" title="" data-offerid="<?= $currentOffer->getId(); ?>">
                                                <span class="b-common-item__wrapper-link">
                                                    <span class="b-cart">
                                                        <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                                    </span>
                                                    <span class="b-common-item__price js-price-block"><?= $currentOffer->getCatalogPrice(); ?></span>
                                                    <span class="b-common-item__currency">
                                                        <span class="b-ruble">₽</span>
                                                    </span>
                                                </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <? } ?>
                        <? } ?>
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
        <? if (!($filters->isEmpty() && $lamps->isEmpty() && $decor->isEmpty())) { ?>
            <div class="b-product-card-complect hidden js-product-complect-other">
                <div class="b-product-card-complect__otherproducts">
                    <div class="b-product-card-complect__title" data-product-complect-otherproducts-title="true"></div>
                    <div class="b-common-section__content b-common-section__content--complect-other js-slider-product-complect-other">
                        <?
                        /** @var Offer $currentOffer */
                        ?>
                        <?
                        foreach ($selectionOffers as $groupKey => $offerGroup) {
                            foreach ($offerGroup as $key => $currentOffer) {
                                if ($key == 0) {
                                    continue;
                                }
                                $currentOfferImage = $currentOffer->getResizeImages(240, 240)->first();
                                switch ($groupKey) {
                                    case 'filters':
                                        $propVal = $currentOffer->getProduct()->getPowerMax();
                                        $propUnit = ' л/ч';
                                        break;
                                    case 'lamps':
                                    case 'decor':
                                        $propVal = WordHelper::showWeightNumber($currentOffer->getCatalogProduct()->getWeight(), true);
                                        $propUnit = ' кг';
                                        break;
                                }
                                $shares = $currentOffer->getShare();
                                ?>
                                <div class="b-common-item" data-product-complect-groupid="<?= $groupKey ?>">
                                    <div class="js-product-item" data-productid="<?= $currentOffer->getProduct()->getId(); ?>">
                                        <? if ($shares->getTotalCount() > 0) { ?>
                                            <?= MarkHelper::getMark($currentOffer, '', $shares->first()->getId()); ?>
                                        <? } ?>
                                        <span class="b-common-item__image-wrap">
                                        <a class="b-common-item__image-link js-item-link" href="<?= $currentOffer->getDetailPageUrl(); ?>">
                                            <img src="<?= $currentOfferImage; ?>" alt="<?= $currentOffer->getName(); ?>" class="b-common-item__image js-weight-img" title="">
                                        </a>
                                    </span>
                                        <div class="b-common-item__info-center-block">
                                            <a class="b-common-item__description-wrap js-item-link" href="<?= $currentOffer->getDetailPageUrl(); ?>">
                                            <span class="b-clipped-text b-clipped-text--three">
                                                <span>
                                                    <span class="span-strong"><?= $currentOffer->getProduct()->getBrandName(); ?></span> <?= $currentOffer->getName(); ?>
                                                </span>
                                            </span>
                                            </a>
                                            <div class="b-weight-container b-weight-container--list">
                                                <ul class="b-weight-container__list">
                                                    <li class="b-weight-container__item">
                                                        <a href="javascript:void(0)"
                                                           class="b-weight-container__link js-price active-link"
                                                           data-oldprice="<?= $currentOffer->getCatalogOldPrice() !== $currentOffer->getCatalogPrice() ? $currentOffer->getCatalogOldPrice() : '' ?>"
                                                           data-discount="<?= ($currentOffer->getDiscountPrice() ?: '') ?>"
                                                           data-price="<?= $currentOffer->getCatalogPrice() ?>"
                                                           data-offerid="<?= $currentOffer->getId() ?>"
                                                           data-image="<?= $currentOfferImage ?>"
                                                           data-link="<?= $currentOffer->getLink() ?>"
                                                           data-groupid="1"><?= ($propVal) ? $propVal . $propUnit : '' ?></a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <a class="b-common-item__add-to-cart js-complect-replace" href="javascript:void(0);" data-offerid="<?= $currentOffer->getId(); ?>">
                                            <span class="b-common-item__wrapper-link">
                                                <span class="b-cart">
                                                    <span class="b-icon b-icon--cart">
                                                        <?php echo new SvgDecorator('icon-cart', 16, 16); ?>
                                                    </span>
                                                </span>
                                                <span class="b-common-item__price js-price-block"><?= $currentOffer->getCatalogPrice() ?></span>
                                                <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                            </span>
                                                <span class="b-common-item__incart">+1</span>
                                            </a>
                                            <? if ($currentOffer->hasDiscount()) { ?>
                                                <div class="b-common-item__benefin js-sale-block">
                                                <span class="b-common-item__prev-price js-sale-origin">
                                                    <?= $currentOffer->getOldPrice() ?>
                                                    <span class="b-ruble b-ruble--prev-price">₽</span>
                                                </span>
                                                    <span class="b-common-item__discount">
                                                    <span class="b-common-item__disc">Скидка</span>
                                                    <span class="b-common-item__discount-price js-sale-sale"><?= $currentOffer->getDiscountPrice() ?></span>
                                                    <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span></span>
                                                </span>
                                                </div>
                                            <? } ?>
                                        </div>
                                    </div>
                                </div>
                                <?
                            }
                        }
                        ?>
                    </div>
                    <div class="b-product-card-complect__link-wrap">
                        <a href="#" class="b-product-card-complect__link" id="all-items-aquariums">Ссылка</a>
                    </div>
                </div>
            </div>
        <? } ?>
    </div>
<? } ?>
