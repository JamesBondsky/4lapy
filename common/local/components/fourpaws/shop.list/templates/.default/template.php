<?php declare(strict_types = 1);

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arResult
 */

use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}


$frame = $this->createFrame(); ?>
<div class="b-stores__block">
    <h2 class="b-title b-title--stores">Ваш город</h2>
    <a class="b-link b-link--select js-open-popup js-stores"
       href="javascript:void(0);"
       title="<?= $arResult['CITY'] ?>"
       data-url="/ajax/store/list/chooseCity/"
       data-code="<?= $arResult['CITY_CODE'] ?>"
       data-popup-id="pick-city"><?= $arResult['CITY'] ?></a>
    <div class="b-stores-sort">
        <?php if (\is_array($arResult['SERVICES']) && !empty($arResult['SERVICES'])) {
            ?>
            <div class="b-stores-sort__checkbox-block" data-url="/ajax/store/list/checkboxFilter/">
                <?php foreach ($arResult['SERVICES'] as $key => $service) {
                    ?>
                    <div class="b-checkbox b-checkbox--stores">
                        <input class="b-checkbox__input"
                               type="checkbox"
                               name="stores-sort[]"
                               id="stores-sort-<?= $key ?>"
                               data-url="/ajax/store/list/checkboxFilter/"
                               value="<?= $service['ID'] ?>" />
                        <label class="b-checkbox__name b-checkbox__name--stores"
                               for="stores-sort-<?= $key ?>">
                            <span class="b-checkbox__text"><?= $service['UF_NAME'] ?></span>
                        </label>
                    </div>
                    <?php
                } ?>
            </div>
            <?php
        } ?>
        <div class="b-form-inline b-form-inline--stores-search">
            <form class="b-form-inline__form" data-url="/ajax/store/list/search/">
                <div class="b-input b-input--stores-search js-stores-search">
                    <input class="b-input__input-field b-input__input-field--stores-search js-stores-search"
                           type="text"
                           id="stores-search"
                           name="search"
                           placeholder="Поиск по адресу, метро и названию ТЦ"
                           data-url="/ajax/store/list/search/" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="b-stores__block">
    <div class="b-availability">
        <div class="b-catalog-filter b-catalog-filter--stores js-availability-parent">
            <div class="b-catalog-filter__sort-part b-catalog-filter__sort-part--stores">
                <span class="b-catalog-filter__label b-catalog-filter__label--amount b-catalog-filter__label--stores"><?= count(
                        $arResult['STORES']
                    ) ?> <?= WordHelper::declension(
                        (int)$arResult['STORES'],
                        [
                            'магазин',
                            'магазина',
                            'магазинов',
                        ]
                    ) ?></span>
                <span class="b-catalog-filter__sort">
                    <span class="b-catalog-filter__label b-catalog-filter__label--sort b-catalog-filter__label--stores">Сортировать</span>
                    <span class="b-select b-select--stores">
                        <select class="b-select__block b-select__block--stores"
                                name="sort"
                                data-url="/ajax/store/list/order/" title="Сортировать">
                            <option value="city">по городу</option>
                            <option value="address">по адресу</option>
                            <option value="metro"<?= (!isset($arResult['METRO'])
                                                      || empty($arResult['METRO'])) ? ' style="display:none"' : '' ?>>по метро</option>
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
                </ul>
            </div>
            <div class="b-tab-delivery-map b-tab-delivery-map--stores js-content-map">
                <a class="b-link b-link b-link--popup-back b-link b-link--popup-choose-shop js-product-list js-map-shoose-shop"
                   href="javascript:void(0);">Выберите магазин</a>
                <div class="b-tab-delivery-map__map"
                     id="map"
                     data-url="/ajax/store/list/chooseCity/"></div>
                <a class="b-link b-link--close-baloon js-product-list"
                   href="javascript:void(0);"
                   title="">
                    <span class="b-icon b-icon--close-baloon">
                        <?= new SvgDecorator('icon-close-baloon', 18, 18) ?>
                    </span>
                </a>
            </div>
        </div>
        <div class="b-preloader js-preload-stores">
            <div class="b-preloader__spinner">
                <img class="b-preloader__image"
                     src="/static/build/images/inhtml/spinner.svg"
                     alt="spinner"
                     title="">
            </div>
        </div>
    </div>
</div>