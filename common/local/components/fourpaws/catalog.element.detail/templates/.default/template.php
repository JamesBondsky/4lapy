<?php
/**
 * @var CBitrixComponentTemplate $this
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogElementDetailComponent $component
 * @var Product $product
 * @var Offer $currentOffer
 */

use FourPaws\App\Application;
use FourPaws\App\Templates\ViewsEnum;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Components\CatalogElementDetailComponent;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\DateHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\LocationBundle\LocationService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

/** @var LocationService $locationService */
$locationService = Application::getInstance()->getContainer()->get('location.service');

/**
 * @todo Разворачивать всю цепочку нужных элементов в result_modifier
 */

$product = $arResult['PRODUCT'];
$offers = $product->getOffers();
$brand = $product->getBrand();
$currentOffer = $arResult['CURRENT_OFFER'];

$mainCombinationType = $currentOffer->getClothingSize() ? 'SIZE' : 'VOLUME';

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
                $iterator = 0;
                /** @var Offer $offer */
                foreach ($offers as $offer) {
                    if (!$offer->getImagesIds()) {
                        continue;
                    }

                    $images = $offer->getResizeImages(480, 480);
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
                                         role="presentation"/>
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
                foreach ($offers as $offer) {
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
                                     role="presentation"/>
                            </div>
                        </div>
                        <?php
                    }
                } ?>
            </div>
        </div>
    </div>
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_OFFERS_VIEW);
?>
    <div class="b-product-card__option-product js-weight-default">
        <?php if ($mainCombinationType && $offers->count() > 0 && $product->isFood()) {
            ?>
            <?php if ($mainCombinationType === 'SIZE') {
                ?>
                <div class="b-product-card__weight">Размеры</div>
                <?php
            } else {
                ?>
                <div class="b-product-card__weight">Варианты фасовки</div>
                <?php
            } ?>
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
                                $weight = $offer->getCatalogProduct()->getWeight();
                                if ($weight > 0) {
                                    $value = WordHelper::showWeight($weight);
                                }
                            }
                        }

                        if (empty($value)) {
                            continue;
                        } ?>
                        <li class="b -weight-container__item b-weight-container__item--product<?= $isCurrentOffer ? ' active' : '' ?>">
                            <a class="b-weight-container__link b-weight-container__link--product js-offer-link-<?= $offer->getId() ?> js-price-product<?= $isCurrentOffer ? ' active-link' : '' ?>"
                               href="<?= $offer->getLink() ?>"
                               data-weight=" <?= $value ?>"
                               data-price=""
                               data-image="<?= $mainImageIndex[$offer->getId()] ?>"
                               data-url="<?= $offer->getLink() ?>"
                               data-offerid="<?= $offer->getId() ?>">
                                <span class="b-weight-container__line">
                                    <span class="b-weight-container__weight"><?= $value ?></span>
                                    <span class="b-weight-container__price"><? /** подгрузка */ ?></span>
                                </span>
                                <span class="b-weight-container__line">
                                    <span class="b-weight-container__not"
                                          style="display: none"><? /** подгрузка */ ?></span>
                                    <span class="b-weight-container__action js-offer-action"
                                          style="display: none"><? /** подгрузка */ ?></span>
                                    <span class="b-weight-container__old-price b-weight-container__old-price--big"
                                          style="display: none">
                                        <? /** подгрузка */ ?>
                                    </span>
                                    <span class="b-weight-container__cart js-offer-in-cart-<?= $offer->getId() ?>"
                                          style="display: none">
                                        <? /** подгрузка */ ?>
                                        <span class="b-cart b-cart--cart-product">
                                            <span class="b-icon b-icon--cart-product">
                                                <?= new SvgDecorator('icon-cart', 16, 16) ?>
                                            </span>
                                        </span>
                                        <span class="b-weight-container__number">0</span>
                                    </span>
                                </span>
                                <span class="b-weight-container__line" style="display: none" data-not-available>
                                    <span class="b-weight-container__not">Нет в наличии</span>
                                </span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <?php
        } ?>
    </div>
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_CURRENT_OFFER_INFO);
?>

    <div class="b-product-card__info js-preloader-fix">
        <div class="b-product-information">
            <ul class="b-product-information__list">
                <?php if ($currentOffer->getClothingSize()) {
                    ?>
                    <li class="b-product-information__item">
                        <div class="b-product-information__title-info js-info-product">Размер</div>
                        <div class="b-product-information__value"><?= $currentOffer->getClothingSize()
                                ->getName() ?></div>
                    </li>
                    <?php
                } elseif ($currentOffer->getVolumeReference()) {
                    ?>
                    <li class="b-product-information__item">
                        <div class="b-product-information__title-info js-info-product">Объем</div>
                        <div class="b-product-information__value"><?= $currentOffer->getVolumeReference()
                                ->getName() ?></div>
                    </li>
                    <?php
                } elseif ($currentOffer->getCatalogProduct()->getWeight() > 0) {
                    ?>
                    <li class="b-product-information__item">
                        <div class="b-product-information__title-info js-info-product">Вес</div>
                        <div class="b-product-information__value">
                            <?= WordHelper::showWeight($currentOffer->getCatalogProduct()->getWeight()) ?>
                        </div>
                    </li>
                    <?php
                } ?>
                <li class="b-product-information__item">
                    <div class="b-product-information__title-info">Цена</div>
                    <div class="b-product-information__value b-product-information__value--price">
                        <?php if ($currentOffer->getOldPrice() > $currentOffer->getPrice()) {
                            ?>
                            <span class="b-product-information__old-price"><?= ceil($currentOffer->getOldPrice()) ?> </span>
                            <span class="b-ruble b-ruble--old-price">₽</span>
                            <?php
                        } ?>
                        <span class="b-product-information__price js-price-product">
                            <?= ceil($currentOffer->getPrice()) ?>
                        </span>
                        <span class="b-ruble b-ruble--product-information">&nbsp;₽</span>
                        <span class="b-product-information__bonus js-bonus-<?= $currentOffer->getId() ?>"></span>
                    </div>
                </li>
                <?php if (!empty($currentOffer->getFlavourCombination())) {
                    $unionOffers = $component->getOffersByUnion('flavour', $currentOffer->getFlavourCombination());
                    if (!$unionOffers->isEmpty()) { ?>
                        <li class="b-product-information__item">
                            <div class="b-product-information__title-info">Вкус</div>
                            <div class="b-product-information__value b-product-information__value--select">
                                <div class="b-select b-select--product">
                                    <select class="b-select__block b-select__block--product js-select-link">
                                        <?php /** @var Offer $unionOffer */
                                        foreach ($unionOffers as $unionOffer) { ?>
                                            <option value="<?= $unionOffer->getDetailPageUrl() ?>" <?= $unionOffer->getId() === $currentOffer->getId() ? ' selected' : '' ?>>
                                                <?= $unionOffer->getName() ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </li>
                    <?php }
                } ?>
                <?php if (!empty($currentOffer->getColourCombination())) {
                    $continue = true;
                    if (trim($currentOffer->getFlavourCombination()) === trim($currentOffer->getColourCombination())) {
                        $continue = false;
                    }
                    if ($continue) {
                        $unionOffers = $component->getOffersByUnion('color', $currentOffer->getColourCombination());
                        if (!$unionOffers->isEmpty()) {
                            ?>
                            <li class="b-product-information__item">
                                <div class="b-product-information__title-info">Цвет
                                </div>
                                <div class="b-product-information__value b-product-information__value--select">
                                    <div class="b-select b-select--product">
                                        <select class="b-select__block b-select__block--product js-select-link">
                                            <?php /** @var Offer $unionOffer */
                                            foreach ($unionOffers as $unionOffer) {
                                                ?>
                                                <option value="<?= $unionOffer->getDetailPageUrl() ?>" <?= $unionOffer->getId() === $currentOffer->getId() ? ' selected' : '' ?>>
                                                    <?= $unionOffer->getName() ?>
                                                </option>
                                                <?php
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                            </li>
                            <?php
                        }
                    }
                } ?>

            </ul>
        </div>
        <div class="b-counter-basket">
            <div class="b-plus-minus b-plus-minus--half-mobile js-buy1click-ps js-plus-minus-cont">
                <a class="b-plus-minus__minus js-minus" href="javascript:void(0);"></a>
                <input class="b-plus-minus__count js-plus-minus-count" value="1" type="text"
                       data-cont-max="<?=$currentOffer->getQuantity()?>" data-one-price="<?=$currentOffer->getPrice()?>" />
                <a class="b-plus-minus__plus js-plus" href="javascript:void(0);"></a>
                <span class="b-plus-minus__by-line">Количество</span>
            </div>
            <?php if ($currentOffer->getMultiplicity() && ($currentOffer->getMultiplicity() > 1) && $currentOffer->getQuantity() >= $currentOffer->getMultiplicity()) {
                ?>
                <a class="b-counter-basket__add-set js-add-set" href="javascript:void(0)" title=""
                   data-count="<?= $currentOffer->getMultiplicity() ?>">
                    Округлить до упаковки (<?= $currentOffer->getMultiplicity() ?> шт.)
                    <span>— скидка 3%</span>
                </a>
                <?php
            } ?>
            <a class="b-counter-basket__basket-link js-basket-add js-this-product"
               href="javascript:void(0)"
               title=""
               data-offerId="<?= $currentOffer->getId(); ?>"
               data-url="/ajax/sale/basket/add/">
                <span class="b-counter-basket__basket-text">Добавить в корзину</span>
                <span class="b-icon b-icon--advice"><?= new SvgDecorator('icon-cart', 20, 20) ?></span>
            </a>
            <?php if ($arResult['SHOW_FAST_ORDER']) { ?>
                <a class="b-link b-link--one-click js-open-popup js-open-popup--one-click" href="javascript:void(0)"
                   title="Купить в 1 клик" data-popup-id="buy-one-click" data-url="/ajax/sale/fast_order/load/"
                   data-offerId="<?= $currentOffer->getId() ?>" data-type="card">
                    <span class="b-link__text b-link__text--one-click js-open-popup">Купить в 1 клик</span>
                </a>
            <?php } ?>
            <hr class="b-counter-basket__hr">
            <?php if ($currentOffer->isShare()) {
                /** @var IblockElement $share */
                foreach ($currentOffer->getShare() as $share) {
                    $activeFrom = $share->getDateActiveFrom();
                    $activeTo = $share->getDateActiveTo(); ?>
                    <p class="b-counter-basket__text b-counter-basket__text--red"><?= $share->getName() ?></p>
                    <?php if (!empty($share->getPreviewText()->getText())) { ?>
                        <p class="b-counter-basket__text"><?= $share->getPreviewText()->getText() ?></p>
                    <?php } ?>
                    <p class="b-counter-basket__text">
                        <?php if ($activeFrom && $activeTo) { ?>
                            <?= DateHelper::replaceRuMonth($activeFrom->format('d #n#')) ?>
                            —
                            <?= DateHelper::replaceRuMonth($activeTo->format('d #n# Y')) ?>
                        <?php } elseif ($activeFrom) { ?>
                            С <?= DateHelper::replaceRuMonth($activeFrom->format('d #n#')) ?>
                        <?php } elseif ($activeTo) { ?>
                            По <?= DateHelper::replaceRuMonth($activeTo->format('d #n# Y')) ?>
                        <?php } ?>
                    </p>
                <?php }
            } ?>
        </div>
        <div class="b-preloader">
            <div class="b-preloader__spinner">
                <img class="b-preloader__image"
                     src="/static/build/images/inhtml/spinner.svg"
                     alt="spinner"
                     title=""/>
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
                <p><?= $product->getDetailText()->getText() ?></p>
            </div>
            <div class="b-description-tab__column b-description-tab__column--characteristics">
                <h2>Подробные характеристики</h2>
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
