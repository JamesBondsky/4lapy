<?php
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

use FourPaws\StoreBundle\Entity\Store;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!is_array($arResult['STORES']) || empty($arResult['STORES'])) {
    return;
}

$frame = $this->createFrame(); ?>
<div class="b-stores__block">
    <h2 class="b-title b-title--stores">Ваш город</h2>
    <a class="b-link b-link--select"
       href="javascript:void(0);"
       title="Москва">Москва</a>
    <div class="b-stores-sort">
        <div class="b-stores-sort__checkbox-block">
            <div class="b-checkbox b-checkbox--stores">
                <input class="b-checkbox__input" type="checkbox" name="stores-sort" id="stores-sort-1" />
                <label class="b-checkbox__name b-checkbox__name--stores"
                       for="stores-sort-1">
                    <span class="b-checkbox__text">аквариумистика</span>
                </label>
            </div>
            <div class="b-checkbox b-checkbox--stores">
                <input class="b-checkbox__input" type="checkbox" name="stores-sort" id="stores-sort-2" />
                <label class="b-checkbox__name b-checkbox__name--stores"
                       for="stores-sort-2">
                    <span class="b-checkbox__text">ветаптека</span>
                </label>
            </div>
            <div class="b-checkbox b-checkbox--stores">
                <input class="b-checkbox__input" type="checkbox" name="stores-sort" id="stores-sort-3" />
                <label class="b-checkbox__name b-checkbox__name--stores"
                       for="stores-sort-3">
                    <span class="b-checkbox__text">гравировка</span>
                </label>
            </div>
            <div class="b-checkbox b-checkbox--stores">
                <input class="b-checkbox__input" type="checkbox" name="stores-sort" id="stores-sort-4" />
                <label class="b-checkbox__name b-checkbox__name--stores"
                       for="stores-sort-4">
                    <span class="b-checkbox__text">груминг</span>
                </label>
            </div>
            <div class="b-checkbox b-checkbox--stores">
                <input class="b-checkbox__input" type="checkbox" name="stores-sort" id="stores-sort-5" />
                <label class="b-checkbox__name b-checkbox__name--stores"
                       for="stores-sort-5">
                    <span class="b-checkbox__text">котята и щенки</span>
                </label>
            </div>
            <div class="b-checkbox b-checkbox--stores">
                <input class="b-checkbox__input" type="checkbox" name="stores-sort" id="stores-sort-6" />
                <label class="b-checkbox__name b-checkbox__name--stores"
                       for="stores-sort-6">
                    <span class="b-checkbox__text">птицы и грызуны</span>
                </label>
            </div>
        </div>
        <div class="b-form-inline b-form-inline--stores-search">
            <form class="b-form-inline__form">
                <input class="b-input b-input--stores-search"
                       type="text"
                       id="stores-search"
                       placeholder="Поиск по адресу, метро и названию ТЦ" />
            </form>
        </div>
    </div>
</div>
<div class="b-stores__block">
    <div class="b-availability">
        <div class="b-catalog-filter b-catalog-filter--stores js-availability-parent">
            <div class="b-catalog-filter__sort-part b-catalog-filter__sort-part--stores">
                <span class="b-catalog-filter__label b-catalog-filter__label--amount b-catalog-filter__label--stores">23 магазина</span>
                <span class="b-catalog-filter__sort">
                    <span class="b-catalog-filter__label b-catalog-filter__label--sort b-catalog-filter__label--stores">Сортировать</span>
                    <span class="b-select b-select--stores">
                        <select class="b-select__block b-select__block--stores" name="sort">
                            <option value="sort-0">по популярности</option>
                            <option value="sort-1">по цене</option>
                        </select>
                        <span class="b-select__arrow"></span>
                    </span>
                </span>
            </div>
            <ul class="b-availability-tab-list b-availability-tab-list--stores js-availability-list">
                <li class="b-availability-tab-list__item active">
                    <a class="b-availability-tab-list__link js-product-list"
                       href="javascript:void(0)"
                       aria-controls="shipping-list"
                       title="Списком">Списком</a>
                </li>
                <li class="b-availability-tab-list__item">
                    <a class="b-availability-tab-list__link js-product-map"
                       href="javascript:void(0)"
                       aria-controls="on-map"
                       title="На карте">На карте</a>
                </li>
            </ul>
        </div>
        <div class="b-availability__content js-availability-content">
            <div class="b-tab-delivery b-tab-delivery--stores js-content-list js-map-list-scroll">
                <ul class="b-delivery-list js-delivery-list">
                    <?php /** @var Store $store */
                    foreach ($arResult['STORES'] as $store) { ?>
                        <li class="b-delivery-list__item">
                            <a class="b-delivery-list__link b-delivery-list__link--stores js-accordion-stores-list"
                               href="javascript:void(0);"
                               title="">
                            <span class="b-delivery-list__col b-delivery-list__col--stores b-delivery-list__col--addr">
                                <?php $metro = $store->getMetro()?>
                                <span class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--blue"></span>
                                <?=!empty($store->getMetro()) ? $store->getMetro().', ' : ''?><?=$store->getAddress()?>
                            </span>
                                <span class="b-delivery-list__col b-delivery-list__col--stores b-delivery-list__col--phone"><?=$store->getPhone()?></span>
                                <span class="b-delivery-list__col b-delivery-list__col--stores b-delivery-list__col--time"><?=$store->getSchedule()?></span>
                                <div class="b-tag">
                                    <span class="b-tag__item">аквариумистика</span>,
                                    <span class="b-tag__item">ветаптека</span>,
                                    <span class="b-tag__item">гравировка</span>,
                                    <span class="b-tag__item">груминг</span>,
                                    <span class="b-tag__item">птицы и грызуны</span>
                                </div>
                            </a>
                            <div class="b-delivery-list__information">
                                <div class="b-delivery-list__image-wrapper">
                                    <img src="images/content/stores-image.jpg"
                                         class="b-delivery-list__image"
                                         alt=""
                                         title="">
                                </div>
                                <div class="b-delivery-list__text">
                                    <p class="b-delivery-list__information-header">Как нас найти</p>
                                    <p class="b-delivery-list__information-text">
                                        Метро «Автозаводская», последний вагон из
                                        центра, из дверей поворачиваете направо,
                                        пройдите вперед 100 метров. Наш магазин
                                        находится в новом высотном доме.
                                    </p>
                                    <a class="b-delivery-list__information-link"
                                       id="shop_id1"
                                       data-shop-id="1"
                                       href="javascript:void(0);"
                                       title="">Показать на карте</a>
                                    <a class="b-delivery-list__information-link"
                                       href="javascript:void(0);"
                                       title="">Проложить маршрут</a>
                                </div>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="b-tab-delivery-map b-tab-delivery-map--stores js-content-map">
                <a class="b-link b-link b-link--popup-back b-link b-link--popup-choose-shop js-product-list js-map-shoose-shop"
                   href="javascript:void(0);">Выберите магазин</a>
                <div class="b-tab-delivery-map__map" id="map"></div>
                <a class="b-link b-link--close-baloon js-product-list"
                   href="javascript:void(0);"
                   title="">
                    <span class="b-icon b-icon--close-baloon">
                        <svg class="b-icon__svg"
                             viewBox="0 0 18 18 "
                             width="18px"
                             height="18px"><use class="b-icon__use"
                                                xlink:href="icons.svg#icon-close-baloon"></use>
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>