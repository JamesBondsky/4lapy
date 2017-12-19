<?php
/**
 * @var CMain                         $APPLICATION
 * @var array                         $arParams
 * @var array                         $arResult
 * @var CatalogElementDetailComponent $component
 * @var Product                       $product
 * @var Offer                         $offer
 */

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Templates\ViewsEnum;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Components\CatalogElementDetailComponent;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;


/**
 * @todo Разворачивать всю цепочку нужных элементов в result_modifier
 */

$product = $arResult['PRODUCT'];
$brand = $product->getBrand();
$offer = $product->getOffers()->first();
$product = $offer->getProduct();


dump($offer);
die();

/**
 * Характеристики
 */
$article = $offer->getXmlId();
try {
    $createCountry = $product->getCountry();
} catch (ApplicationCreateException $e) {
    $createCountry = null;
}

?>
<div class="b-product-card">
    <div class="b-container">
        <?php
        $APPLICATION->IncludeComponent('fourpaws:catalog.breadcrumbs', 'product', []);
        ?>
        <div class="b-product-card__top">
            <div class="b-product-card__title-product">
                <h2 class="b-title b-title--h2 b-title--card"><?= $brand->getName() ?></h2>
                <h1 class="b-title b-title--h1 b-title--card"><?= $product->getName() ?></h1>
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
                <div class="b-product-card__slider">
                    <div class="b-product-slider">
                        <div class="b-product-slider__list b-product-slider__list--main js-product-slider-for">
                            <div class="b-product-slider__item b-product-slider__item--big">
                                <div class="b-product-slider__wrapper b-product-slider__wrapper--big">
                                    <img class="b-product-slider__photo-img b-product-slider__photo-img--big js-image-wrapper"
                                         src="images/content/pro-plan.jpg" alt="" title="" role="presentation"/>
                                </div>
                            </div>
                            <div class="b-product-slider__item b-product-slider__item--big">
                                <div class="b-product-slider__wrapper b-product-slider__wrapper--big">
                                    <img class="b-product-slider__photo-img b-product-slider__photo-img--big js-image-wrapper"
                                         src="images/content/clean-cat.jpg" alt="" title="" role="presentation"/>
                                </div>
                            </div>
                            <div class="b-product-slider__item b-product-slider__item--big">
                                <div class="b-product-slider__wrapper b-product-slider__wrapper--big">
                                    <img class="b-product-slider__photo-img b-product-slider__photo-img--big js-image-wrapper"
                                         src="images/content/brit.png" alt="" title="" role="presentation"/>
                                </div>
                            </div>
                            <div class="b-product-slider__item b-product-slider__item--big">
                                <div class="b-product-slider__wrapper b-product-slider__wrapper--big">
                                    <img class="b-product-slider__photo-img b-product-slider__photo-img--big js-image-wrapper"
                                         src="images/content/royal-canin-2.jpg" alt="" title="" role="presentation"/>
                                </div>
                            </div>
                            <div class="b-product-slider__item b-product-slider__item--big">
                                <div class="b-product-slider__wrapper b-product-slider__wrapper--big">
                                    <img class="b-product-slider__photo-img b-product-slider__photo-img--big js-image-wrapper"
                                         src="images/content/abba.png" alt="" title="" role="presentation"/>
                                </div>
                            </div>
                        </div>
                        <div class="b-product-slider__list b-product-slider__list--nav js-product-slider-nav">
                            <div class="b-product-slider__item b-product-slider__item--small">
                                <div class="b-product-slider__wrapper b-product-slider__wrapper--small">
                                    <img class="b-product-slider__photo-img b-product-slider__photo-img--small js-image-wrapper"
                                         src="images/content/pro-plan.jpg" alt="" title="" role="presentation"/>
                                </div>
                            </div>
                            <div class="b-product-slider__item b-product-slider__item--small">
                                <div class="b-product-slider__wrapper b-product-slider__wrapper--small">
                                    <img class="b-product-slider__photo-img b-product-slider__photo-img--small js-image-wrapper"
                                         src="images/content/clean-cat.jpg" alt="" title="" role="presentation"/>
                                </div>
                            </div>
                            <div class="b-product-slider__item b-product-slider__item--small">
                                <div class="b-product-slider__wrapper b-product-slider__wrapper--small">
                                    <img class="b-product-slider__photo-img b-product-slider__photo-img--small js-image-wrapper"
                                         src="images/content/brit.png" alt="" title="" role="presentation"/>
                                </div>
                            </div>
                            <div class="b-product-slider__item b-product-slider__item--small">
                                <div class="b-product-slider__wrapper b-product-slider__wrapper--small">
                                    <img class="b-product-slider__photo-img b-product-slider__photo-img--small js-image-wrapper"
                                         src="images/content/royal-canin-2.jpg" alt="" title="" role="presentation"/>
                                </div>
                            </div>
                            <div class="b-product-slider__item b-product-slider__item--small">
                                <div class="b-product-slider__wrapper b-product-slider__wrapper--small">
                                    <img class="b-product-slider__photo-img b-product-slider__photo-img--small js-image-wrapper"
                                         src="images/content/abba.png" alt="" title="" role="presentation"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="b-product-card__info-product js-weight-here">
                    <div class="b-product-card__option-product js-weight-default">
                        <div class="b-product-card__weight">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--product">
                            <ul class="b-weight-container__list b-weight-container__list--product">
                                <li class="b-weight-container__item b-weight-container__item--product"><a
                                            class="b-weight-container__link b-weight-container__link--product js-price-product active-link"
                                            href="javascript:void(0);" data-weight="5 л" data-price="353"
                                            data-image="1"><span class="b-weight-container__line"><span
                                                    class="b-weight-container__weight">5 л</span><span
                                                    class="b-weight-container__price b-undefined">353 <span
                                                        class="b-ruble b-ruble--weight">₽</span></span></span><span
                                                class="b-weight-container__line"><span
                                                    class="b-weight-container__action">Акция</span></span></a>
                                </li>
                                <li class="b-weight-container__item b-weight-container__item--product"><a
                                            class="b-weight-container__link b-weight-container__link--product js-price-product"
                                            href="javascript:void(0);" data-weight="10 л" data-price="915"
                                            data-image="2"><span class="b-weight-container__line"><span
                                                    class="b-weight-container__weight">10 л</span><span
                                                    class="b-weight-container__price b-undefined">915 <span
                                                        class="b-ruble b-ruble--weight">₽</span></span></span><span
                                                class="b-weight-container__line"><span
                                                    class="b-weight-container__action">Акция</span></span></a>
                                </li>
                                <li class="b-weight-container__item b-weight-container__item--product"><a
                                            class="b-weight-container__link b-weight-container__link--product js-price-product"
                                            href="javascript:void(0);" data-weight="3 кг" data-price="115 000"
                                            data-image="3"><span class="b-weight-container__line"><span
                                                    class="b-weight-container__weight">3 кг</span><span
                                                    class="b-weight-container__price b-weight-container__price--big">115 000 <span
                                                        class="b-ruble b-ruble--weight">₽</span><span
                                                        class="b-ruble b-ruble--weight-big"></span></span></span> <span
                                                class="b-weight-container__line"><span
                                                    class="b-weight-container__action">Акция</span><span
                                                    class="b-weight-container__old-price b-weight-container__old-price--big">130 000 <span
                                                        class="b-ruble b-ruble--old-weight-price">₽</span></span></span></a>
                                </li>
                                <li class="b-weight-container__item b-weight-container__item--product"><a
                                            class="b-weight-container__link b-weight-container__link--product js-price-product unavailable-link"
                                            href="javascript:void(0);" data-weight="8 кг" data-price="585"
                                            data-image="4"><span class="b-weight-container__line"><span
                                                    class="b-weight-container__weight">8 кг</span><span
                                                    class="b-weight-container__price b-undefined">585 <span
                                                        class="b-ruble b-ruble--weight">₽</span></span></span><span
                                                class="b-weight-container__line"><span class="b-weight-container__not">Нет в наличии</span></span></a>
                                </li>
                                <li class="b-weight-container__item b-weight-container__item--product"><a
                                            class="b-weight-container__link b-weight-container__link--product js-price-product"
                                            href="javascript:void(0);" data-weight="1 л" data-price="915"
                                            data-image="5"><span class="b-weight-container__line"><span
                                                    class="b-weight-container__weight">1 л</span><span
                                                    class="b-weight-container__price b-undefined">915 <span
                                                        class="b-ruble b-ruble--weight">₽</span></span></span><span
                                                class="b-weight-container__line"><span
                                                    class="b-weight-container__action">Акция</span><span
                                                    class="b-weight-container__cart"><span
                                                        class="b-cart b-cart--cart-product"><span
                                                            class="b-icon b-icon--cart-product"><svg class="b-icon__svg"
                                                                                                     viewBox="0 0 16 16 "
                                                                                                     width="16px"
                                                                                                     height="16px"><use
                                                                    class="b-icon__use"
                                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                                        class="b-weight-container__number">2</span></span></span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-product-card__info">
                        <div class="b-product-information">
                            <ul class="b-product-information__list">
                                <li class="b-product-information__item">
                                    <div class="b-product-information__title-info js-info-product">Вес</div>
                                    <div class="b-product-information__value">6кг</div>
                                </li>
                                <li class="b-product-information__item">
                                    <div class="b-product-information__title-info">Цена</div>
                                    <div class="b-product-information__value b-product-information__value--price"><span
                                                class="b-product-information__price js-price-product">3 719</span><span
                                                class="b-ruble b-ruble--product-information">&nbsp₽</span><span
                                                class="b-product-information__bonus">+112 бонусов</span>
                                    </div>
                                </li>
                                <li class="b-product-information__item">
                                    <div class="b-product-information__title-info">Наличие</div>
                                    <div class="b-product-information__value">Только под заказ</div>
                                </li>
                                <li class="b-product-information__item">
                                    <div class="b-product-information__title-info">Доставка</div>
                                    <div class="b-product-information__value">10 сентября ближайшая</div>
                                </li>
                                <li class="b-product-information__item">
                                    <div class="b-product-information__title-info">Вкус</div>
                                    <div class="b-product-information__value b-product-information__value--select">
                                        <div class="b-select b-select--product">
                                            <select class="b-select__block b-select__block--product js-select-link">
                                                <option value="../product-card.html">Ягненок/яблоки, 6 кг</option>
                                                <option value="../product-card-2-socks.html">Корова/арбуз, 6 кг</option>
                                                <option value="../product-card-3-milprazon.html">Варан/авокадо, 6 кг
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="b-counter-basket">
                            <div class="b-plus-minus b-plus-minus--half-mobile js-plus-minus-cont">
                                <a class="b-plus-minus__minus js-minus" href="javascript:void(0);"></a>
                                <input class="b-plus-minus__count js-plus-minus-count" value="1" type="text"/>
                                <a class="b-plus-minus__plus js-plus" href="javascript:void(0);"></a><span
                                        class="b-plus-minus__by-line">Количество</span>
                            </div>
                            <a class="b-counter-basket__add-set js-add-set" href="javascript:void(0)" title=""
                               data-count="6">Округлить до упаковки (6 шт.)<span>— скидка 3%</span></a> <a
                                    class="b-counter-basket__basket-link" href="javascript:void(0)"
                                    title=""><span class="b-counter-basket__basket-text">Добавить в корзину</span><span
                                        class="b-icon b-icon--advice"><svg class="b-icon__svg" viewBox="0 0 20 20 "
                                                                           width="20px" height="20px"><use
                                                class="b-icon__use" xlink:href="icons.svg#icon-cart"></use></svg></span></a>
                            <a
                                    class="b-link b-link--one-click" href="javascript:void(0)"
                                    title="Купить в 1 клик"><span class="b-link__text b-link__text--one-click">Купить в 1 клик</span>
                            </a>
                            <hr class="b-counter-basket__hr"/>
                            <p class="b-counter-basket__text b-counter-basket__text--red">Акция. 4+1 подарок при
                                покупке</p>
                            <p class="b-counter-basket__text">При покупке четырех кормов, пятый вы получите
                                бесплатно</p>
                            <p class="b-counter-basket__text">5 июня — 25 августа 2017</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="b-advice">
                <h2 class="b-title b-title--advice">Часто берут вместе</h2>
                <div class="b-advice__list">
                    <div class="b-advice__list-items"><a class="b-advice__item" href="javascript:void(0)" title=""><span
                                    class="b-advice__image-wrapper"><img class="b-advice__image"
                                                                         src="../images/content/akana.png" alt=""
                                                                         title="" role="presentation"/></span><span
                                    class="b-advice__block"><span
                                        class="b-clipped-text b-clipped-text--advice"><span><strong>Акана</strong> корм для собак всех пород ягненок/яблоки</span></span><span
                                        class="b-advice__info"><span class="b-advice__weight">6 кг</span><span
                                            class="b-advice__cost">3 719 <span class="b-ruble b-ruble--advice">₽</span></span></span></span></a>
                        <div
                                class="b-advice__sign b-advice__sign--plus"></div>
                        <a class="b-advice__item" href="javascript:void(0)" title=""><span
                                    class="b-advice__image-wrapper"><img class="b-advice__image"
                                                                         src="../images/content/food-1.jpg" alt=""
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
                                                                         src="../images/content/fresh-step.png" alt=""
                                                                         title="" role="presentation"/></span><span
                                    class="b-advice__block"><span
                                        class="b-clipped-text b-clipped-text--advice"><span><strong>Роял Канин</strong> корм для поощрения при обучении и дресси…</span></span><span
                                        class="b-advice__info"><span class="b-advice__weight">50 г</span><span
                                            class="b-advice__cost">57 <span
                                                class="b-ruble b-ruble--advice">₽</span></span></span></span></a>
                    </div>
                    <div class="b-advice__list-cost">
                        <div class="b-advice__sign b-advice__sign--equally"></div>
                        <div class="b-advice__cost-wrapper"><span class="b-advice__total-price">4 015 <span
                                        class="b-ruble b-ruble--total">₽</span></span><a class="b-advice__basket-link"
                                                                                         href="javascript:void(0)"
                                                                                         title=""><span
                                        class="b-advice__basket-text">В корзину</span><span
                                        class="b-icon b-icon--advice"><svg class="b-icon__svg" viewBox="0 0 20 20 "
                                                                           width="20px" height="20px"><use
                                                class="b-icon__use" xlink:href="icons.svg#icon-cart"></use></svg></span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-product-card__tab">
            <div class="b-tab">
                <div class="b-tab-title">
                    <ul class="b-tab-title__list">
                        <li class="b-tab-title__item active">
                            <a class="b-tab-title__link js-tab-link"
                               href="javascript:void(0);" title="Описание"
                               data-tab="description"><span class="b-tab-title__text">Описание</span></a>
                        </li>
                        <?php
                        /**
                         * @todo Состава пока нет
                         */
                        //<li class="b-tab-title__item">
                        //    <a class="b-tab-title__link js-tab-link"
                        //    href="javascript:void(0);" title="Состав"
                        //    data-tab="composition"><span
                        //    class="b-tab-title__text">Состав</span></a>
                        //</li>
                        /**
                         * @todo Рекоммендация по питанию пока нет
                         */
                        /*<li class="b-tab-title__item">
                            <a class="b-tab-title__link js-tab-link"
                               href="javascript:void(0);" title="Рекомендации по питанию"
                               data-tab="recommendations"><span class="b-tab-title__text">Рекомендации по питанию</span></a>
                        </li>*/

                        $APPLICATION->ShowViewContent(ViewsEnum::PRODUCT_RATING_TAB_HEADER_VIEW);
                        ?>
                        <li class="b-tab-title__item">
                            <a class="b-tab-title__link js-tab-link"
                               href="javascript:void(0);" title="Отзывы"
                               data-tab="reviews"><span class="b-tab-title__text">Отзывы<span
                                            class="b-tab-title__number">(12)</span></span></a>
                        </li>
                        <li class="b-tab-title__item">
                            <a class="b-tab-title__link js-tab-link"
                               href="javascript:void(0);" title="Доставка и оплата"
                               data-tab="data"><span class="b-tab-title__text">Доставка и оплата</span></a>
                        </li>
                        <li class="b-tab-title__item">
                            <a class="b-tab-title__link js-tab-link"
                               href="javascript:void(0);" title="Наличие в магазинах"
                               data-tab="availability"><span class="b-tab-title__text">Наличие в магазинах<span
                                            class="b-tab-title__number">(21)</span></span></a>
                        </li>
                        <li class="b-tab-title__item">
                            <a class="b-tab-title__link js-tab-link"
                               href="javascript:void(0);" title="Акция"
                               data-tab="shares"><span class="b-tab-title__text">Акция</span></a>
                        </li>
                    </ul>
                </div>
                <div class="b-tab-content">
                    <div class="b-tab-content__container active js-tab-content" data-tab-content="description">
                        <div class="b-description-tab">
                            <div class="b-description-tab__column">
                                <div class="b-description-tab__title">Описание</div>
                                <div class="b-description-tab__text">
                                    <p>
                                        <?= $product->getDetailText()->getText() ?>
                                    </p>
                                </div>
                            </div>
                            <div class="b-description-tab__column b-description-tab__column--characteristics">
                                <div class="b-description-tab__title">Подробные характеристики</div>
                                <div class="b-characteristics-tab">
                                    <ul class="b-characteristics-tab__list">
                                        <?php
                                        if ($article) {
                                            ?>
                                            <li class="b-characteristics-tab__item">
                                                <div class="b-characteristics-tab__characteristics-text">
                                                    <span>Артикул</span>
                                                    <div class="b-characteristics-tab__dots"></div>
                                                </div>
                                                <div class="b-characteristics-tab__characteristics-value"><?= $article ?></div>
                                            </li>
                                            <?php
                                        }
                                        /**
                                         * @todo Направленность
                                         */
                                        ?>
                                        <!--                                        <li class="b-characteristics-tab__item">
                                                                                    <div class="b-characteristics-tab__characteristics-text"><span>Направленность</span>
                                                                                        <div class="b-characteristics-tab__dots"></div>
                                                                                    </div>
                                                                                    <div class="b-characteristics-tab__characteristics-value">Повседневный корм
                                                                                        для собак от 7 лет
                                                                                    </div>
                                                                                </li>-->
                                        <?php
                                        if ($createCountry) {
                                            ?>
                                            <li class="b-characteristics-tab__item">
                                                <div class="b-characteristics-tab__characteristics-text">
                                                    <span>Страна производства</span>
                                                    <div class="b-characteristics-tab__dots"></div>
                                                </div>
                                                <div class="b-characteristics-tab__characteristics-value"><?= $createCountry->getName() ?></div>
                                            </li>
                                            <?php
                                        }
                                        ?>
                                        <li class="b-characteristics-tab__item">
                                            <div class="b-characteristics-tab__characteristics-text">
                                                <span>Упаковано</span>
                                                <div class="b-characteristics-tab__dots"></div>
                                            </div>
                                            <div class="b-characteristics-tab__characteristics-value">Пакет / коробка
                                            </div>
                                        </li>
                                        <li class="b-characteristics-tab__item">
                                            <div class="b-characteristics-tab__characteristics-text">
                                                <span>В упаковке</span>
                                                <div class="b-characteristics-tab__dots"></div>
                                            </div>
                                            <div class="b-characteristics-tab__characteristics-value">6 шт.</div>
                                        </li>
                                        <?php


                                        ?>
                                        <li class="b-characteristics-tab__item">
                                            <div class="b-characteristics-tab__characteristics-text"><span>Вес</span>
                                                <div class="b-characteristics-tab__dots"></div>
                                            </div>
                                            <div class="b-characteristics-tab__characteristics-value">2 кг</div>
                                        </li>
                                        <li class="b-characteristics-tab__item">
                                            <div class="b-characteristics-tab__characteristics-text"><span>Длина</span>
                                                <div class="b-characteristics-tab__dots"></div>
                                            </div>
                                            <div class="b-characteristics-tab__characteristics-value">9 см</div>
                                        </li>
                                        <li class="b-characteristics-tab__item">
                                            <div class="b-characteristics-tab__characteristics-text"><span>Ширина</span>
                                                <div class="b-characteristics-tab__dots"></div>
                                            </div>
                                            <div class="b-characteristics-tab__characteristics-value">23 см</div>
                                        </li>
                                        <li class="b-characteristics-tab__item">
                                            <div class="b-characteristics-tab__characteristics-text"><span>Высота</span>
                                                <div class="b-characteristics-tab__dots"></div>
                                            </div>
                                            <div class="b-characteristics-tab__characteristics-value">35 см</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
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
                                        <td class="b-tab-shipping__td b-tab-shipping__td--second">на следующий день</td>
                                    </tr>
                                    <tr class="b-tab-shipping__tr">
                                        <td class="b-tab-shipping__td b-tab-shipping__td--first">после 20:00</td>
                                        <td class="b-tab-shipping__td b-tab-shipping__td--second">по договоренности</td>
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
                                        class="b-icon b-icon--map"><svg class="b-icon__svg" viewBox="0 0 22 20 "
                                                                        width="22px" height="20px"><use
                                                class="b-icon__use" xlink:href="icons.svg#icon-map"></use></svg></span></a>
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
                                    <a class="b-link b-link--close-baloon js-product-list" href="javascript:void(0);"
                                       title=""><span class="b-icon b-icon--close-baloon"><svg class="b-icon__svg"
                                                                                               viewBox="0 0 18 18 "
                                                                                               width="18px"
                                                                                               height="18px"><use
                                                        class="b-icon__use"
                                                        xlink:href="icons.svg#icon-close-baloon"></use></svg></span></a>
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
                                <div class="b-advice b-advice--stock"><a class="b-advice__item b-advice__item--stock"
                                                                         href="javascript:void(0)" title=""><span
                                                class="b-advice__image-wrapper b-advice__image-wrapper--stock"><img
                                                    class="b-advice__image" src="../images/content/fresh-step.png"
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
                                            src="images/content/royal-canin-2.jpg" alt="Роял Канин" title=""/></span>
                                <div class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
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
                                                        data-image="images/content/royal-canin-2.jpg">4 кг</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price"
                                                        href="javascript:void(0);" data-price="4 199"
                                                        data-image="images/content/abba.png">6 кг</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price unavailable-link"
                                                        href="javascript:void(0);" data-price="4 199"
                                                        data-image="images/content/abba.png">15 кг</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                        class="b-icon b-icon--cart"><svg class="b-icon__svg"
                                                                                         viewBox="0 0 16 16 "
                                                                                         width="16px" height="16px"><use
                                                                class="b-icon__use"
                                                                xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                                    class="b-common-item__price js-price-block">100</span> <span
                                                    class="b-common-item__currency"><span
                                                        class="b-ruble">Р</span></span></span></a>
                                </div>
                            </div>
                            <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                        class="b-common-item__image-wrap"><img
                                            class="b-common-item__image js-weight-img"
                                            src="images/content/hills-cat.jpg" alt="Хиллс" title=""/></span>
                                <div class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
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
                                                        data-image="images/content/hills-cat.jpg">3,5 кг</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">8 кг</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price unavailable-link"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">12 кг</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                        class="b-icon b-icon--cart"><svg class="b-icon__svg"
                                                                                         viewBox="0 0 16 16 "
                                                                                         width="16px" height="16px"><use
                                                                class="b-icon__use"
                                                                xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                                    class="b-common-item__price js-price-block">2 585</span> <span
                                                    class="b-common-item__currency"><span
                                                        class="b-ruble">Р</span></span></span></a>
                                </div>
                            </div>
                            <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                        class="b-common-item__image-wrap"><img
                                            class="b-common-item__image js-weight-img"
                                            src="images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
                                <div class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
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
                                                        data-image="images/content/clean-cat.jpg">5 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price"
                                                        href="javascript:void(0);" data-price="915"
                                                        data-image="images/content/pro-plan.jpg">10 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price unavailable-link"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">18 кг</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                        class="b-icon b-icon--cart"><svg class="b-icon__svg"
                                                                                         viewBox="0 0 16 16 "
                                                                                         width="16px" height="16px"><use
                                                                class="b-icon__use"
                                                                xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                                    class="b-common-item__price js-price-block">353</span> <span
                                                    class="b-common-item__currency"><span
                                                        class="b-ruble">Р</span></span></span></a>
                                </div>
                            </div>
                            <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                        class="b-common-item__image-wrap"><img
                                            class="b-common-item__image js-weight-img"
                                            src="images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
                                <div class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
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
                                                        data-image="images/content/clean-cat.jpg">5 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price"
                                                        href="javascript:void(0);" data-price="915"
                                                        data-image="images/content/pro-plan.jpg">10 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price unavailable-link"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">18 кг</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                        class="b-icon b-icon--cart"><svg class="b-icon__svg"
                                                                                         viewBox="0 0 16 16 "
                                                                                         width="16px" height="16px"><use
                                                                class="b-icon__use"
                                                                xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                                    class="b-common-item__price js-price-block">353</span> <span
                                                    class="b-common-item__currency"><span
                                                        class="b-ruble">Р</span></span></span></a>
                                </div>
                            </div>
                            <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                        class="b-common-item__image-wrap"><img
                                            class="b-common-item__image js-weight-img"
                                            src="images/content/hills-cat.jpg" alt="Хиллс" title=""/></span>
                                <div class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
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
                                                        data-image="images/content/hills-cat.jpg">3,5 кг</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">8 кг</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price unavailable-link"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">12 кг</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                        class="b-icon b-icon--cart"><svg class="b-icon__svg"
                                                                                         viewBox="0 0 16 16 "
                                                                                         width="16px" height="16px"><use
                                                                class="b-icon__use"
                                                                xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                                    class="b-common-item__price js-price-block">2 585</span> <span
                                                    class="b-common-item__currency"><span
                                                        class="b-ruble">Р</span></span></span></a>
                                </div>
                            </div>
                            <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                        class="b-common-item__image-wrap"><img
                                            class="b-common-item__image js-weight-img"
                                            src="images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
                                <div class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
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
                                                        data-image="images/content/clean-cat.jpg">5 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price"
                                                        href="javascript:void(0);" data-price="915"
                                                        data-image="images/content/pro-plan.jpg">10 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price unavailable-link"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">18 кг</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                        class="b-icon b-icon--cart"><svg class="b-icon__svg"
                                                                                         viewBox="0 0 16 16 "
                                                                                         width="16px" height="16px"><use
                                                                class="b-icon__use"
                                                                xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                                    class="b-common-item__price js-price-block">353</span> <span
                                                    class="b-common-item__currency"><span
                                                        class="b-ruble">Р</span></span></span></a>
                                </div>
                            </div>
                            <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                        class="b-common-item__image-wrap"><img
                                            class="b-common-item__image js-weight-img"
                                            src="images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
                                <div class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
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
                                                        data-image="images/content/clean-cat.jpg">5 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price"
                                                        href="javascript:void(0);" data-price="915"
                                                        data-image="images/content/pro-plan.jpg">10 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price unavailable-link"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">18 кг</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                        class="b-icon b-icon--cart"><svg class="b-icon__svg"
                                                                                         viewBox="0 0 16 16 "
                                                                                         width="16px" height="16px"><use
                                                                class="b-icon__use"
                                                                xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                                    class="b-common-item__price js-price-block">353</span> <span
                                                    class="b-common-item__currency"><span
                                                        class="b-ruble">Р</span></span></span></a>
                                </div>
                            </div>
                            <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                        class="b-common-item__image-wrap"><img
                                            class="b-common-item__image js-weight-img"
                                            src="images/content/hills-cat.jpg" alt="Хиллс" title=""/></span>
                                <div class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
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
                                                        data-image="images/content/hills-cat.jpg">3,5 кг</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">8 кг</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price unavailable-link"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">12 кг</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                        class="b-icon b-icon--cart"><svg class="b-icon__svg"
                                                                                         viewBox="0 0 16 16 "
                                                                                         width="16px" height="16px"><use
                                                                class="b-icon__use"
                                                                xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                                    class="b-common-item__price js-price-block">2 585</span> <span
                                                    class="b-common-item__currency"><span
                                                        class="b-ruble">Р</span></span></span></a>
                                </div>
                            </div>
                            <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                        class="b-common-item__image-wrap"><img
                                            class="b-common-item__image js-weight-img"
                                            src="images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
                                <div class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
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
                                                        data-image="images/content/clean-cat.jpg">5 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price"
                                                        href="javascript:void(0);" data-price="915"
                                                        data-image="images/content/pro-plan.jpg">10 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price unavailable-link"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">18 кг</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                        class="b-icon b-icon--cart"><svg class="b-icon__svg"
                                                                                         viewBox="0 0 16 16 "
                                                                                         width="16px" height="16px"><use
                                                                class="b-icon__use"
                                                                xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                                    class="b-common-item__price js-price-block">353</span> <span
                                                    class="b-common-item__currency"><span
                                                        class="b-ruble">Р</span></span></span></a>
                                </div>
                            </div>
                            <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                                        class="b-common-item__image-wrap"><img
                                            class="b-common-item__image js-weight-img"
                                            src="images/content/clean-cat.jpg" alt="CleanCat" title=""/></span>
                                <div class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
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
                                                        data-image="images/content/clean-cat.jpg">5 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price"
                                                        href="javascript:void(0);" data-price="915"
                                                        data-image="images/content/pro-plan.jpg">10 л</a>
                                            </li>
                                            <li class="b-weight-container__item"><a
                                                        class="b-weight-container__link js-price unavailable-link"
                                                        href="javascript:void(0);" data-price="2 585"
                                                        data-image="images/content/brit.png">18 кг</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                                class="b-common-item__wrapper-link"><span class="b-cart"><span
                                                        class="b-icon b-icon--cart"><svg class="b-icon__svg"
                                                                                         viewBox="0 0 16 16 "
                                                                                         width="16px" height="16px"><use
                                                                class="b-icon__use"
                                                                xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                                    class="b-common-item__price js-price-block">353</span> <span
                                                    class="b-common-item__currency"><span
                                                        class="b-ruble">Р</span></span></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
