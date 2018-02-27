<?php
/**
 * @var CBitrixComponentTemplate      $this
 * @var CMain                         $APPLICATION
 * @var array                         $arParams
 * @var array                         $arResult
 * @var CatalogElementDetailComponent $component
 * @var Product                       $product
 * @var Offer                         $currentOffer
 */

use FourPaws\App\Application;
use FourPaws\App\Templates\ViewsEnum;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Components\CatalogElementDetailComponent;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;
use FourPaws\Location\LocationService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

/** @var LocationService $locationService */
$locationService = Application::getInstance()->getContainer()->get('location.service');

/**
 * @todo Разворачивать всю цепочку нужных элементов в result_modifier
 */

$product      = $arResult['PRODUCT'];
$offers       = $product->getOffers();
$brand        = $product->getBrand();
$currentOffer = $arResult['CURRENT_OFFER'];

$mainCombinationType = '';
if ($currentOffer->getClothingSize()) {
    $mainCombinationType = 'SIZE';
} else {
    $mainCombinationType = 'VOLUME';
}

$this->setFrameMode(true);

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_TITLE_VIEW);
?>
    <a href="<?= $brand->getDetailPageUrl() ?>"
       class="b-title b-title--h2 b-title--inline b-title--card"
       title="<?= $brand->getName() ?>"><?= $brand->getName() ?></a>
    <h1 class="b-title b-title--h1 b-title--card"><?= $product->getName() ?></h1>
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_SLIDER_VIEW);
?>
    <div class="b-product-card__slider">
        <div class="b-product-slider">
            <div class="b-product-slider__list b-product-slider__list--main js-product-slider-for">
                <?php
                $mainImageIndex = [];
                $iterator       = 0;
                /** @var Offer $offer */
                foreach ($product->getOffers() as $offer) {
                    if (!$offer->getImagesIds()) {
                        continue;
                    }
    
                    $images         = $offer->getResizeImages(480, 480);
                    $originalImages = $offer->getImages();

                    foreach ($images as $id => $image) {
                        /**
                         * @var ResizeImageDecorator $image
                         */
                        if ($id === 0) {
                            $mainImageIndex[$offer->getId()] = $iterator;
                        } ?>
                        <div class="b-product-slider__item b-product-slider__item--big">
                            <div class="b-product-slider__wrapper b-product-slider__wrapper--big">
                                <a class="b-product-slider__link js-fancybox"
                                   data-fancybox="b-product-slider--images"
                                   href="<?= $image ?>"
                                   data-toolbar="false"
                                   data-small-btn="true">
                                    <img class="b-product-slider__photo-img b-product-slider__photo-img--big js-image-wrapper<?= $iterator ? '' : ' js-zoom' ?>"
                                         src="<?= $image ?>"
                                         alt="<?= $offer->getName() . ($id ? ' ' . $id : '') ?>"
                                         title="<?= $offer->getName() . ($id ? ' ' . $id : '') ?>"
                                         data-zoom-image="<?= $originalImages->get($id) ?>"
                                         role="presentation" />
                                </a>
                            </div>
                        </div>
                        <?php $iterator++;
                    }
                } ?>
            </div>
            <div class="b-product-slider__list b-product-slider__list--nav js-product-slider-nav">
                <?php
                /** @var Offer $offer */
                foreach ($product->getOffers() as $offer) {
                    if (!$offer->getImagesIds()) {
                        continue;
                    }
                    $images = $offer->getResizeImages(80, 80);
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
                                     role="presentation" />
                            </div>
                        </div>
                    <?php }
                } ?>
            </div>
        </div>
    </div>
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_OFFERS_VIEW);
?>
    <div class="b-product-card__option-product js-weight-default">
        <?php if ($mainCombinationType && ($offers->count() > 1)) { ?>
            <?php if ($mainCombinationType === 'SIZE') { ?>
                <div class="b-product-card__weight">Размеры</div>
            <?php } else { ?>
                <div class="b-product-card__weight">Варианты фасовки</div>
            <?php } ?>
            <div class="b-weight-container b-weight-container--product">
                <ul class="b-weight-container__list b-weight-container__list--product">
                    <?php
                    $isCurrentOffer = false;

                    foreach ($offers as $offer) {
                        $isCurrentOffer = !$isCurrentOffer && $currentOffer->getId() === $offer->getId();
                        
                        $value = null;
                        if ($mainCombinationType === 'SIZE') {
                            if ($offer->getClothingSize()) {
                                $value = $offer->getClothingSize()->getName();
                            }
                        } else {
                            if ($offer->getVolumeReference()) {
                                $value = $offer->getVolumeReference()->getName();
                            } else {
                                $value = WordHelper::showWeight($offer->getCatalogProduct()->getWeight());
                            }
                        }
    
                        if (!$value) {
                            continue;
                        }
                        ?>
                        <li class="b-weight-container__item b-weight-container__item--product<?= $isCurrentOffer ? ' active' : '' ?>">
                            <a class="b-weight-container__link b-weight-container__link--product js-price-product<?= $isCurrentOffer ? ' active-link' : '' ?>"
                               href="<?= $offer->getLink() ?>"
                               data-weight=" <?= $value ?>"
                               data-price="<?= $offer->getPrice() ?>"
                               data-image="<?= $mainImageIndex[$offer->getId()] ?>"
                               data-url="<?= $offer->getLink() ?>"
                               data-offerid="<?= $offer->getId() ?>">
                                <span class="b-weight-container__line">
                                    <span class="b-weight-container__weight"><?= $value ?></span>
                                    <span class="b-weight-container__price">
                                        <?= $offer->getPrice() ?> <span class="b-ruble b-ruble--weight">₽</span>
                                    </span>
                                </span>
                                <span class="b-weight-container__line">
                                    <?php /** @todo впилить акцию
                                     * <span class="b-weight-container__action">Акция</span>
                                     */ ?>
                                </span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
    </div>
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_CURRENT_OFFER_INFO);
?>
    
    <div class="b-product-card__info js-preloader-fix">
        <div class="b-product-information">
            <ul class="b-product-information__list">
                <?php if ($currentOffer->getClothingSize()) { ?>
                    <li class="b-product-information__item">
                        <div class="b-product-information__title-info js-info-product">Размер</div>
                        <div class="b-product-information__value"><?= $currentOffer->getClothingSize()
                                                                                   ->getName() ?></div>
                    </li>
                <?php } elseif ($currentOffer->getVolumeReference()) { ?>
                    <li class="b-product-information__item">
                        <div class="b-product-information__title-info js-info-product">Объем</div>
                        <div class="b-product-information__value"><?= $currentOffer->getVolumeReference()
                                                                                   ->getName() ?></div>
                    </li>
                <?php } elseif ($currentOffer->getCatalogProduct()->getWeight()) { ?>
                    <li class="b-product-information__item">
                        <div class="b-product-information__title-info js-info-product">Вес</div>
                        <div class="b-product-information__value">
                            <?= WordHelper::showWeight($currentOffer->getCatalogProduct()->getWeight()) ?>
                        </div>
                    </li>
                <?php } ?>
                <li class="b-product-information__item">
                    <div class="b-product-information__title-info">Цена</div>
                    <div class="b-product-information__value b-product-information__value--price">
                        <?php if ($currentOffer->getOldPrice() > $currentOffer->getPrice()) { ?>
                            <span class="b-product-information__old-price"><?= $currentOffer->getOldPrice() ?> </span>
                            <span class="b-ruble b-ruble--old-price">₽</span>
                        <?php } ?>
                        <span class="b-product-information__price js-price-product">
                            <?= $currentOffer->getPrice() ?>
                        </span>
                        <span class="b-ruble b-ruble--product-information">&nbsp;₽</span>
                        <?php if ($currentOffer->getBonuses()) { ?>
                            <span class="b-product-information__bonus">+<?= $currentOffer->getBonuses() ?>
                                <?= WordHelper::declension($currentOffer->getBonuses(),
                                                           [
                                                               'бонус',
                                                               'бонуса',
                                                               'бонусов',
                                                           ]) ?>
                            </span>
                        <?php } ?>
                    </div>
                </li>
                <?php if ($currentOffer->isByRequest()) {
                    /**
                     * @todo наличие по зоне
                     */
                    ?>
                    <li class="b-product-information__item">
                        <div class="b-product-information__title-info">Наличие</div>
                        <div class="b-product-information__value">Только под заказ</div>
                    </li>
                <?php } ?>
                <?php /* @todo сделать подгрузку инфо о доставках через ajax */ ?>
                <?php $APPLICATION->IncludeComponent('fourpaws:catalog.product.delivery.info',
                                                     'detail',
                                                     [
                                                         'OFFER'         => $currentOffer,
                                                         'LOCATION_CODE' => $locationService->getCurrentLocation(),
                                                     ],
                                                     false,
                                                     ['HIDE_ICONS' => 'Y']); ?>
                
                <?php /* todo вывод связанных товаров по цвету и вкусу  */ ?>
                <?php if ($currentOffer->getFlavourCombination()) { ?>
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
                <?php } ?>
                <?php if ($currentOffer->getColourCombination()) { ?>
                    <li class="b-product-information__item">
                        <div class="b-product-information__title-info">Цвет
                        </div>
                        <div class="b-product-information__value b-product-information__value--select">
                            <div class="b-select b-select--product">
                                <select class="b-select__block b-select__block--product js-select-link">
                                    <option value="../product-card.html">Черно-желтые, размер S</option>
                                    <option value="../product-card-2-socks.html">Бело-красные, размер S</option>
                                    <option value="../product-card-3-milprazon.html">Коричнево-голубые, размер S
                                    </option>
                                </select>
                            </div>
                        </div>
                    </li>
                <?php } ?>

            </ul>
        </div>
        <div class="b-counter-basket">
            <div class="b-plus-minus b-plus-minus--half-mobile js-plus-minus-cont">
                <a class="b-plus-minus__minus js-minus" href="javascript:void(0);"></a>
                <input class="b-plus-minus__count js-plus-minus-count" value="1" type="text" />
                <a class="b-plus-minus__plus js-plus" href="javascript:void(0);"></a>
                <span class="b-plus-minus__by-line">Количество</span>
            </div>
            <?php if ($currentOffer->getMultiplicity() && ($currentOffer->getMultiplicity() > 1)) { ?>
                <a class="b-counter-basket__add-set js-add-set" href="javascript:void(0)" title=""
                   data-count="<?= $currentOffer->getMultiplicity() ?>">
                    Округлить до упаковки (<?= $currentOffer->getMultiplicity() ?> шт.)
                    <span>— скидка 3%</span>
                </a>
            <?php } ?>
            <a class="b-counter-basket__basket-link js-basket-add js-this-product"
               href="javascript:void(0)"
               title=""
               data-offerId="<?= $currentOffer->getId(); ?>"
               data-url="/ajax/sale/basket/add/">
                <span class="b-counter-basket__basket-text">Добавить в корзину</span>
                <span class="b-icon b-icon--advice"><?= new SvgDecorator('icon-cart', 20, 20) ?></span>
            </a>
            <a class="b-link b-link--one-click js-open-popup js-open-popup--one-click" href="javascript:void(0)"
               title="Купить в 1 клик" data-popup-id="buy-one-click" data-url="/ajax/fast_order/load/">
                <span class="b-link__text b-link__text--one-click js-open-popup">Купить в 1 клик</span>
            </a>
            <hr class="b-counter-basket__hr" />
            <?php
            /**
             * @todo Акции
             */
            ?>
            <p class="b-counter-basket__text b-counter-basket__text--red">Акция. 4+1 подарок при
                                                                          покупке</p>
            <p class="b-counter-basket__text">При покупке четырех кормов, пятый вы получите
                                              бесплатно</p>
            <p class="b-counter-basket__text">5 июня — 25 августа 2017</p>
            <?php ?>
        </div>
        <div class="b-preloader">
            <div class="b-preloader__spinner">
                <img class="b-preloader__image"
                     src="/static/build/images/inhtml/spinner.svg"
                     alt="spinner"
                     title="" />
            </div>
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
                    <?= $product->getDetailText()->getText() ?>
                </div>
            </div>
            <div class="b-description-tab__column b-description-tab__column--characteristics">
                <div class="b-description-tab__title">Подробные характеристики</div>
                <div class="b-characteristics-tab">
                    <ul class="b-characteristics-tab__list">
                        <li class="b-characteristics-tab__item" <?= (!$currentOffer->getXmlId()) ? 'style="display:none"' : '' ?>>
                            <div class="b-characteristics-tab__characteristics-text">
                                <span>Артикул</span>
                                <div class="b-characteristics-tab__dots"></div>
                            </div>
                            <div class="b-characteristics-tab__characteristics-value">
                                <?= $currentOffer->getXmlId() ?>
                            </div>
                        </li>
                        <li class="b-characteristics-tab__item" <?= (!$product->getPurpose()) ? 'style="display:none"' : '' ?>>
                            <div class="b-characteristics-tab__characteristics-text"><span>Направленность</span>
                                <div class="b-characteristics-tab__dots"></div>
                            </div>
                            <div class="b-characteristics-tab__characteristics-value">
                                <?= $product->getPurpose() ? $product->getPurpose()->getName() : '' ?>
                            </div>
                        </li>
                        <li class="b-characteristics-tab__item" <?= (!$product->getCountry()) ? 'style="display:none"' : '' ?>>
                            <div class="b-characteristics-tab__characteristics-text">
                                <span>Страна производства</span>
                                <div class="b-characteristics-tab__dots"></div>
                            </div>
                            <div class="b-characteristics-tab__characteristics-value">
                                <?= $product->getCountry() ? $product->getCountry()->getName() : '' ?>
                            </div>
                        </li>
                        <li class="b-characteristics-tab__item" <?= (!$currentOffer->getKindOfPacking()) ? 'style="display:none"' : '' ?>>
                            <div class="b-characteristics-tab__characteristics-text">
                                <span>Упаковано</span>
                                <div class="b-characteristics-tab__dots"></div>
                            </div>
                            <div class="b-characteristics-tab__characteristics-value">
                                <?= $currentOffer->getKindOfPacking() ? $currentOffer->getKindOfPacking()
                                                                                     ->getName() : '' ?>
                            </div>
                        </li>
                        <li class="b-characteristics-tab__item" <?= (!$currentOffer->getMultiplicity()) ? 'style="display:none"' : '' ?>>
                            <div class="b-characteristics-tab__characteristics-text">
                                <span>В упаковке</span>
                                <div class="b-characteristics-tab__dots"></div>
                            </div>
                            <div class="b-characteristics-tab__characteristics-value">
                                <?= $currentOffer->getMultiplicity() ?> шт.
                            </div>
                        </li>
                        <?php
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
