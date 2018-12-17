<?php
/**
 * @var ProductDetailRequest  $productDetailRequest
 * @var CatalogLandingService $landingService
 * @var Request               $request
 * @var CMain                 $APPLICATION
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
        'CODE'            => $productDetailRequest->getProductSlug(),
        'OFFER_ID'        => $offerId,
        'SET_TITLE'       => 'Y',
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
     itemprop="itemListElement" itemscope itemtype="http://schema.org/Product" >
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
                            <?=MarkHelper::getDetailTopMarks($offer) ?>
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
        /**
         * @var Offer $pedestal
         */
        $pedestal = $product->getPedestal($product->getAssociationAquariums());

        $volumeStr = strtolower($offer->getVolumeReference()->getName());
        /**
         * Перевод миллилитров в литры (если нужно)
         */
        if (mb_strpos($volumeStr, 'мл') || mb_strpos($volumeStr, 'л')) {
            $volume = intval(str_replace(',', '.', preg_replace("/[^0-9]/", '', $volumeStr)));
            if (mb_strpos($volumeStr, 'мл')) {
                $volume = $volume / 1000;
            }
        } else {
            $hideBlockAqua = true;
        }
        /**
         * @var OfferCollection $internalFilters
         */
        $internalFilters = $product->getInternalFilters($offerVolume);
        if (!empty($internalFilters)) {
            /** @var Offer $internalFilterFirst */
            $internalFilterFirst = $internalFilters->first();
        }

        /**
         * @var OfferCollection $externalFilters
         */
        $externalFilters = $product->getExternalFilters($offerVolume);
        if (!empty($externalFilters)) {
            /** @var Offer $externalFilterFirst */
            $externalFilterFirst = $externalFilters->first();
        }
        /**
         * @var OfferCollection $lamps
         */
        $lamps = $product->getLamps();
        if (!empty($lamps)) {
            /** @var Offer $lamp */
            $lamp = $lamps->first();
        }
        ?>
        <? if (!empty($pedestal) && !$internalFilters->isEmpty() && !$externalFilters->isEmpty()&& !$lamps->isEmpty() && !$hideBlockAqua) { ?>
            <div class="b-product-card__complect">
            <div class="b-product-card-complect">
                <div class="b-product-card-complect__title">Аквариум под ключ</div>
                <div class="b-product-card-complect__row">
                    <div class="b-product-card-complect__slider" data-product-complect-container="true">
                        <div class="b-product-card-complect__list js-product-complect">
                            <div class="b-product-card-complect__list-item slide">
                                <div class="b-common-item">
                                    <div class="b-common-item__image-wrap">
                                        <div class="b-common-item__image-link">
                                            <img class="b-common-item__image" src="<?= $offer->getResizeImages(240,240)->first()?>" alt="<?= $offer->getName()?>" title="">
                                        </div>
                                    </div>
                                    <div class="b-common-item__info-center-block">
                                        <div class="b-common-item__description-wrap">
                                            <span class="b-clipped-text b-clipped-text--three">
                                                <span>
                                                    <span class="span-strong"><?= $product->getBrandName() ?></span> <?= $offer->getName()?>
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
                                <div class="b-common-item js-product-complect-item" data-product-info='{"productid": <?= $pedestal->getProduct()->getXmlId(); ?>, "offerid": <?= $pedestal->getXmlId(); ?>, "offerprice": <?= $pedestal->getPrice(); ?>}' tabindex="0">
                                    <div class="b-common-item__image-wrap">
                                        <a class="b-common-item__image-link js-item-link" href="<?= $pedestal->getDetailPageUrl(); ?>" tabindex="0">
                                            <img class="b-common-item__image" src="<?= $pedestal->getResizeImages(240,240)->first(); ?>" alt="<?= $pedestal->getName(); ?>" title="">
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

                                <div class="b-common-item js-product-complect-item" data-product-info='{"productid": <?= $externalFilterFirst->getProduct()->getXmlId(); ?>, "offerid": <?= $externalFilterFirst->getXmlId(); ?>, "offerprice": <?= $externalFilterFirst->getPrice(); ?>, "groupid": 1}' data-product-group-title="Другие внешние фильтры" tabindex="0">
                                    <div class="b-common-item__image-wrap">
                                        <a class="b-common-item__image-link js-item-link" href="<?= $externalFilterFirst->getDetailPageUrl(); ?>" tabindex="0">
                                            <img class="b-common-item__image" src="<?= $externalFilterFirst->getResizeImages(240,240)->first(); ?>" alt="<?= $externalFilterFirst->getName(); ?>" title="">
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
                                        <div class="b-common-item__replace">
                                            <a href="javascript:void(0)" class="b-common-item__replace-link js-product-complect-replace js-this-product-complect">
                                                <span class="b-common-item__replace-text js-product-complect-replace-text">Поменять</span>
                                                <span class="b-icon b-icon--replace-complect b-icon--left-3"><?= new SvgDecorator('icon-arrow-down', 10, 12) ?></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="b-product-card-complect__item-replace js-product-complect-replace-item">
                                    <div class="js-product-item" data-productid="33016">
                                        <span class="b-common-item__image-wrap">
                                            <a class="b-common-item__image-link js-item-link" href="<?= $externalFilterFirst->getDetailPageUrl(); ?>">
                                                <img class="b-common-item__image js-weight-img" src="<?= $externalFilterFirst->getResizeImages(240,240)->first(); ?>" alt="<?= $externalFilterFirst->getName(); ?>" title="">
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
                                            <div class="b-common-item__info">
                                                <? if ($externalFilterFirst->getProduct()->getPowerMax()) { ?>
                                                    <div class="b-common-item__property">
                                                        <span class="b-common-item__property-value"><?= $externalFilterFirst->getProduct()->getPowerMax(); ?> л/ч</span>
                                                    </div>
                                                <? } ?>
                                            </div>
                                            <?
                                            /*
                                            <div class="b-weight-container b-weight-container--list">
                                                <a class="b-weight-container__link  js-mobile-select js-select-mobile-package"
                                                   href="javascript:void(0);"
                                                   title="">7 г</a>
                                                <div class="b-weight-container__dropdown-list__wrapper">
                                                    <div class="b-weight-container__dropdown-list"></div>
                                                </div>
                                                <ul class="b-weight-container__list">
                                                    <li class="b-weight-container__item">
                                                        <a href="javascript:void(0)"
                                                           class="b-weight-container__link js-price active-link"
                                                           data-oldprice=""
                                                           data-discount=""
                                                           data-price="519"
                                                           data-offerid="33017"
                                                           data-image="/resize/240x240/upload/iblock/5a1/5a1841d8dc62e9102c6c41b0a8101b62.jpg"
                                                           data-link="/catalog/ryby/oborudowanie/vnutrennie-filtry-ryby/Amma_Filtr_vnutrenniy__250lch__1004795.html?offer=33017">7 г</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            */
                                            ?>
                                            <a class="b-common-item__add-to-cart js-complect-replace"
                                               href="javascript:void(0);"
                                               title=""
                                               data-offerid="<?= $externalFilterFirst->getXmlId(); ?>">
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
                                <div class="b-common-item js-product-complect-item" data-product-info='{"productid": 33079, "offerid": 33080, "offerprice": 3065, "groupid": 2}' data-product-group-title="Другие внутренние фильтры" tabindex="0">
                                    <div class="b-common-item__image-wrap">
                                        <a class="b-common-item__image-link js-item-link" href="/catalog/ryby/oborudowanie/pompy-ryby/Pompa_dlya_akvariuma_YUvel_Rekord_1000_Rio_125180_Ekoflou_600lch_1005324.html?offer=33080" tabindex="0">
                                            <img class="b-common-item__image" src="/resize/240x240/upload/iblock/4f0/4f09b0205a49de782ee73afecddae18d.jpg" alt="Помпа для аквариума Рекорд 1000 Рио 125/180 Экофлоу 600л/ч" title="">
                                        </a>
                                    </div>
                                    <div class="b-common-item__info-center-block">
                                        <a class="b-common-item__description-wrap" href="/catalog/ryby/oborudowanie/pompy-ryby/Pompa_dlya_akvariuma_YUvel_Rekord_1000_Rio_125180_Ekoflou_600lch_1005324.html?offer=33080" tabindex="0">
                                            <span class="b-clipped-text b-clipped-text--three">
                                                <span>
                                                    <span class="span-strong">Juwel</span> Помпа для аквариума Рекорд 1000 Рио 125/180 Экофлоу 600л/ч
                                                </span>
                                            </span>
                                        </a>
                                        <div class="b-common-item__info">
                                            <div class="b-common-item__property">
                                                <span class="b-common-item__property-value">250 л/ч</span>
                                            </div>
                                            <div class="b-common-item__price">
                                                <span class="b-common-item__price-value">3 065</span>
                                                <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                            </div>
                                        </div>
                                        <div class="b-common-item__replace">
                                            <a href="javascript:void(0)" class="b-common-item__replace-link js-product-complect-replace js-this-product-complect">
                                                <span class="b-common-item__replace-text js-product-complect-replace-text">Поменять</span>
                                                <span class="b-icon b-icon--replace-complect b-icon--left-3"><?= new SvgDecorator('icon-arrow-down', 10, 12) ?></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="b-product-card-complect__item-replace js-product-complect-replace-item">
                                    <div class="js-product-item" data-productid="33079">
                                        <span class="b-common-item__image-wrap">
                                            <a class="b-common-item__image-link js-item-link" href="/catalog/ryby/oborudowanie/pompy-ryby/Pompa_dlya_akvariuma_YUvel_Rekord_1000_Rio_125180_Ekoflou_600lch_1005324.html?offer=33080" >
                                                <img class="b-common-item__image js-weight-img"
                                                     src="/resize/240x240/upload/iblock/4f0/4f09b0205a49de782ee73afecddae18d.jpg"
                                                     alt="Помпа для аквариума Рекорд 1000 Рио 125/180 Экофлоу 600л/ч"
                                                     title="Помпа для аквариума Рекорд 1000 Рио 125/180 Экофлоу 600л/ч"/>
                                            </a>
                                        </span>
                                        <div class="b-common-item__info-center-block">
                                            <a class="b-common-item__description-wrap js-item-link" href="/catalog/ryby/oborudowanie/pompy-ryby/Pompa_dlya_akvariuma_YUvel_Rekord_1000_Rio_125180_Ekoflou_600lch_1005324.html?offer=33080" title="">
                                                <span class="b-clipped-text b-clipped-text--three">
                                                    <span>
                                                        <span class="span-strong">Juwel</span> Помпа для аквариума Рекорд 1000 Рио 125/180 Экофлоу 600л/ч</span>
                                                </span>
                                            </a>
                                            <div class="b-common-item__info">
                                                <div class="b-common-item__property">
                                                    <span class="b-common-item__property-value">250 л/ч</span>
                                                </div>
                                            </div>
                                            <div class="b-weight-container b-weight-container--list">
                                                <a class="b-weight-container__link  js-mobile-select js-select-mobile-package"
                                                   href="javascript:void(0);"
                                                   title="">398 г</a>
                                                <div class="b-weight-container__dropdown-list__wrapper">
                                                    <div class="b-weight-container__dropdown-list"></div>
                                                </div>
                                                <ul class="b-weight-container__list">
                                                    <li class="b-weight-container__item">
                                                        <a href="javascript:void(0)"
                                                           class="b-weight-container__link js-price active-link"
                                                           data-oldprice=""
                                                           data-discount=""
                                                           data-price="3065"
                                                           data-offerid="33080"
                                                           data-image="/resize/240x240/upload/iblock/4f0/4f09b0205a49de782ee73afecddae18d.jpg"
                                                           data-link="/catalog/ryby/oborudowanie/pompy-ryby/Pompa_dlya_akvariuma_YUvel_Rekord_1000_Rio_125180_Ekoflou_600lch_1005324.html?offer=33080">398 г</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <a class="b-common-item__add-to-cart js-complect-replace"
                                               href="javascript:void(0);"
                                               title=""
                                               data-offerid="33080">
                                                <span class="b-common-item__wrapper-link">
                                                    <span class="b-cart">
                                                        <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                                    </span>
                                                    <span class="b-common-item__price js-price-block">3065</span>
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

                                <div class="b-common-item js-product-complect-item" data-product-info='{"productid": 85914, "offerid": 85915, "offerprice": 5889, "groupid": 3}' data-product-group-title="Другие светильники" tabindex="0">
                                    <div class="b-common-item__image-wrap">
                                        <a class="b-common-item__image-link js-item-link" href="/catalog/ryby/oborudowanie/lampy-i-svetilniki-ryby/vneshniy-svetilnik-led-fixture-1200black.html?offer=85915" tabindex="0">
                                            <img class="b-common-item__image" src="/resize/240x240/upload/iblock/2d5/2d578432b4b4d7f4940e82b5895c8394.jpg" alt="Внешний светильник LED fiXture 1200black" title="Внешний светильник LED fiXture 1200black">
                                        </a>
                                    </div>
                                    <div class="b-common-item__info-center-block">
                                        <a class="b-common-item__description-wrap" href="/catalog/ryby/oborudowanie/lampy-i-svetilniki-ryby/vneshniy-svetilnik-led-fixture-1200black.html?offer=85915" tabindex="0">
                                            <span class="b-clipped-text b-clipped-text--three">
                                                <span>
                                                    <span class="span-strong">Sera</span> Внешний светильник LED fiXture 1200black
                                                </span>
                                            </span>
                                        </a>
                                        <div class="b-common-item__info">
                                            <div class="b-common-item__property">
                                                <span class="b-common-item__property-value">1.91 кг</span>
                                            </div>
                                            <div class="b-common-item__price">
                                                <span class="b-common-item__price-value">5 889</span>
                                                <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                            </div>
                                        </div>
                                        <div class="b-common-item__replace">
                                            <a href="javascript:void(0)" class="b-common-item__replace-link js-product-complect-replace js-this-product-complect">
                                                <span class="b-common-item__replace-text js-product-complect-replace-text">Поменять</span>
                                                <span class="b-icon b-icon--replace-complect b-icon--left-3"><?= new SvgDecorator('icon-arrow-down', 10, 12) ?></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="b-product-card-complect__item-replace js-product-complect-replace-item">
                                    <div class="js-product-item" data-productid="85914">
                                        <span class="b-common-item__image-wrap">
                                            <a class="b-common-item__image-link js-item-link" href="/catalog/ryby/oborudowanie/lampy-i-svetilniki-ryby/vneshniy-svetilnik-led-fixture-1200black.html?offer=85915">
                                                <img class="b-common-item__image js-weight-img"
                                                     src="/resize/240x240/upload/iblock/2d5/2d578432b4b4d7f4940e82b5895c8394.jpg"
                                                     alt="Внешний светильник LED fiXture 1200black"
                                                     title="Внешний светильник LED fiXture 1200black"/>
                                            </a>
                                        </span>
                                        <div class="b-common-item__info-center-block">
                                            <a class="b-common-item__description-wrap js-item-link" href="/catalog/ryby/oborudowanie/lampy-i-svetilniki-ryby/vneshniy-svetilnik-led-fixture-1200black.html?offer=85915" title="">
                                                <span class="b-clipped-text b-clipped-text--three">
                                                    <span><span class="span-strong">Sera</span> Внешний светильник LED fiXture 1200black</span>
                                                </span>
                                            </a>
                                            <div class="b-common-item__info">
                                                <div class="b-common-item__property">
                                                    <span class="b-common-item__property-value">250 л/ч</span>
                                                </div>
                                            </div>
                                            <div class="b-weight-container b-weight-container--list">
                                                <a class="b-weight-container__link  js-mobile-select js-select-mobile-package"
                                                   href="javascript:void(0);"
                                                   title="">1.91 кг</a>
                                                <div class="b-weight-container__dropdown-list__wrapper">
                                                    <div class="b-weight-container__dropdown-list"></div>
                                                </div>
                                                <ul class="b-weight-container__list">
                                                    <li class="b-weight-container__item">
                                                        <a href="javascript:void(0)"
                                                           class="b-weight-container__link js-price active-link"
                                                           data-oldprice=""
                                                           data-discount=""
                                                           data-price="5889"
                                                           data-offerid="85915"
                                                           data-image="/resize/240x240/upload/iblock/2d5/2d578432b4b4d7f4940e82b5895c8394.jpg"
                                                           data-link="/catalog/ryby/oborudowanie/lampy-i-svetilniki-ryby/vneshniy-svetilnik-led-fixture-1200black.html?offer=85915">1.91 кг</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <a class="b-common-item__add-to-cart js-complect-replace"
                                               href="javascript:void(0);"
                                               title=""
                                               data-offerid="85915">
                                                <span class="b-common-item__wrapper-link">
                                                    <span class="b-cart">
                                                        <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                                    </span>
                                                    <span class="b-common-item__price js-price-block">5889</span>
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
                            <span class="b-product-card-complect__price js-total-price-product-complect">10 891</span><span class="b-ruble b-ruble--product-information">&nbsp;₽</span>
                        </div>
                        <div class="b-product-card-complect__basket">
                            <a href="javascript:void(0)" class="b-product-card-complect__basket-link js-basket-add-complect js-this-product-complect">
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
                        <div class="b-common-item red" data-product-complect-groupid="2">
                            <div class="js-product-item" data-productid="42375">
                                <span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;"><img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/></span>
                                <span class="b-common-item__image-wrap">
                                    <a class="b-common-item__image-link js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Royal_Canin_Maxi_Adult_26_suhoy_korm_dlya_sobak_krupnyh_porod.html?offer=42376">
                                        <img
                                            src="/resize/240x240/upload/iblock/935/9359bb97482838f9f4ddc55bc1c7974c.jpg"
                                            class="b-common-item__image js-weight-img"
                                            alt="Maxi Adult 26 корм для собак от 15 месяцев до 5 лет,15 кг"
                                            title="">
                                    </a>
                                </span>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap js-item-link"
                                       href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Royal_Canin_Maxi_Adult_26_suhoy_korm_dlya_sobak_krupnyh_porod.html?offer=42376">
                                        <span class="b-clipped-text b-clipped-text--three">
                                            <span>
                                                <span class="span-strong">Royal Canin</span> Maxi Adult 26 корм для собак от 15 месяцев до 5 лет
                                            </span>
                                        </span>
                                    </a>
                                    <div class="b-common-item__info">
                                        <div class="b-common-item__property">
                                            <span class="b-common-item__property-value">250 л/ч</span>
                                        </div>
                                    </div>
                                    <div class="b-weight-container b-weight-container--list">
                                        <a class="b-weight-container__link  b-weight-container__link--mobile js-mobile-select js-select-mobile-package"
                                           href="javascript:void(0);">15 кг</a>
                                        <div class="b-weight-container__dropdown-list__wrapper">
                                            <div class="b-weight-container__dropdown-list"></div>
                                        </div>
                                        <ul class="b-weight-container__list">
                                            <li class="b-weight-container__item">
                                                <a data-price="1432" data-offerid="42379" data-image="/resize/240x240/upload/iblock/609/6098174ac3dd7e4b001c07c7423d29f9.jpg" data-pickup="Только под заказ" data-name="Maxi Adult 26 корм для собак от 15 месяцев до 5 лет,4 кг" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Royal_Canin_Maxi_Adult_26_suhoy_korm_dlya_sobak_krupnyh_porod.html?offer=42379" data-oldprice="1685" data-discount="253" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price">
                                                    4 кг
                                                </a>
                                            </li>
                                            <li class="b-weight-container__item">
                                                <a data-price="4441" data-offerid="42376" data-image="/resize/240x240/upload/iblock/935/9359bb97482838f9f4ddc55bc1c7974c.jpg" data-pickup="" data-name="Maxi Adult 26 корм для собак от 15 месяцев до 5 лет,15 кг" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Royal_Canin_Maxi_Adult_26_suhoy_korm_dlya_sobak_krupnyh_porod.html?offer=42376" data-oldprice="5225" data-discount="784" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price active-link">
                                                    15 кг
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart js-complect-replace"
                                       href="javascript:void(0);"
                                       data-offerid="42376">
                                        <span class="b-common-item__wrapper-link">
                                            <span class="b-cart">
                                                <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                            </span>
                                            <span class="b-common-item__price js-price-block">4441</span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </span>
                                    </a>
                                    <div class="b-common-item__additional-information">
                                        <div class="b-common-item__benefin js-sale-block">
                                            <span class="b-common-item__prev-price js-sale-origin">
                                                5225 <span class="b-ruble b-ruble--prev-price">₽</span>
                                            </span>
                                            <span class="b-common-item__discount">
                                                <span class="b-common-item__disc">Скидка</span>
                                                <span class="b-common-item__discount-price js-sale-sale">784</span>
                                                <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-common-item" data-product-complect-groupid="1">
                            <div class="js-product-item" data-productid="42461">
                                <span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;">
                                    <img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/>
                                </span>
                                <span class="b-common-item__image-wrap">
                                    <a class="b-common-item__image-link js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Hills_Standard_suhoy_korm_dlya_sobak_yagnenokris.html?offer=45780">
                                        <img
                                            src="/resize/240x240/upload/iblock/d9e/d9e5d9ff573d426363c2604e3f192f6b.jpg"
                                            class="b-common-item__image js-weight-img"
                                            alt="Science Plan Adult Advanced Fitness корм для взрослых собак, с ягненком и рисом, 12 кг"
                                            title="">
                                    </a>
                                </span>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap js-item-link"
                                   href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Hills_Standard_suhoy_korm_dlya_sobak_yagnenokris.html?offer=45780">
                                        <span class="b-clipped-text b-clipped-text--three">
                                            <span>
                                                <span class="span-strong">Hill's</span> Science Plan Adult Advanced Fitness корм для взрослых собак, с ягненком и рисом
                                            </span>
                                        </span>
                                    </a>
                                    <div class="b-common-item__info">
                                        <div class="b-common-item__property">
                                            <span class="b-common-item__property-value">250 л/ч</span>
                                        </div>
                                    </div>
                                    <div class="b-weight-container b-weight-container--list">
                                        <a class="b-weight-container__link  b-weight-container__link--mobile js-mobile-select js-select-mobile-package"
                                           href="javascript:void(0);">12 кг</a>
                                        <div class="b-weight-container__dropdown-list__wrapper">
                                            <div class="b-weight-container__dropdown-list"></div>
                                        </div>
                                        <ul class="b-weight-container__list">
                                            <li class="b-weight-container__item">
                                                <a data-price="1755" data-offerid="42462" data-image="/resize/240x240/upload/iblock/4d1/4d12d1598153fe79038bf6b0b77c2ad8.jpg" data-pickup="Только под заказ" data-name="Science Plan Adult Advanced Fitness корм для взрослых собак, с ягненком и рисом, 3 кг" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Hills_Standard_suhoy_korm_dlya_sobak_yagnenokris.html?offer=42462" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price">
                                                    3 кг
                                                </a>
                                            </li>
                                            <li class="b-weight-container__item">
                                                <a data-price="4195" data-offerid="42463" data-image="/resize/240x240/upload/iblock/d9e/d9e5d9ff573d426363c2604e3f192f6b.jpg" data-pickup="Только под заказ" data-name="Science Plan Adult Advanced Fitness корм для взрослых собак, с ягненком и рисом, 7,5 кг" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Hills_Standard_suhoy_korm_dlya_sobak_yagnenokris.html?offer=42463" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price">
                                                    7 кг 500 г
                                                </a>
                                            </li>
                                            <li class="b-weight-container__item">
                                                <a data-price="5835" data-offerid="45780" data-image="/resize/240x240/upload/iblock/d9e/d9e5d9ff573d426363c2604e3f192f6b.jpg" data-pickup="Только под заказ" data-name="Science Plan Adult Advanced Fitness корм для взрослых собак, с ягненком и рисом, 12 кг" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Hills_Standard_suhoy_korm_dlya_sobak_yagnenokris.html?offer=45780" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price active-link">
                                                    12 кг
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart js-complect-replace"
                                        href="javascript:void(0);"
                                        data-offerid="45780">
                                        <span class="b-common-item__wrapper-link">
                                            <span class="b-cart">
                                                <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                            </span>
                                            <span class="b-common-item__price js-price-block">5835</span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </span>
                                    </a>
                                    <div class="b-common-item__additional-information">
                                        <div class="b-common-item__info-wrap">
                                            <span class="b-common-item__text">
                                                Только под заказ
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-common-item blue" data-product-complect-groupid="2">
                            <div class="js-product-item" data-productid="42921">
                                <span class="b-common-item__image-wrap">
                                    <a class="b-common-item__image-link js-item-link" href="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=43476">
                                        <img
                                            src="/resize/240x240/upload/iblock/0c0/0c0b4fdf5e9b5f4dab75b45ae8150aa4.jpg"
                                            class="b-common-item__image js-weight-img"
                                            alt="Sterilised корм для стерилизованных кошек, с индейкой, 10 кг"
                                            title="">
                                    </a>
                                </span>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap js-item-link"
                                       href="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=43476">
                                        <span class="b-clipped-text b-clipped-text--three">
                                            <span>
                                                <span class="span-strong">Pro Plan</span> Sterilised корм для стерилизованных кошек, с индейкой
                                            </span>
                                        </span>
                                    </a>
                                    <div class="b-common-item__info">
                                        <div class="b-common-item__property">
                                            <span class="b-common-item__property-value">250 л/ч</span>
                                        </div>
                                    </div>
                                    <div class="b-weight-container b-weight-container--list">
                                        <a class="b-weight-container__link  b-weight-container__link--mobile js-mobile-select js-select-mobile-package"
                                           href="javascript:void(0);">10 кг</a>
                                        <div class="b-weight-container__dropdown-list__wrapper">
                                            <div class="b-weight-container__dropdown-list"></div>
                                        </div>
                                        <ul class="b-weight-container__list">
                                            <li class="b-weight-container__item">
                                                <a data-price="339" data-offerid="45693" data-image="/resize/240x240/upload/iblock/d81/d8159f52d88270330a87f13e453ca411.jpg" data-pickup="Только под заказ" data-name="Sterilised корм для стерилизованных кошек, с индейкой, 400 г" data-link="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=45693" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price">
                                                    400 г
                                                </a>
                                            </li>
                                            <li class="b-weight-container__item">
                                                <a data-price="1159" data-offerid="42922" data-image="/resize/240x240/upload/iblock/55d/55dcafcfc81deed8663d24f7b4ec3bb5.jpg" data-pickup="Только под заказ" data-name="Sterilised корм для стерилизованных кошек, с индейкой, 1,5 кг" data-link="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=42922" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price">
                                                    1 кг 500 г
                                                </a>
                                            </li>
                                            <li class="b-weight-container__item">
                                                <a data-price="1099" data-offerid="31113" data-image="/resize/240x240/upload/iblock/fd4/fd4647e4af2572cbbc517fbd209db822.jpg" data-pickup="" data-name="Sterilised корм для стерилизованных кошек, с индейкой, 1,9 кг" data-link="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=31113"  data-oldprice="" data-discount="" data-available="Нет в наличии" href="javascript:void(0)" class="b-weight-container__link js-price">
                                                    1 кг 900 г
                                                </a>
                                            </li>
                                            <li class="b-weight-container__item">
                                                <a data-price="1849" data-offerid="45702" data-image="/resize/240x240/upload/iblock/c8f/c8f3fae515d0bd6f4638fbcc82220f36.jpg" data-pickup="Только под заказ" data-name="Sterilised корм для стерилизованных кошек, с индейкой, 3 кг" data-link="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=45702" data-oldprice="2175" data-discount="326" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price">
                                                    3 кг
                                                </a>
                                            </li>
                                            <li class="b-weight-container__item">
                                                <a data-price="5875" data-offerid="43476" data-image="/resize/240x240/upload/iblock/0c0/0c0b4fdf5e9b5f4dab75b45ae8150aa4.jpg" data-pickup="" data-name="Sterilised корм для стерилизованных кошек, с индейкой, 10 кг" data-link="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=43476" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price active-link">
                                                    10 кг
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart js-complect-replace"
                                       href="javascript:void(0);"
                                       data-offerid="43476">
                                        <span class="b-common-item__wrapper-link">
                                            <span class="b-cart">
                                                <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                            </span>
                                            <span class="b-common-item__price js-price-block">5875</span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="b-common-item blue" data-product-complect-groupid="3">
                            <div class="js-product-item" data-productid="43407">
                                <span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;"><img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/></span> <span class="b-common-item__image-wrap">
                                    <a class="b-common-item__image-link js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Eukanuba_Adultarge_suhoy_korm_dlya_sobak_krupnyh_porod_yagnenok_i_ris.html?offer=43408">
                                        <img
                                            src="/resize/240x240/upload/iblock/f3a/f3afe36d5656c2a8a261a3657a6b696e.jpg"
                                            class="b-common-item__image js-weight-img"
                                            alt="Adult Large Корм сухой для собак крупных пород, ягненок и рис"
                                            title="">
                                    </a>
                                </span>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap js-item-link"
                                   href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Eukanuba_Adultarge_suhoy_korm_dlya_sobak_krupnyh_porod_yagnenok_i_ris.html?offer=43408">
                                        <span class="b-clipped-text b-clipped-text--three">
                                            <span>
                                                <span class="span-strong">Eukanuba</span> Adult Large Корм сухой для собак крупных пород, ягненок и рис
                                            </span>
                                        </span>
                                    </a>
                                    <div class="b-common-item__info">
                                        <div class="b-common-item__property">
                                            <span class="b-common-item__property-value">250 л/ч</span>
                                        </div>
                                    </div>
                                    <div class="b-weight-container b-weight-container--list">
                                        <a class="b-weight-container__link  b-weight-container__link--mobile js-mobile-select js-select-mobile-package"
                                           href="javascript:void(0);">12 кг</a>
                                        <div class="b-weight-container__dropdown-list__wrapper">
                                            <div class="b-weight-container__dropdown-list"></div>
                                        </div>
                                        <ul class="b-weight-container__list">
                                            <li class="b-weight-container__item">
                                                <a data-price="1489" data-offerid="31701" data-image="/resize/240x240/upload/iblock/751/75146db0cd4ec5be678c4417289645bf.jpg" data-pickup="Только под заказ" data-name="Adult Large Корм сухой для собак крупных пород, ягненок и рис" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Eukanuba_Adultarge_suhoy_korm_dlya_sobak_krupnyh_porod_yagnenok_i_ris.html?offer=31701" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price">
                                                    2 кг 500 г
                                                </a>
                                            </li>
                                            <li class="b-weight-container__item">
                                                <a data-price="5865" data-offerid="43408" data-image="/resize/240x240/upload/iblock/f3a/f3afe36d5656c2a8a261a3657a6b696e.jpg" data-pickup="Только под заказ" data-name="Adult Large Корм сухой для собак крупных пород, ягненок и рис" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Eukanuba_Adultarge_suhoy_korm_dlya_sobak_krupnyh_porod_yagnenok_i_ris.html?offer=43408" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price active-link">
                                                    12 кг
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart js-complect-replace"
                                       href="javascript:void(0);"
                                       data-offerid="43408">
                                        <span class="b-common-item__wrapper-link">
                                            <span class="b-cart">
                                                <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                            </span>
                                            <span class="b-common-item__price js-price-block">5865</span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </span>
                                    </a>
                                    <div class="b-common-item__additional-information">
                                        <div class="b-common-item__info-wrap">
                                            <span class="b-common-item__text">
                                                Только под заказ
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-common-item yellow" data-product-complect-groupid="1">
                            <div class="js-product-item" data-productid="43589">
                                <span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;"><img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/></span> <span class="b-common-item__image-wrap">
                                    <a class="b-common-item__image-link js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Avva_Premium_suhoy_korm_dlya_shchenkov_krupnyh_porod_kuritsa.html?offer=45807">
                                        <img
                                            src="/resize/240x240/upload/iblock/11b/11bf72b0378cf0d00783e344e02f7c74.jpg"
                                            class="b-common-item__image js-weight-img"
                                            alt="Premium Корм сухой для щенков крупных пород, курица"
                                            title="">
                                    </a>
                                </span>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap js-item-link"
                                       href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Avva_Premium_suhoy_korm_dlya_shchenkov_krupnyh_porod_kuritsa.html?offer=45807">
                                        <span class="b-clipped-text b-clipped-text--three">
                                            <span>
                                                <span class="span-strong">Авва</span> Premium Корм сухой для щенков крупных пород, курица
                                            </span>
                                        </span>
                                    </a>
                                    <div class="b-common-item__info">
                                        <div class="b-common-item__property">
                                            <span class="b-common-item__property-value">250 л/ч</span>
                                        </div>
                                    </div>
                                    <div class="b-weight-container b-weight-container--list">
                                        <a class="b-weight-container__link  b-weight-container__link--mobile js-mobile-select js-select-mobile-package"
                                           href="javascript:void(0);">12 кг</a>
                                        <div class="b-weight-container__dropdown-list__wrapper">
                                            <div class="b-weight-container__dropdown-list"></div>
                                        </div>
                                        <ul class="b-weight-container__list">
                                            <li class="b-weight-container__item">
                                                <a data-price="859" data-offerid="43590" data-image="/resize/240x240/upload/iblock/985/98588d42a790fe84bbb53bf595737236.jpg" data-pickup="Только под заказ" data-name="Premium Корм сухой для щенков крупных пород, курица" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Avva_Premium_suhoy_korm_dlya_shchenkov_krupnyh_porod_kuritsa.html?offer=43590" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price">
                                                    3 кг
                                                </a>
                                            </li>
                                            <li class="b-weight-container__item">
                                                <a data-price="2979" data-offerid="45807" data-image="/resize/240x240/upload/iblock/11b/11bf72b0378cf0d00783e344e02f7c74.jpg" data-pickup="Только под заказ" data-name="Premium Корм сухой для щенков крупных пород, курица" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Avva_Premium_suhoy_korm_dlya_shchenkov_krupnyh_porod_kuritsa.html?offer=45807" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price active-link">
                                                    12 кг
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart js-complect-replace"
                                       href="javascript:void(0);"
                                       data-offerid="45807">
                                        <span class="b-common-item__wrapper-link">
                                            <span class="b-cart">
                                                <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                            </span>
                                            <span class="b-common-item__price js-price-block">2979</span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </span>
                                    </a>
                                    <div class="b-common-item__additional-information">
                                        <div class="b-common-item__info-wrap">
                                            <span class="b-common-item__text">
                                                Только под заказ
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-common-item yellow" data-product-complect-groupid="3">
                            <div class="js-product-item" data-productid="43596">
                                <span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;">
                                    <img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"/>
                                </span>
                                <span class="b-common-item__image-wrap">
                                    <a class="b-common-item__image-link js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Avva_Premium_suhoy_korm_dlya_sobak_vseh_porod_yagnenokris.html?offer=45768">
                                        <img
                                            src="/static/build/images/inhtml/no_image_list.jpg"
                                            data-product-complect-slider="/resize/240x240/upload/iblock/153/153aca699f53330b851e70f2c01e5559.jpg"
                                            class="b-common-item__image js-weight-img not_loaded_src"
                                            alt="Premium Корм сухой для собак всех пород, ягненок/рис"
                                            title="">
                                    </a>
                                </span>
                                <div class="b-common-item__info-center-block">
                                    <a class="b-common-item__description-wrap js-item-link"
                                       href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Avva_Premium_suhoy_korm_dlya_sobak_vseh_porod_yagnenokris.html?offer=45768">
                                        <span class="b-clipped-text b-clipped-text--three">
                                            <span>
                                                <span class="span-strong">Авва</span> Premium Корм сухой для собак всех пород, ягненок/рис
                                            </span>
                                        </span>
                                    </a>
                                    <div class="b-common-item__info">
                                        <div class="b-common-item__property">
                                            <span class="b-common-item__property-value">250 л/ч</span>
                                        </div>
                                    </div>
                                    <div class="b-weight-container b-weight-container--list">
                                        <a class="b-weight-container__link  b-weight-container__link--mobile js-mobile-select js-select-mobile-package"
                                           href="javascript:void(0);">12 кг</a>
                                        <div class="b-weight-container__dropdown-list__wrapper">
                                            <div class="b-weight-container__dropdown-list"></div>
                                        </div>
                                        <ul class="b-weight-container__list">
                                            <li class="b-weight-container__item">
                                                <a data-price="859" data-offerid="43597" data-image="/resize/240x240/upload/iblock/27f/27f89c2cd4d7d805077d455875d72aa3.jpg" data-pickup="Только под заказ" data-name="Premium Корм сухой для собак всех пород, ягненок/рис" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Avva_Premium_suhoy_korm_dlya_sobak_vseh_porod_yagnenokris.html?offer=43597" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price">
                                                    3 кг
                                                </a>
                                            </li>
                                            <li class="b-weight-container__item">
                                                <a data-price="2979" data-offerid="45768" data-image="/resize/240x240/upload/iblock/153/153aca699f53330b851e70f2c01e5559.jpg" data-pickup="Только под заказ" data-name="Premium Корм сухой для собак всех пород, ягненок/рис" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/Avva_Premium_suhoy_korm_dlya_sobak_vseh_porod_yagnenokris.html?offer=45768" data-oldprice="" data-discount="" data-available="" href="javascript:void(0)" class="b-weight-container__link js-price active-link">
                                                    12 кг
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart js-complect-replace"
                                       href="javascript:void(0);"
                                       data-offerid="45768">
                                        <span class="b-common-item__wrapper-link">
                                            <span class="b-cart">
                                                <span class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart-complect', 12, 16) ?></span>
                                            </span>
                                            <span class="b-common-item__price js-price-block">2979</span>
                                            <span class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                                        </span>
                                    </a>
                                    <div class="b-common-item__additional-information">
                                        <div class="b-common-item__info-wrap">
                                            <span class="b-common-item__text">
                                                Только под заказ
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                            'HL_ID'              => HighloadHelper::getIdByName('Comments'),
                            'OBJECT_ID'          => $product->getId(),
                            'SORT_DESC'          => 'Y',
                            'ITEMS_COUNT'        => 5,
                            'ACTIVE_DATE_FORMAT' => 'd j Y',
                            'TYPE'               => 'catalog',
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
                            'OFFER'   => $offer,
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
                                        'IBLOCK_ID'            => IblockUtils::getIblockId(IblockType::PUBLICATION,
                                            IblockCode::SHARES),
                                        'ITEM_ID'              => $share->getId(),
                                        'TITLE'                => 'Товары по акции',
                                        'COUNT_ON_PAGE'        => 20,
                                        'PROPERTY_CODE'        => 'PRODUCTS',
                                        'FILTER_FIELD'         => 'XML_ID',
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
        'PATH'           => '/local/include/blocks/advantages.php',
        'EDIT_TEMPLATE'  => '',
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
        'NAME'        => 'Блок похожих товаров',
        'MODE'        => 'php',
    ]
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
