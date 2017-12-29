<?php
/**
 * @var ProductDetailRequest $productDetailRequest
 * @var CMain                $APPLICATION
 */

use FourPaws\App\Templates\ViewsEnum;
use FourPaws\CatalogBundle\Dto\ProductDetailRequest;
use FourPaws\Decorators\SvgDecorator;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $APPLICATION;

$product = $APPLICATION->IncludeComponent(
    'fourpaws:catalog.element.detail',
    '',
    [
        'CODE' => $productDetailRequest->getProductSlug(),
    ],
    $component
);

$APPLICATION->IncludeComponent('fourpaws:catalog.product.reviews', 'product_tab', [], $component);

?>
    <div class="b-product-card">
        <div class="b-container">
            <?php
            $APPLICATION->IncludeComponent('fourpaws:catalog.breadcrumbs', 'product', [], $component);
            ?>
            <div class="b-product-card__top">
                <div class="b-product-card__title-product">
                    <?php
                    $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_TITLE_VIEW);
                    ?>
                    <div class="b-common-item b-common-item--card">
                        <div class="b-common-item__rank b-common-item__rank--card">
                            <?php
                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_RATING_STARS_VIEW);
                            /**
                             * @todo implement Акции и Шильдики
                             */
                            ?>
                            <div class="b-common-item__rank-wrapper">
                                <span class="b-common-item__rank-text b-common-item__rank-text--green b-common-item__rank-text--card">Новинка</span>
                                <span class="b-common-item__rank-text b-common-item__rank-text--red">4+1 в подарок при покупке</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-product-card__product">
                    <div class="b-product-card__permutation-weight js-weight-tablet"></div>
                    <?php
                    $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_SLIDER_VIEW);
                    ?>

                    <div class="b-product-card__info-product js-weight-here">
                        <?php
                        $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_OFFERS_VIEW);
                        $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_CURRENT_OFFER_INFO);
                        ?>
                    </div>
                </div>

                <div class="b-advice">
                    <h2 class="b-title b-title--advice">Часто берут вместе</h2>
                    <div class="b-advice__list">
                        <div class="b-advice__list-items">
                            <a class="b-advice__item" href="javascript:void(0)" title="">
                                <span class="b-advice__image-wrapper"><img class="b-advice__image"
                                                                           src="/static/build/images/content/akana.png"
                                                                           alt=""
                                                                           title="" role="presentation"/></span>
                                <span class="b-advice__block"><span
                                            class="b-clipped-text b-clipped-text--advice"><span><strong>Акана</strong> корм для собак всех пород ягненок/яблоки</span></span>
                                    <span class="b-advice__info"><span class="b-advice__weight">6 кг</span><span
                                                class="b-advice__cost">3 719 <span
                                                    class="b-ruble b-ruble--advice">₽</span></span></span></span>
                            </a>
                            <div class="b-advice__sign b-advice__sign--plus"></div>
                            <a class="b-advice__item" href="javascript:void(0)" title=""><span
                                        class="b-advice__image-wrapper"><img class="b-advice__image"
                                                                             src="/static/build/images/content/food-1.jpg"
                                                                             alt=""
                                                                             title="" role="presentation"/></span><span
                                        class="b-advice__block"><span
                                            class="b-clipped-text b-clipped-text--advice"><span><strong>Хиллс</strong> корм для собак с курицей консервы</span></span><span
                                            class="b-advice__info"><span class="b-advice__weight">370 г</span><span
                                                class="b-advice__cost">239 <span
                                                    class="b-ruble b-ruble--advice">₽</span></span></span></span></a>
                            <div
                                    class="b-advice__sign b-advice__sign--plus"></div>
                            <a class="b-advice__item" href="javascript:void(0)" title=""><span
                                        class="b-advice__image-wrapper"><img class="b-advice__image"
                                                                             src="/static/build/images/content/fresh-step.png"
                                                                             alt=""
                                                                             title="" role="presentation"/></span><span
                                        class="b-advice__block"><span
                                            class="b-clipped-text b-clipped-text--advice"><span><strong>Роял Канин</strong> корм для поощрения при обучении и дресси…</span></span><span
                                            class="b-advice__info"><span class="b-advice__weight">50 г</span><span
                                                class="b-advice__cost">57 <span
                                                    class="b-ruble b-ruble--advice">₽</span></span></span></span></a>
                        </div>
                        <div class="b-advice__list-cost">
                            <div class="b-advice__sign b-advice__sign--equally"></div>
                            <div class="b-advice__cost-wrapper">
                                <span class="b-advice__total-price">4 015 <span class="b-ruble b-ruble--total">₽</span></span>
                                <a class="b-advice__basket-link" href="javascript:void(0)" title="">
                                    <span class="b-advice__basket-text">В корзину</span>
                                    <span class="b-icon b-icon--advice"><?= new SvgDecorator(
                                            'icon-cart',
                                            20,
                                            20
                                        ) ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-product-card__tab">
                <div class="b-tab">
                    <div class="b-tab-title">
                        <ul class="b-tab-title__list">
                            <?php
                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_DETAIL_DESCRIPTION_TAB_HEADER);

                            /**
                             * @todo Состава пока нет
                             */
                            //<li class="b-tab-title__item js-tab-item">
                            //    <a class="b-tab-title__link js-tab-link"
                            //    href="javascript:void(0);" title="Состав"
                            //    data-tab="composition"><span
                            //    class="b-tab-title__text">Состав</span></a>
                            //</li>
                            /**
                             * @todo Рекоммендация по питанию пока нет
                             */
                            /*<li class="b-tab-title__item js-tab-item">
                                <a class="b-tab-title__link js-tab-link"
                                   href="javascript:void(0);" title="Рекомендации по питанию"
                                   data-tab="recommendations"><span class="b-tab-title__text">Рекомендации по питанию</span></a>
                            </li>*/

                            $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_RATING_TAB_HEADER_VIEW);
                            ?>
                            <li class="b-tab-title__item js-tab-item">
                                <a class="b-tab-title__link js-tab-link"
                                   href="javascript:void(0);" title="Доставка и оплата"
                                   data-tab="data"><span class="b-tab-title__text">Доставка и оплата</span></a>
                            </li>
                            <li class="b-tab-title__item js-tab-item">
                                <a class="b-tab-title__link js-tab-link"
                                   href="javascript:void(0);" title="Наличие в магазинах"
                                   data-tab="availability"><span class="b-tab-title__text">Наличие в магазинах<span
                                                class="b-tab-title__number">(21)</span></span></a>
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

                        /**
                         * @todo Состава пока нет
                         */
                        /*<div class="b-tab-content__container js-tab-content" data-tab-content="composition">
                            <div>2</div>
                        </div>*/

                        /**
                         * @todo Рекомендация по питанию пока нет
                         */
                        /*
                         <div class="b-tab-content__container js-tab-content" data-tab-content="recommendations">
                            <div>3</div>
                        </div>
                        */
                        $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_RATING_TAB_VIEW);
                        ?>
                        <div class="b-tab-content__container js-tab-content" data-tab-content="data">
                            <div class="b-tab-shipping">
                                <div class="b-tab-shipping__inline-table">
                                    <table class="b-tab-shipping__table">
                                        <caption class="b-tab-shipping__caption">Стоимость доставки</caption>
                                        <tbody class="b-tab-shipping__tbody">
                                        <tr class="b-tab-shipping__tr">
                                            <th class="b-tab-shipping__th b-tab-shipping__th--first">Заказ на сумму</th>
                                            <th class="b-tab-shipping__th b-tab-shipping__th--second">Доставка</th>
                                        </tr>
                                        <tr class="b-tab-shipping__tr b-tab-shipping__tr--first-line">
                                            <td class="b-tab-shipping__td b-tab-shipping__td--first">до 500 <span
                                                        class="b-ruble b-ruble--table-tab-shipping">₽</span>
                                            </td>
                                            <td class="b-tab-shipping__td b-tab-shipping__td--second">—</td>
                                        </tr>
                                        <tr class="b-tab-shipping__tr">
                                            <td class="b-tab-shipping__td b-tab-shipping__td--first">500 — 1 999 <span
                                                        class="b-ruble b-ruble--table-tab-shipping">₽</span>
                                            </td>
                                            <td class="b-tab-shipping__td b-tab-shipping__td--second">200 <span
                                                        class="b-ruble b-ruble--table-tab-shipping">₽</span>
                                            </td>
                                        </tr>
                                        <tr class="b-tab-shipping__tr">
                                            <td class="b-tab-shipping__td b-tab-shipping__td--first">от 2 000</td>
                                            <td class="b-tab-shipping__td b-tab-shipping__td--second">бесплатно</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="b-tab-shipping__inline-table b-tab-shipping__inline-table--right">
                                    <table class="b-tab-shipping__table">
                                        <caption class="b-tab-shipping__caption">Время доставки</caption>
                                        <tbody class="b-tab-shipping__tbody">
                                        <tr class="b-tab-shipping__tr">
                                            <th class="b-tab-shipping__th b-tab-shipping__th--first">Время заказа</th>
                                            <th class="b-tab-shipping__th b-tab-shipping__th--second">Доставка</th>
                                        </tr>
                                        <tr class="b-tab-shipping__tr b-tab-shipping__tr--first-line">
                                            <td class="b-tab-shipping__td b-tab-shipping__td--first">до 14:00</td>
                                            <td class="b-tab-shipping__td b-tab-shipping__td--second">в тот же день</td>
                                        </tr>
                                        <tr class="b-tab-shipping__tr">
                                            <td class="b-tab-shipping__td b-tab-shipping__td--first">до 20:00</td>
                                            <td class="b-tab-shipping__td b-tab-shipping__td--second">на следующий
                                                день
                                            </td>
                                        </tr>
                                        <tr class="b-tab-shipping__tr">
                                            <td class="b-tab-shipping__td b-tab-shipping__td--first">после 20:00</td>
                                            <td class="b-tab-shipping__td b-tab-shipping__td--second">по
                                                договоренности
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="b-tab-content__container js-tab-content" data-tab-content="availability">
                            <h2 class="b-title b-title--advice b-title--stock">Наличие в магазинах</h2>
                            <div class="b-availability"><a class="b-link b-link--show-map js-product-map"
                                                           href="javascript:void(0);" title=""><span
                                            class="b-icon b-icon--map"><?= new SvgDecorator('icon-map', 22, 20)?></span></a>
                                <ul
                                        class="b-availability-tab-list">
                                    <li class="b-availability-tab-list__item active"><a
                                                class="b-availability-tab-list__link js-product-list"
                                                href="javascript:void(0)" aria-controls="shipping-list" title="Списком">Списком</a>
                                    </li>
                                    <li class="b-availability-tab-list__item"><a
                                                class="b-availability-tab-list__link js-product-map"
                                                href="javascript:void(0)" aria-controls="on-map" title="На карте">На
                                            карте</a>
                                    </li>
                                </ul>
                                <div class="b-availability__content js-availability-content">
                                    <div class="b-tab-delivery js-content-list js-map-list-scroll">
                                        <div class="b-tab-delivery__header">
                                            <ul class="b-tab-delivery__header-list">
                                                <li class="b-tab-delivery__header-item b-tab-delivery__header-item--addr">
                                                    Адрес
                                                </li>
                                                <li class="b-tab-delivery__header-item b-tab-delivery__header-item--phone">
                                                    Телефон
                                                </li>
                                                <li class="b-tab-delivery__header-item b-tab-delivery__header-item--time">
                                                    Время работы
                                                </li>
                                                <li class="b-tab-delivery__header-item b-tab-delivery__header-item--amount">
                                                    Товара
                                                </li>
                                                <li class="b-tab-delivery__header-item b-tab-delivery__header-item--self-picked">
                                                    Самовывоз
                                                </li>
                                            </ul>
                                        </div>
                                        <ul class="b-delivery-list js-delivery-list"></ul>
                                    </div>
                                    <div class="b-tab-delivery-map js-content-map">
                                        <div class="b-tab-delivery-map__map" id="map"></div>
                                        <a class="b-link b-link--close-baloon js-product-list"
                                           href="javascript:void(0);"
                                           title=""><span class="b-icon b-icon--close-baloon"><?= new SvgDecorator('icon-close-baloon', 18, 18)?></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                                        alt="" title="" role="presentation"/></span><span
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
                                                title=""/></span>
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
                                                            data-image="/static/build/images/content/royal-canin-2.jpg">4 кг</a>
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
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 16, 16)?></span></span><span
                                                        class="b-common-item__price js-price-block">100</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/hills-cat.jpg" alt="Хиллс" title=""/></span>
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
                                                            data-image="/static/build/images/content/hills-cat.jpg">3,5 кг</a>
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
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 16, 16)?></span></span><span
                                                        class="b-common-item__price js-price-block">2 585</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
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
                                                            data-image="/static/build/images/content/clean-cat.jpg">5 л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10 л</a>
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
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 16, 16)?></span></span><span
                                                        class="b-common-item__price js-price-block">353</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
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
                                                            data-image="/static/build/images/content/clean-cat.jpg">5 л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10 л</a>
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
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 16, 16)?></span></span><span
                                                        class="b-common-item__price js-price-block">353</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/hills-cat.jpg" alt="Хиллс" title=""/></span>
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
                                                            data-image="/static/build/images/content/hills-cat.jpg">3,5 кг</a>
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
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 16, 16)?></span></span><span
                                                        class="b-common-item__price js-price-block">2 585</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
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
                                                            data-image="/static/build/images/content/clean-cat.jpg">5 л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10 л</a>
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
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 16, 16)?></span></span><span
                                                        class="b-common-item__price js-price-block">353</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
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
                                                            data-image="/static/build/images/content/clean-cat.jpg">5 л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10 л</a>
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
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 16, 16)?></span></span><span
                                                        class="b-common-item__price js-price-block">353</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/hills-cat.jpg" alt="Хиллс" title=""/></span>
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
                                                            data-image="/static/build/images/content/hills-cat.jpg">3,5 кг</a>
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
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 16, 16)?></span></span><span
                                                        class="b-common-item__price js-price-block">2 585</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
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
                                                            data-image="/static/build/images/content/clean-cat.jpg">5 л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10 л</a>
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
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 16, 16)?></span></span><span
                                                        class="b-common-item__price js-price-block">353</span> <span
                                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                                    </div>
                                </div>
                                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                            class="b-common-item__image-wrap"><img
                                                class="b-common-item__image js-weight-img"
                                                src="/static/build/images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
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
                                                            data-image="/static/build/images/content/clean-cat.jpg">5 л</a>
                                                </li>
                                                <li class="b-weight-container__item"><a
                                                            class="b-weight-container__link js-price"
                                                            href="javascript:void(0);" data-price="915"
                                                            data-image="/static/build/images/content/pro-plan.jpg">10 л</a>
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
                                                            class="b-icon b-icon--cart"><?= new SvgDecorator('icon-cart', 16, 16)?></span></span><span
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

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
