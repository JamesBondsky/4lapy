<?php
/**
 * @var ProductDetailRequest $productDetailRequest
 * @var CMain                $APPLICATION
 */

use FourPaws\App\Templates\ViewsEnum;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\CatalogBundle\Dto\ProductDetailRequest;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\HighloadHelper;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $APPLICATION;

$offerId = $productDetailRequest->getOfferId();
/** @var Product $product */
$product = $APPLICATION->IncludeComponent(
    'fourpaws:catalog.element.detail',
    '',
    [
        'CODE'      => $productDetailRequest->getProductSlug(),
        'OFFER_ID'  => $offerId,
        'SET_TITLE' => 'Y',
    ],
    false,
    ['HIDE_ICONS' => 'Y']
);

/** @var Offer $offer */
if (empty($offerId)) {
    $offer   = $product->getOffers()->first();
    $offerId = $offer->getId();
}
if (!($offer instanceof Offer)) {
    $offerQuery = new OfferQuery();
    $offer      = $offerQuery->withFilter(['=ID' => $offerId])->exec()->first();
}
?>
    <div class="b-product-card" data-productid="<?= $product->getId() ?>" data-offerId="<?= $offer->getId() ?>" data-url="/ajax/catalog/product-info/">
        <div class="b-container">
            <?php
            $APPLICATION->IncludeComponent(
                'fourpaws:breadcrumbs',
                '',
                [
                    'IBLOCK_ELEMENT' => $product,
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );
            ?>
            <div class="b-product-card__top">
                <div class="b-product-card__title-product">
                    <?php $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_TITLE_VIEW); ?>
                    <div class="b-common-item b-common-item--card">
                        <div class="b-common-item__rank b-common-item__rank--card">
                            <?php $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_RATING_STARS_VIEW); ?>
                            <div class="b-common-item__rank-wrapper">
                                <?php
                                /** @todo implement Акции и Шильдики
                                 * <span class="b-common-item__rank-text b-common-item__rank-text--green
                                 * b-common-item__rank-text--card">Новинка</span>
                                 * <span class="b-common-item__rank-text b-common-item__rank-text--red">4+1 в подарок
                                 * при покупке</span>
                                 */ ?>
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
                
                <?php
                /**
                 * @todo implement and remove
                 */
                include 'tmp_action_set.html.php';
                ?>
            </div>
            <div class="b-product-card__tab">
                <div class="b-tab">
                    <div class="b-tab-title">
                        <ul class="b-tab-title__list">
                            <?php
                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_DESCRIPTION_TAB_HEADER);
                            
                            if ($product->getComposition()->getText()) { ?>
                                <li class="b-tab-title__item js-tab-item">
                                    <a class="b-tab-title__link js-tab-link"
                                       href="javascript:void(0);" title="Состав"
                                       data-tab="composition"><span
                                                class="b-tab-title__text">Состав</span></a>
                                </li>
                            <?php }
                            
                            if ($product->getNormsOfUse()->getText()) { ?>
                                <li class="b-tab-title__item js-tab-item">
                                    <a class="b-tab-title__link js-tab-link"
                                       href="javascript:void(0);" title="Рекомендации по питанию"
                                       data-tab="recommendations"><span class="b-tab-title__text">Рекомендации по питанию</span></a>
                                </li>
                            <?php }
                            
                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_RATING_TAB_HEADER_VIEW);
                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_DELIVERY_PAYMENT_TAB_HEADER);
                            
                            /** наличие меняется аяксом */?>
                            <li class="b-tab-title__item js-tab-item">
                                <a class="b-tab-title__link js-tab-link"
                                   href="javascript:void(0);" title="Наличие в магазинах"
                                   data-tab="availability">
                                    <span class="b-tab-title__text">Наличие в магазинах
                                        <span class="b-tab-title__number">(0)</span>
                                    </span>
                                </a>
                            </li>
                            <li class="b-tab-title__item js-tab-item">
                                <a class="b-tab-title__link js-tab-link"
                                   href="javascript:void(0);" title="Акция"
                                   data-tab="shares"><span class="b-tab-title__text">Акция</span></a>
                            </li>
                        </ul>
                    </div>
                    <div class="b-tab-content">
                        <?php
                        $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_DESCRIPTION_TAB);
                        
                        if ($product->getComposition()->getText()) { ?>
                            <div class="b-tab-content__container js-tab-content" data-tab-content="composition">
                                <div class="b-description-tab b-description-tab--full">
                                    <div class="b-description-tab__column b-description-tab__column--full">
                                        <h2>Состав</h2>
                                        <p><?= $product->getComposition()->getText() ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php }
                        
                        if ($product->getNormsOfUse()->getText()) { ?>
                            <div class="b-tab-content__container js-tab-content" data-tab-content="recommendations">
                                <div class="b-description-tab b-description-tab--full">
                                    <div class="b-description-tab__column b-description-tab__column--full">
                                        <p><?= $product->getNormsOfUse()->getText() ?></p>
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
                        );
                        ?>
                        <?php $APPLICATION->IncludeComponent(
                            'fourpaws:city.delivery.info',
                            'catalog.detail.tab',
                            [
                                'DELIVERY_CODES' => [DeliveryService::INNER_DELIVERY_CODE],
                            ],
                            false,
                            ['HIDE_ICONS' => 'Y']
                        ) ?>
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
                        <div class="b-tab-content__container js-tab-content" data-tab-content="shares">
                            <h2 class="b-title b-title--advice b-title--stock">Акция</h2>
                            <div class="b-stock">
                                <div class="b-characteristics-tab b-characteristics-tab--stock">
                                    <ul class="b-characteristics-tab__list">
                                        <li class="b-characteristics-tab__item b-characteristics-tab__item--stock">
                                            <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--stock">
                                                <span>Название</span>
                                                <div class="b-characteristics-tab__dots"></div>
                                            </div>
                                            <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--stock">
                                                4+1: подарок при покупке
                                            </div>
                                        </li>
                                        <li class="b-characteristics-tab__item b-characteristics-tab__item--stock">
                                            <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--stock">
                                                <span>Срок проведения</span>
                                                <div class="b-characteristics-tab__dots"></div>
                                            </div>
                                            <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--stock">
                                                5 июня 2017 — 25 августа 2017
                                            </div>
                                        </li>
                                        <li class="b-characteristics-tab__item b-characteristics-tab__item--stock">
                                            <div class="b-characteristics-tab__characteristics-text b-characteristics-tab__characteristics-text--stock">
                                                <span>Описание</span>
                                                <div class="b-characteristics-tab__dots"></div>
                                            </div>
                                            <div class="b-characteristics-tab__characteristics-value b-characteristics-tab__characteristics-value--stock">
                                                При покупке четырех кормов, пятый вы получите бесплатно
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="b-stock__gift">
                                    <div class="b-advice b-advice--stock"><a
                                                class="b-advice__item b-advice__item--stock"
                                                href="javascript:void(0)" title=""><span
                                                    class="b-advice__image-wrapper b-advice__image-wrapper--stock"><img
                                                        class="b-advice__image"
                                                        src="/static/build/images/content/fresh-step.png"
                                                        alt="" title="" role="presentation" /></span><span
                                                    class="b-advice__block b-advice__block--stock"><span
                                                        class="b-advice__text b-advice__text--red">Подарок по акции</span><span
                                                        class="b-clipped-text b-clipped-text--advice"><span><strong>Китекат</strong> корм для кошек рыба в соусе</span></span><span
                                                        class="b-advice__info b-advice__info--stock"><span
                                                            class="b-advice__weight">85 г</span><span
                                                            class="b-advice__cost">13,40 <span
                                                                class="b-ruble b-ruble--advice">₽</span></span></span></span></a>
                                    </div>
                                    <a class="b-button b-button--bordered-grey" href="javascript:void(0)" title="">Выбрать
                                                                                                                   подарок</a>
                                </div>
                            </div>
                            <h3 class="b-title b-title--light">Товары по акции</h3>
                            <div class="b-common-wrapper b-common-wrapper--stock js-product-stock-mobile">
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/royal-canin-2.jpg" alt="Роял Канин"
                                                title="" /></span>
                                    <div class="b-common-item__info-center-block"><a
                                                class="b-common-item__description-wrap"
                                                href="javascript:void(0);"
                                                title=""><span
                                                    class="b-clipped-text"><span><strong>Роял Канин</strong> корм для собак крупных пород макси эдалт</span></span></a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                                               href="javascript:void(0);" title=""></a>
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price active-link"
                                                            href="javascript:void(0);" data-price="100"
                                                            data-image="/static/build/images/content/royal-canin-2.jpg">4
                                                                                                                        кг</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="4 199"
                                                            data-image="/static/build/images/content/abba.png">6 кг</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price unavailable-link"
                                                            href="javascript:void(0);" data-price="4 199"
                                                            data-image="/static/build/images/content/abba.png">15 кг</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator(
                                                            'icon-cart', 16, 16
                                                        ) ?></span></span><span
                                                        class="b-common-item__price js-price-block">100</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/hills-cat.jpg" alt="Хиллс" title="" /></span>
                                    <div class="b-common-item__info-center-block"><a
                                                class="b-common-item__description-wrap"
                                                href="javascript:void(0);"
                                                title=""><span
                                                    class="b-clipped-text"><span><strong>Хиллс</strong> корм для кошек тунец стерилайз</span></span></a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                                               href="javascript:void(0);" title=""></a>
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price active-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/hills-cat.jpg">3,5
                                                                                                                    кг</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">8 кг</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price unavailable-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">12 кг</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator(
                                                            'icon-cart', 16, 16
                                                        ) ?></span></span><span
                                                        class="b-common-item__price js-price-block">2 585</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg"
                                                alt="CleanCat"
                                                title="" /></span>
                                    <div class="b-common-item__info-center-block"><a
                                                class="b-common-item__description-wrap"
                                                href="javascript:void(0);"
                                                title=""><span
                                                    class="b-clipped-text"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                                               href="javascript:void(0);" title=""></a>
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price active-link"
                                                            href="javascript:void(0);" data-price="353"
                                                            data-image="/static/build/images/content/clean-cat.jpg">5
                                                                                                                    л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10
                                                                                                                   л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price unavailable-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">18 кг</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator(
                                                            'icon-cart', 16, 16
                                                        ) ?></span></span><span
                                                        class="b-common-item__price js-price-block">353</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg"
                                                alt="CleanCat"
                                                title="" /></span>
                                    <div class="b-common-item__info-center-block"><a
                                                class="b-common-item__description-wrap"
                                                href="javascript:void(0);"
                                                title=""><span
                                                    class="b-clipped-text"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                                               href="javascript:void(0);" title=""></a>
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price active-link"
                                                            href="javascript:void(0);" data-price="353"
                                                            data-image="/static/build/images/content/clean-cat.jpg">5
                                                                                                                    л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10
                                                                                                                   л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price unavailable-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">18 кг</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator(
                                                            'icon-cart', 16, 16
                                                        ) ?></span></span><span
                                                        class="b-common-item__price js-price-block">353</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/hills-cat.jpg" alt="Хиллс" title="" /></span>
                                    <div class="b-common-item__info-center-block"><a
                                                class="b-common-item__description-wrap"
                                                href="javascript:void(0);"
                                                title=""><span
                                                    class="b-clipped-text"><span><strong>Хиллс</strong> корм для кошек тунец стерилайз</span></span></a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                                               href="javascript:void(0);" title=""></a>
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price active-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/hills-cat.jpg">3,5
                                                                                                                    кг</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">8 кг</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price unavailable-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">12 кг</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator(
                                                            'icon-cart', 16, 16
                                                        ) ?></span></span><span
                                                        class="b-common-item__price js-price-block">2 585</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg"
                                                alt="CleanCat"
                                                title="" /></span>
                                    <div class="b-common-item__info-center-block"><a
                                                class="b-common-item__description-wrap"
                                                href="javascript:void(0);"
                                                title=""><span
                                                    class="b-clipped-text"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                                               href="javascript:void(0);" title=""></a>
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price active-link"
                                                            href="javascript:void(0);" data-price="353"
                                                            data-image="/static/build/images/content/clean-cat.jpg">5
                                                                                                                    л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10
                                                                                                                   л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price unavailable-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">18 кг</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator(
                                                            'icon-cart', 16, 16
                                                        ) ?></span></span><span
                                                        class="b-common-item__price js-price-block">353</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg"
                                                alt="CleanCat"
                                                title="" /></span>
                                    <div class="b-common-item__info-center-block"><a
                                                class="b-common-item__description-wrap"
                                                href="javascript:void(0);"
                                                title=""><span
                                                    class="b-clipped-text"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                                               href="javascript:void(0);" title=""></a>
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price active-link"
                                                            href="javascript:void(0);" data-price="353"
                                                            data-image="/static/build/images/content/clean-cat.jpg">5
                                                                                                                    л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10
                                                                                                                   л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price unavailable-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">18 кг</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator(
                                                            'icon-cart', 16, 16
                                                        ) ?></span></span><span
                                                        class="b-common-item__price js-price-block">353</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/hills-cat.jpg" alt="Хиллс" title="" /></span>
                                    <div class="b-common-item__info-center-block"><a
                                                class="b-common-item__description-wrap"
                                                href="javascript:void(0);"
                                                title=""><span
                                                    class="b-clipped-text"><span><strong>Хиллс</strong> корм для кошек тунец стерилайз</span></span></a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                                               href="javascript:void(0);" title=""></a>
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price active-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/hills-cat.jpg">3,5
                                                                                                                    кг</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">8 кг</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price unavailable-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">12 кг</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator(
                                                            'icon-cart', 16, 16
                                                        ) ?></span></span><span
                                                        class="b-common-item__price js-price-block">2 585</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg"
                                                alt="CleanCat"
                                                title="" /></span>
                                    <div class="b-common-item__info-center-block"><a
                                                class="b-common-item__description-wrap"
                                                href="javascript:void(0);"
                                                title=""><span
                                                    class="b-clipped-text"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                                               href="javascript:void(0);" title=""></a>
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price active-link"
                                                            href="javascript:void(0);" data-price="353"
                                                            data-image="/static/build/images/content/clean-cat.jpg">5
                                                                                                                    л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10
                                                                                                                   л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price unavailable-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">18 кг</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator(
                                                            'icon-cart', 16, 16
                                                        ) ?></span></span><span
                                                        class="b-common-item__price js-price-block">353</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg"
                                                alt="CleanCat"
                                                title="" /></span>
                                    <div class="b-common-item__info-center-block"><a
                                                class="b-common-item__description-wrap"
                                                href="javascript:void(0);"
                                                title=""><span
                                                    class="b-clipped-text"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                                        <div class="b-weight-container b-weight-container--list">
                                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                                               href="javascript:void(0);" title=""></a>
                                            <ul class="b-weight-container__list">
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price active-link"
                                                            href="javascript:void(0);" data-price="353"
                                                            data-image="/static/build/images/content/clean-cat.jpg">5
                                                                                                                    л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10
                                                                                                                   л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price unavailable-link"
                                                            href="javascript:void(0);" data-price="2 585"
                                                            data-image="/static/build/images/content/brit.png">18 кг</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator(
                                                            'icon-cart', 16, 16
                                                        ) ?></span></span><span
                                                        class="b-common-item__price js-price-block">353</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
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
