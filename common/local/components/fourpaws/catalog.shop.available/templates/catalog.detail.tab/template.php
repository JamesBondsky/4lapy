<?php

use FourPaws\Catalog\Model\Offer;
use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 * @var array                     $templateData
 *
 * @var string                    $componentPath
 * @var string                    $templateName
 * @var string                    $templateFile
 * @var string                    $templateFolder
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
 */

$this->setFrameMode(true);

/** @var Offer $offer */
$offer = $arParams['OFFER'];
?>
<div class="b-tab-content__container js-tab-content" data-tab-content="availability">
    <h2 class="b-title b-title--advice b-title--stock">Наличие в магазинах</h2>
    <div class="b-availability">
        <a class="b-link b-link--show-map js-product-map"
           href="javascript:void(0);" title="">
            <span class="b-icon b-icon--map">
                <?= new SvgDecorator('icon-map', 22, 20) ?>
            </span>
        </a>
        <ul class="b-availability-tab-list">
            <li class="b-availability-tab-list__item active">
                <a class="b-availability-tab-list__link js-product-list"
                   href="javascript:void(0)" aria-controls="shipping-list" title="Списком">Списком</a>
            </li>
            <li class="b-availability-tab-list__item">
                <a class="b-availability-tab-list__link js-product-map"
                   href="javascript:void(0)" aria-controls="on-map" title="На карте">На карте</a>
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
                <a class="b-link b-link--more-shop js-load-shops"
                   data-url="/ajax/showMore"
                   data-step="1"
                   href="javascript:void(0)">Показать
                                             еще</a>
            </div>
            <div class="b-tab-delivery-map b-tab-delivery-map--card js-content-map">
                <div class="b-tab-delivery-map__map js-product-map" id="map" data-url="/ajax/store/list/getByItem/?offer=<?=$offer->getId()?>"></div>
                <a class="b-link b-link--close-baloon js-product-list"
                   href="javascript:void(0);"
                   title="">
                    <span class="b-icon b-icon--close-baloon">
                        <?= new SvgDecorator('icon-close-baloon', 18, 18) ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>
