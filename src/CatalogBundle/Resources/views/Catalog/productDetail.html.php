<?php
/**
 * @var ProductDetailRequest $productDetailRequest
 * @var CatalogLandingService $landingService
 * @var Request $request
 * @var CMain $APPLICATION
 * @var OfferCollection $internalFilters
 * @var OfferCollection $externalFilters
 * @var OfferCollection $lamps
 * @var Offer $filter
 * @var Offer $pedestal
 * @var Offer $internalFilterFirst
 * @var Offer $curOffer
 * @var Offer $lamp
 */

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\App\Templates\ViewsEnum;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Dto\ProductDetailRequest;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use FourPaws\Components\CatalogElementDetailComponent;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\HighloadHelper;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\Helpers\WordHelper;
use FourPaws\Catalog\Model\Offer;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $APPLICATION;

$logger = LoggerFactory::create('productDetail');
$offerId = $productDetailRequest->getOfferId();

/** @var Product $product */
$product = $APPLICATION->IncludeComponent(
    'fourpaws:catalog.element.detail',
    '',
    [
        'CODE' => $productDetailRequest->getProductSlug(),
        'OFFER_ID' => $offerId,
        'SET_TITLE' => 'Y',
        'SHOW_FAST_ORDER' => $productDetailRequest->getZone() !== DeliveryService::ZONE_4,
    ],
    false,
    ['HIDE_ICONS' => 'Y']
);

if (!($product instanceof Product)) {
    $logger->error('Нет итема');
    /** прерываем если вернулось непонятно что */
    return;
}

$offer = null;
CBitrixComponent::includeComponentClass('fourpaws:personal.profile');
/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
try {
    $catalogElementDetailClass = new CatalogElementDetailComponent();
    try {
        $offer = $catalogElementDetailClass->getCurrentOffer($product, $offerId);
    } catch (LoaderException | NotSupportedException | ObjectNotFoundException $e) {
        $logger->error('ошибка при получении оффера');
        /** ошибки быть не должно */
    }
} catch (SystemException | RuntimeException | ServiceNotFoundException $e) {
    $logger->error('ошибка при загрузке класса компонента');
    /** ошибки быть не должно, так как компонент отрабатывает выше */
    return;
}

