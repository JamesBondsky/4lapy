<?php
/**
 * @var CBitrixComponentTemplate      $this
 * @var CMain                         $APPLICATION
 * @var array                         $arParams
 * @var array                         $arResult
 * @var CatalogElementDetailComponent $component
 * @var Product                       $product
 * @var Offer                         $offer
 */

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Templates\ViewsEnum;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Components\CatalogElementDetailComponent;
use FourPaws\Decorators\SvgDecorator;

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


/**
 * Характеристики
 */
$article = $offer->getXmlId();

$product->getWeightCapacityPacking();

try {
    $createCountry = $product->getCountry();
} catch (ApplicationCreateException $e) {
    $createCountry = null;
}

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_TITLE_VIEW);
?>
    <h2 class="b-title b-title--h2 b-title--card"><?= $brand->getName() ?></h2>
    <h1 class="b-title b-title--h1 b-title--card"><?= $product->getName() ?></h1>
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_SLIDER_VIEW);
?>
    <div class="b-product-card__slider">
        <div class="b-product-slider">
            <div class="b-product-slider__list b-product-slider__list--main js-product-slider-for" id="gallery1">
                <?php
                foreach ($product->getOffers() as $offer) {
                    if (!$offer->getImagesIds()) {
                        continue;
                    }
                    $images = $offer->getResizeImages(240, 240);
                    foreach ($images as $id => $image) {
                        /**
                         * @var ResizeImageDecorator $image
                         */
                        ?>
                        <div class="b-product-slider__item b-product-slider__item--big">
                            <div class="b-product-slider__wrapper b-product-slider__wrapper--big">
                                <img class="b-product-slider__photo-img b-product-slider__photo-img--big js-image-wrapper js-zoom"
                                     src="<?= $image ?>"
                                     alt="<?= $offer->getName() . ($id ? ' ' . $id : '') ?>"
                                     title="<?= $offer->getName() . ($id ? ' ' . $id : '') ?>"
                                     role="presentation"/>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>


            <div class="b-product-slider__list b-product-slider__list--nav js-product-slider-nav">
                <?php
                foreach ($product->getOffers() as $offer) {
                    if (!$offer->getImagesIds()) {
                        continue;
                    }
                    $images = $offer->getResizeImages(40, 40);
                    foreach ($images as $id => $image) {
                        /**
                         * @var ResizeImageDecorator $image
                         */
                        ?>
                        <div class="b-product-slider__item b-product-slider__item--small">
                            <div class="b-product-slider__wrapper b-product-slider__wrapper--small">
                                <img class="b-product-slider__photo-img b-product-slider__photo-img--small js-image-wrapper"
                                     src="<?= $image ?>"
                                     alt="Превью <?= $offer->getName() . ($id ? ' ' . $id : '') ?>"
                                     title="Превью <?= $offer->getName() . ($id ? ' ' . $id : '') ?>"
                                     role="presentation"/>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_OFFERS_VIEW);
?>
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
                                class="b-weight-container__line"><span
                                    class="b-weight-container__not">Нет в наличии</span></span></a>
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
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_CURRENT_OFFER_INFO);
?>
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
               data-count="6">Округлить до упаковки (6 шт.)<span>— скидка 3%</span></a>
            <a class="b-counter-basket__basket-link" href="javascript:void(0)" title="">
                <span class="b-counter-basket__basket-text">Добавить в корзину</span>
                <span class="b-icon b-icon--advice"><?= new SvgDecorator('icon-cart', 20, 20) ?></span>
            </a>
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
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_DESCRIPTION_TAB_HEADER);
?>
    <li class="b-tab-title__item js-tab-item active">
        <a class="b-tab-title__link js-tab-link"
           href="javascript:void(0);" title="Описание"
           data-tab="description"><span class="b-tab-title__text">Описание</span></a>
    </li>
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_DESCRIPTION_TAB);
?>
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

                        /*
                        ?>
                        <li class="b-characteristics-tab__item">
                            <div class="b-characteristics-tab__characteristics-text">
                                <span>Упаковано</span>
                                <div class="b-characteristics-tab__dots"></div>
                            </div>
                            <div class="b-characteristics-tab__characteristics-value">
                                Пакет / коробка
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
                        */

                        /*
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
                        <?php
                        */
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php
$this->EndViewTarget();