if (null === $offer) {
    /** нет оффера что-то пошло не так */
    $logger->error('Нет оффера');
    return;
} ?>
    <div class="b-product-card"
         data-productid="<?= $product->getId() ?>"
         data-offerId="<?= $offer->getId() ?>"
         data-urlDelivery="/ajax/catalog/product-info/product/deliverySet/"
         itemprop="itemListElement" itemscope itemtype="http://schema.org/Product">
        <div class="b-container">
            <?php
            ob_start();
            $APPLICATION->IncludeComponent(
                'fourpaws:breadcrumbs',
                '',
                [
                    'IBLOCK_ELEMENT' => $product,
                ],
                null,
                ['HIDE_ICONS' => 'Y']
            );
            $breadcrumbs = ob_get_clean();
            echo $landingService->replaceLinksToLanding($breadcrumbs, $request);
            ?>
            <div class="b-product-card__top">
                <div class="b-product-card__title-product">
                    <?php $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_TITLE_VIEW); ?>
                    <div class="b-common-item b-common-item--card">
                        <div class="b-common-item__rank b-common-item__rank--card">
                            <?php $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_RATING_STARS_VIEW); ?>
                            <div class="b-common-item__rank-wrapper">
                                <?= MarkHelper::getDetailTopMarks($offer) ?>
                                <?php if ($offer->isShare()) {
                                    /** @var IblockElement $share */
                                    foreach ($offer->getShare() as $share) { ?>
                                        <span class="b-common-item__rank-text b-common-item__rank-text--red"><?= $share->getName() ?></span>
                                    <?php }
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-product-card__product">
                    <div class="b-product-card__permutation-weight js-weight-tablet"></div>
                    <?php $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_SLIDER_VIEW); ?>

                    <div class="b-product-card__info-product js-weight-here">
                        <?php
                        $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_OFFERS_VIEW);
                        $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_CURRENT_OFFER_INFO);
                        ?>
                    </div>
                </div>
                <?php $hasSetFirst = $APPLICATION->IncludeComponent(
                    'fourpaws:catalog.groupset',
                    '',
                    [
                        'OFFER' => $offer,
                    ],
                    null,
                    ['HIDE_ICONS' => 'Y']
                );
                if (!$hasSetFirst) {
                    $APPLICATION->IncludeComponent(
                        'fourpaws:catalog.product.bundle',
                        '',
                        [
                            'OFFER' => $offer,
                        ],
                        null,
                        ['HIDE_ICONS' => 'Y']
                    );
                } ?>
            </div>
            <?
            if ($product->getSection()->getCode() == 'banki-bez-kryshki-akvariumy' && $product->getAquariumCombination() != '') {
                $pedestal = $product->getPedestal($product->getAquariumCombination());
                if (!empty($pedestal)) {
                    //перевод милилитров в литры
                    $volumeStr = strtolower($offer->getVolumeReference()->getName());
                    if (mb_strpos($volumeStr, 'мл') || mb_strpos($volumeStr, 'л')) {
                        $volume = intval(str_replace(',', '.', preg_replace("/[^0-9]/", '', $volumeStr)));
                        if (mb_strpos($volumeStr, 'мл')) {
                            $volume = $volume / 1000;
                        }
                    } else {
                        $hideBlockAqua = true;
                    }
                    $internalFilters = $product->getInternalFilters($offerVolume);
                    if (!empty($internalFilters)) {
                        $internalFilterFirst = $internalFilters->first();
                    }

                    $externalFilters = $product->getExternalFilters($offerVolume);
                    if (!empty($externalFilters)) {
                        /** @var Offer $externalFilterFirst */
                        $externalFilterFirst = $externalFilters->first();
                    }
                    $lamps = $product->getLamps();
                    if (!empty($lamps)) {
                        $lamp = $lamps->first();
                    }

                    $totalPrice = $offer->getPrice() + $pedestal->getPrice() + $internalFilterFirst->getPrice() + $externalFilterFirst->getPrice() + $lamp->getPrice();
                } else {
                    $hideBlockAqua = true;
                }
            } else {
                $hideBlockAqua = true;
            }
            ?>
            <? if (!$hideBlockAqua && !empty($pedestal) && !$internalFilters->isEmpty() && !$externalFilters->isEmpty() && !$lamps->isEmpty()) { ?>
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
                                            <div class="js-product-item" data-productid="<?= $externalFilterFirst->getProduct()->getId(); ?>">
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
                                            <div class="js-product-item" data-productid="<?= $internalFilterFirst->getProduct()->getId(); ?>">
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
                                            <div class="js-product-item" data-productid="<?= $lamp->getProduct()->getId(); ?>">
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
                                <? foreach ($externalFilters as $key => $curOffer) { ?>
                                    <?
                                    if ($key == 0) {
                                        continue;
                                    }
                                    $curOfferImage = $curOffer->getResizeImages(240, 240)->first();
                                    $value = $curOffer->getProduct()->getPowerMax() . ' л/ч';
                                    ?>
                                    <div class="b-common-item" data-product-complect-groupid="1">
                                        <div class="js-product-item" data-productid="<?= $curOffer->getProduct()->getId(); ?>">
<!--                                            <span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;">-->
<!--                                                <img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/>-->
<!--                                            </span>-->
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
                                                            <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                                        </span>
                                                        <span class="b-common-item__price js-price-block"><?= $curOffer->getPrice(); ?></span>
                                                        <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                                    </span>
                                                </a>
                                                <? /*
                                                <div class="b-common-item__additional-information">
                                                    <div class="b-common-item__benefin js-sale-block">
                                                    <span class="b-common-item__prev-price js-sale-origin">5225 <span class="b-ruble b-ruble--prev-price">₽</span>
                                                    </span>
                                                        <span class="b-common-item__discount">
                                                        <span class="b-common-item__disc">Скидка</span>
                                                        <span class="b-common-item__discount-price js-sale-sale">784</span>
                                                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span></span>
                                                    </span>
                                                    </div>
                                                </div>
                                                */ ?>
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
                                    ?>
                                    <div class="b-common-item" data-product-complect-groupid="2">
                                        <div class="js-product-item" data-productid="<?= $curOffer->getProduct()->getId(); ?>">
<!--                                            <span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;">-->
<!--                                                <img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/>-->
<!--                                            </span>-->
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
                                                            <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                                        </span>
                                                        <span class="b-common-item__price js-price-block"><?= $curOffer->getPrice(); ?></span>
                                                        <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                                    </span>
                                                </a>
                                                <? /*
                                                <div class="b-common-item__additional-information">
                                                    <div class="b-common-item__benefin js-sale-block">
                                                    <span class="b-common-item__prev-price js-sale-origin">5225 <span class="b-ruble b-ruble--prev-price">₽</span>
                                                    </span>
                                                        <span class="b-common-item__discount">
                                                        <span class="b-common-item__disc">Скидка</span>
                                                        <span class="b-common-item__discount-price js-sale-sale">784</span>
                                                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span></span>
                                                    </span>
                                                    </div>
                                                </div>
                                                */ ?>
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
                                    ?>
                                    <div class="b-common-item" data-product-complect-groupid="3">
                                        <div class="js-product-item" data-productid="<?= $curOffer->getProduct()->getId(); ?>">
<!--                                            <span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;">-->
<!--                                                <img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/>-->
<!--                                            </span>-->
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
                                                            <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                                        </span>
                                                        <span class="b-common-item__price js-price-block"><?= $curOffer->getPrice(); ?></span>
                                                        <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                                    </span>
                                                </a>
                                                <? /*
                                                <div class="b-common-item__additional-information">
                                                    <div class="b-common-item__benefin js-sale-block">
                                                    <span class="b-common-item__prev-price js-sale-origin">5225 <span class="b-ruble b-ruble--prev-price">₽</span>
                                                    </span>
                                                        <span class="b-common-item__discount">
                                                        <span class="b-common-item__disc">Скидка</span>
                                                        <span class="b-common-item__discount-price js-sale-sale">784</span>
                                                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span></span>
                                                    </span>
                                                    </div>
                                                </div>
                                                */ ?>
                                            </div>
                                        </div>
                                    </div>
                                <? } ?>
                            </div>
                        </div>
                    </div>
                </div>
            <? } ?>

            <div class="b-product-card__tab">
                <div class="b-tab">
                    <div class="b-tab-title">
                        <ul class="b-tab-title__list">
                            <?php
                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_DESCRIPTION_TAB_HEADER);

                            if ($product->getComposition()->getText() || $product->getLayoutComposition()->getText()) { ?>
                                <li class="b-tab-title__item js-tab-item">
                                    <a class="b-tab-title__link js-tab-link"
                                       href="javascript:void(0);" title="Состав"
                                       data-tab="composition"><span
                                                class="b-tab-title__text">Состав</span></a>
                                </li>
                            <?php }

                            if ($product->getNormsOfUse()->getText() || $product->getLayoutRecommendations()->getText()) { ?>
                                <li class="b-tab-title__item js-tab-item">
                                    <a class="b-tab-title__link js-tab-link"
                                       href="javascript:void(0);" title="Рекомендации по питанию"
                                       data-tab="recommendations"><span
                                                class="b-tab-title__text">Рекомендации по питанию</span></a>
                                </li>
                            <?php }

                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_RATING_TAB_HEADER_VIEW);
                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_DELIVERY_PAYMENT_TAB_HEADER);

                            /** наличие меняется аяксом */ ?>
                            <li class="b-tab-title__item js-tab-item shops-tab disable">
                                <a class="b-tab-title__link js-tab-link"
                                   href="javascript:void(0);" title="Наличие в магазинах"
                                   data-tab="availability">
                                    <span class="b-tab-title__text">Наличие в магазинах
                                        <span class="b-tab-title__number">(0)</span>
                                    </span>
                                </a>
                            </li>
                            <?php if ($offer->isShare()) { ?>
                                <li class="b-tab-title__item js-tab-item">
                                    <a class="b-tab-title__link js-tab-link"
                                       href="javascript:void(0);" title="Акция"
                                       data-tab="shares">
                                        <span class="b-tab-title__text">Акция</span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="b-tab-content">
                        <?php
                        $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_DESCRIPTION_TAB);

                        if ($product->getComposition()->getText() || $product->getLayoutComposition()->getText()) { ?>
                            <div class="b-tab-content__container js-tab-content" data-tab-content="composition">
                                <div class="b-description-tab b-description-tab--full">
                                    <div class="b-description-tab__column b-description-tab__column--full">
                                        <div class="rc-product-detail">
                                            <? if ($product->getLayoutComposition()->getText() != '' && $product->getLayoutComposition()->getText() != null) { ?>
                                                <?= $product->getLayoutComposition()->getText() ?>
                                            <? } else { ?>
                                                <p><?= $product->getComposition()->getText() ?></p>
                                            <? } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }

                        if ($product->getNormsOfUse()->getText() || $product->getLayoutRecommendations()->getText()) { ?>
                            <div class="b-tab-content__container js-tab-content b-tab-content__container_recommendations" data-tab-content="recommendations">
                                <div class="b-description-tab b-description-tab--full">
                                    <div class="rc-product-detail">
                                        <div class="b-description-tab__column b-description-tab__column--full">
                                            <? if ($product->getLayoutRecommendations()->getText() != '' && $product->getLayoutRecommendations()->getText() != null) { ?>
                                                <?= $product->getLayoutRecommendations()->getText() ?>
                                            <? } else { ?>
                                                <p><?= $product->getNormsOfUse()->getText() ?></p>
                                            <? } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }

                        /** @noinspection PhpUnhandledExceptionInspection */
                        $APPLICATION->IncludeComponent(
                            'fourpaws:comments',
                            'catalog',
                            [
                                'HL_ID' => HighloadHelper::getIdByName('Comments'),
                                'OBJECT_ID' => $product->getId(),
                                'SORT_DESC' => 'Y',
                                'ITEMS_COUNT' => 5,
                                'ACTIVE_DATE_FORMAT' => 'd j Y',
                                'TYPE' => 'catalog',
                            ],
                            false,
                            ['HIDE_ICONS' => 'Y']
                        ); ?>
                        <?php $APPLICATION->IncludeComponent(
                            'fourpaws:city.delivery.info',
                            'catalog.detail.tab',
                            [
                                'DELIVERY_CODES' => [DeliveryService::INNER_DELIVERY_CODE],
                                'CACHE_TIME' => 3600
                            ],
                            false,
                            ['HIDE_ICONS' => 'Y']
                        ); ?>
                        <?php $APPLICATION->IncludeComponent(
                            'fourpaws:catalog.shop.available',
                            'catalog.detail.tab',
                            [
                                'PRODUCT' => $product,
                                'OFFER' => $offer,
                            ],
                            false,
                            ['HIDE_ICONS' => 'Y']
                        ); ?>
                        <?php if ($offer->isShare()) { ?>
                            <div class="b-tab-content__container js-tab-content" data-tab-content="shares">
                                <?php /** @var IblockElement $share */
                                foreach ($offer->getShare() as $share) {
                                    ?>
                                    <div class="b-title b-title--advice b-title--stock">Акция</div>
                                    <div class="b-stock">
                                        <div class="b-characteristics-tab b-characteristics-tab--stock">
                                            <ul class="b-characteristics-tab__list">
                                                <li class="b-characteristics-tab__item b-characteristics-tab__item--stock">
                                                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--stock">
                                                        <span>Название</span>
                                                        <div class="b-characteristics-tab__dots"></div>
                                                    </div>
                                                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--stock">
                                                        <a href="<?= $share->getDetailPageUrl() ?>" title="<?= $share->getName() ?>"><?= $share->getName() ?></a>
                                                    </div>
                                                </li>
                                                <li class="b-characteristics-tab__item b-characteristics-tab__item--stock">
                                                    <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--stock">
                                                        <span>Срок проведения</span>
                                                        <div class="b-characteristics-tab__dots"></div>
                                                    </div>
                                                    <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--stock">
                                                        <?php
                                                        $activeFrom = $share->getDateActiveFrom();
                                                        $activeTo = $share->getDateActiveTo();

                                                        if ($activeFrom && $activeTo) {
                                                            ?>
                                                            <?= DateHelper::replaceRuMonth($activeFrom->format('d #n# Y'),
                                                                DateHelper::GENITIVE) ?>
                                                            —
                                                            <?= DateHelper::replaceRuMonth($activeTo->format('d #n# Y'),
                                                                DateHelper::GENITIVE) ?>
                                                            <?php
                                                        } elseif ($activeFrom) {
                                                            ?>
                                                            С <?= DateHelper::replaceRuMonth($activeFrom->format('d #n# Y'),
                                                                DateHelper::GENITIVE) ?>
                                                            <?php
                                                        } elseif ($activeTo) {
                                                            ?>
                                                            По <?= DateHelper::replaceRuMonth($activeTo->format('d #n# Y'),
                                                                DateHelper::GENITIVE) ?>
                                                            <?php
                                                        } ?>
                                                    </div>
                                                </li>
                                                <?php if (!empty($share->getPreviewText()->getText())) { ?>
                                                    <li class="b-characteristics-tab__item b-characteristics-tab__item--stock">
                                                        <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--stock">
                                                            <span>Описание</span>
                                                            <div class="b-characteristics-tab__dots"></div>
                                                        </div>
                                                        <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--stock">
                                                            <?= $share->getPreviewText()->getText() ?>
                                                        </div>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                        <?php
                                        /**
                                         * @todo подарок по акции
                                         * <div class="b-stock__gift">
                                         * <div class="b-advice b-advice--stock">
                                         * <a class="b-advice__item b-advice__item--stock"
                                         * href="javascript:void(0)" title="">
                                         * <span class="b-advice__image-wrapper b-advice__image-wrapper--stock"><img
                                         * class="b-advice__image"
                                         * src="/static/build/images/content/fresh-step.png"
                                         * alt="" title="" role="presentation" /></span>
                                         * <span class="b-advice__block b-advice__block--stock">
                                         * <span class="b-advice__text b-advice__text--red">Подарок по акции</span>
                                         * <span class="b-clipped-text b-clipped-text--advice">
                                         * <span><strong>Китекат</strong> корм для кошек рыба в соусе</span>
                                         * </span>
                                         * <span class="b-advice__info b-advice__info--stock">
                                         * <span class="b-advice__weight">85 г</span>
                                         * <span class="b-advice__cost">
                                         * 13,40 <span class="b-ruble b-ruble--advice">₽</span>
                                         * </span>
                                         * </span>
                                         * </span>
                                         * </a>
                                         * </div>
                                         * <a class="b-button b-button--bordered-grey" href="javascript:void(0)" title="">
                                         * Выбрать подарок
                                         * </a>
                                         * </div>
                                         **/
                                        ?>
                                    </div>
                                    <?php $APPLICATION->IncludeComponent(
                                        'fourpaws:products.by.prop',
                                        'product.detail.stocks',
                                        [
                                            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION,
                                                IblockCode::SHARES),
                                            'ITEM_ID' => $share->getId(),
                                            'TITLE' => 'Товары по акции',
                                            'COUNT_ON_PAGE' => 20,
                                            'PROPERTY_CODE' => 'PRODUCTS',
                                            'FILTER_FIELD' => 'XML_ID',
                                            'SHOW_PAGE_NAVIGATION' => false,
                                        ],
                                        null,
                                        [
                                            'HIDE_ICONS' => 'Y',
                                        ]
                                    ); ?>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php

/**
 * Преимущества
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/local/include/blocks/advantages.php',
        'EDIT_TEMPLATE' => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'N',
    ]
);

/**
 * Похожие товары
 */
$APPLICATION->IncludeFile(
    'blocks/components/similar_products.php',
    [
        'PRODUCT_ID' => $product->getId(),
    ],
    [
        'SHOW_BORDER' => false,
        'NAME' => 'Блок похожих товаров',
        'MODE' => 'php',
    ]
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
