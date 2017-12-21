<?php
/**
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($arResult['VARIABLES']['SECTION_CODE'] === $arResult['VARIABLES']['SECTION_CODE_PATH'] && $arResult['VARIABLES']['SECTION_ID']) {
    include __DIR__ . '/sections.php';
    return;
}


/**
 * @todo Определение лендоса
 */

$APPLICATION->IncludeComponent('fourpaws:catalog.category', '');

return;



?>
<div class="b-catalog">
    <div class="b-container b-container--catalog-filter">
        <div class="b-catalog__wrapper-title b-catalog__wrapper-title--filter">
            <nav class="b-breadcrumbs">
                <ul class="b-breadcrumbs__list">
                    <li class="b-breadcrumbs__item">
                        <a class="b-breadcrumbs__link" href="javascript:void(0);" title="Товары для собак">
                            Товары для собак
                        </a>
                    </li>
                </ul>
            </nav>
            <h1 class="b-title b-title--h1 b-title--catalog-filter">Корм для собак</h1>
        </div>
        <?php
        /**
         * @todo  filters
         */
        ?>
        <aside class="b-filter b-filter--popup js-filter-popup">
            <div class="b-filter__top">
                <a class="b-filter__close js-close-filter" href="javascript:void(0);" title=""></a>
                <div class="b-filter__title">Фильтры</div>
            </div>
            <div class="b-filter__wrapper b-filter__wrapper--scroll">
                <form class="b-form js-filter-form">
                    <div class="b-filter__block b-filter__block--back">
                        <ul class="b-back">
                            <li class="b-back__item"><a class="b-link b-link--back" href="javascript:void(0);"
                                                        title="Товары для собак">Товары для собак</a>
                            </li>
                        </ul>
                    </div>
                    <div class="b-filter__block b-filter__block--reset js-reset-link-block"><a
                                class="b-link b-link--reset js-reset-filter" href="javascript:void(0);"
                                title="Сбросить фильтры">Сбросить фильтры</a>
                    </div>
                    <div class="b-filter__block b-filter__block--select">
                        <h3 class="b-title b-title--filter-header">Категория</h3>
                        <div class="b-select b-select--filter">
                            <ul class="b-filter-link-list b-filter-link-list--filter b-filter-link-list--select-filter js-accordion-filter-select js-filter-checkbox">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                            <a class="b-link b-link--filter-more b-link--filter-select js-open-filter-all"
                               href="javascript:void(0);" title="Показать все">Показать все <span
                                        class="b-icon b-icon--more"><svg class="b-icon__svg" viewBox="0 0 10 10 "
                                                                         width="10px" height="10px"><use
                                                class="b-icon__use" xlink:href="icons.svg#icon-arrow-down"></use></svg></span></a>
                        </div>
                    </div>
                    <div class="b-filter__block">
                        <h3 class="b-title b-title--filter-header">Бренд</h3>
                        <ul class="b-filter-link-list b-filter-link-list--filter js-accordion-filter js-filter-checkbox">
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-brand" value="filter-brand-0"
                                           id="filter-brand-0"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Chappy">Chappy</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-brand" value="filter-brand-1"
                                           id="filter-brand-1"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);"
                                            title="Royal CaninRoyal CaninRoyal CaninRoyal Canin">Royal CaninRoyal
                                        CaninRoyal CaninRoyal Canin</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-brand" value="filter-brand-2"
                                           id="filter-brand-2"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Hills">Hills</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-brand" value="filter-brand-3"
                                           id="filter-brand-3"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Royal Canin">Royal Canin</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-brand" value="filter-brand-4"
                                           id="filter-brand-4"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Hills">Hills</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-brand" value="filter-brand-5"
                                           id="filter-brand-5"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Royal Canin">Royal Canin</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-brand" value="filter-brand-6"
                                           id="filter-brand-6"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Hills">Hills</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-brand" value="filter-brand-7"
                                           id="filter-brand-7"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Royal Canin">Royal Canin</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-brand" value="filter-brand-8"
                                           id="filter-brand-8"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Hills">Hills</a>
                                </label>
                            </li>
                        </ul>
                        <a class="b-link b-link--filter-more js-open-filter-all" href="javascript:void(0);"
                           title="Показать все">Показать все <span class="b-icon b-icon--more"><svg class="b-icon__svg"
                                                                                                    viewBox="0 0 10 10 "
                                                                                                    width="10px"
                                                                                                    height="10px"><use
                                            class="b-icon__use"
                                            xlink:href="icons.svg#icon-arrow-down"></use></svg></span></a>
                    </div>
                    <div class="b-filter__block">
                        <h3 class="b-title b-title--filter-header">Возраст</h3>
                        <ul class="b-filter-link-list b-filter-link-list--filter js-filter-checkbox">
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-age" value="filter-age-0" id="filter-age-0"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Юниор (до года)">Юниор (до года)</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-age" value="filter-age-1" id="filter-age-1"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Эдалт (1–7 лет)">Эдалт (1–7 лет)</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           disabled="disabled" type="checkbox" name="filter-age" value="filter-age-2"
                                           id="filter-age-2"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Сеньор (старше 7 лет)">Сеньор (старше 7
                                        лет)</a>
                                </label>
                            </li>
                        </ul>
                    </div>
                    <div class="b-filter__block">
                        <h3 class="b-title b-title--filter-header">Размер</h3>
                        <ul class="b-filter-link-list b-filter-link-list--filter js-filter-checkbox">
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           disabled="disabled" type="checkbox" name="filter-size" value="filter-size-0"
                                           id="filter-size-0"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Мелкий">Мелкий</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-size" value="filter-size-1" id="filter-size-1"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Средний">Средний</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-size" value="filter-size-2" id="filter-size-2"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Крупный">Крупный</a>
                                </label>
                            </li>
                        </ul>
                    </div>
                    <div class="b-filter__block">
                        <h3 class="b-title b-title--filter-header">Вид упаковки</h3>
                        <ul class="b-filter-link-list b-filter-link-list--filter js-filter-checkbox">
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-packing-type" value="filter-packing-type-0"
                                           id="filter-packing-type-0"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Банка">Банка</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-packing-type" value="filter-packing-type-1"
                                           id="filter-packing-type-1"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Мешок">Мешок</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           disabled="disabled" type="checkbox" name="filter-packing-type"
                                           value="filter-packing-type-2" id="filter-packing-type-2"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Пауч">Пауч</a>
                                </label>
                            </li>
                        </ul>
                    </div>
                    <div class="b-filter__block">
                        <h3 class="b-title b-title--filter-header">Цена, <span
                                    class="b-ruble b-ruble--catalpg-filter">₽</span></h3>
                        <div class="b-range js-filter-input">
                            <div class="b-range__price-block">
                                <input class="b-input b-input--price b-input--min js-price-min" type="text" data-min="0"
                                       value="0" name="price-min"/><span class="b-range__line-input">-</span>
                                <input class="b-input b-input--price b-input--max js-price-max" type="text"
                                       data-max="10000" value="10569" name="price-max"/>
                            </div>
                            <div class="b-range__line js-slider-range"></div>
                        </div>
                    </div>
                    <div class="b-filter__block">
                        <h3 class="b-title b-title--filter-header">Доступность</h3>
                        <ul class="b-filter-link-list b-filter-link-list--filter js-filter-checkbox">
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-availability" value="filter-availability-0"
                                           id="filter-availability-0"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Доставка">Доставка</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-availability" value="filter-availability-1"
                                           id="filter-availability-1"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Самовывоз">Самовывоз</a>
                                </label>
                            </li>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                           type="checkbox" name="filter-availability" value="filter-availability-2"
                                           id="filter-availability-2"/><a
                                            class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                            href="javascript:void(0);" title="Под заказ">Под заказ</a>
                                </label>
                            </li>
                        </ul>
                    </div>
                    <div class="b-filter__block b-filter__block--discount js-discount-mobile-here"></div>
                </form>
            </div>
            <div class="b-filter__bottom"><a class="b-filter__button" href="javascript:void(0);" title="">Показать 300
                    товаров</a>
            </div>
        </aside>
        <main class="b-catalog__main" role="main">
            <div class="b-catalog-filter js-permutation-desktop-here"><a
                        class="b-link b-link--open-filter js-permutation-filter js-open-filter"
                        href="javascript:void(0);" title="Открыть фильтры"><span class="b-icon b-icon--open-filter"><svg
                                class="b-icon__svg" viewBox="0 0 19 14 " width="19px" height="14px"><use
                                    class="b-icon__use" xlink:href="icons.svg#icon-open-filter"></use></svg></span></a>
                <div class="b-catalog-filter__filter-part">
                    <dl class="b-catalog-filter__row">
                        <dt class="b-catalog-filter__label">Часто ищут:</dt>
                        <dd class="b-catalog-filter__block"><a class="b-link b-link--filter" href="javascript:void(0);"
                                                               title="Hills для взрослых собак среднего размера">Hills
                                для взрослых собак среднего размера</a><a class="b-link b-link--filter"
                                                                          href="javascript:void(0);"
                                                                          title="Chappy для маленьких собак">Chappy для
                                маленьких собак</a>
                        </dd>
                    </dl>
                    <div class="b-line b-line--sort-desktop"></div>
                    <div class="b-catalog-filter__row b-catalog-filter__row--sort">
                        <div class="b-catalog-filter__sort-part js-permutation-mobile-here"><span
                                    class="b-catalog-filter__label b-catalog-filter__label--amount">98 товаров</span><span
                                    class="b-catalog-filter__sort"><span
                                        class="b-catalog-filter__label b-catalog-filter__label--sort">Сортировать</span>
                                        <span
                                                class="b-select b-select--sort js-filter-select">
                                            <select class="b-select__block b-select__block--sort js-filter-select"
                                                    name="sort">
                                                <option value="sort-0">по популярности</option>
                                                <option value="sort-1">по цене</option>
                                            </select><span class="b-select__arrow"></span></span>
                                            </span><span class="b-catalog-filter__discount js-discount-desktop-here"><ul
                                        class="b-filter-link-list b-filter-link-list--filter js-discount-checkbox js-filter-checkbox"><li
                                            class="b-filter-link-list__item"><label
                                                class="b-filter-link-list__label"><input
                                                    class="b-filter-link-list__checkbox js-discount-input js-filter-control"
                                                    type="checkbox" name="filter-discount" value="filter-discount-0"
                                                    id="filter-discount-0"/><a
                                                    class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                                    href="javascript:void(0);" title="Товары со скидкой">Товары со скидкой</a></label></li></ul></span>
                        </div>
                        <div class="b-catalog-filter__type-part"><a
                                    class="b-link b-link--type active js-link-type-normal" href="javascript:void(0);"
                                    title=""><span class="b-icon b-icon--type"><svg class="b-icon__svg"
                                                                                    viewBox="0 0 20 20 " width="20px"
                                                                                    height="20px"><use
                                                class="b-icon__use"
                                                xlink:href="icons.svg#icon-catalog-normal"></use></svg></span></a>
                            <a
                                    class="b-link b-link--type js-link-type-line" href="javascript:void(0);"
                                    title=""><span class="b-icon b-icon--type"><svg class="b-icon__svg"
                                                                                    viewBox="0 0 20 20 " width="20px"
                                                                                    height="20px"><use
                                                class="b-icon__use"
                                                xlink:href="icons.svg#icon-catalog-line"></use></svg></span>
                            </a>
                        </div>
                    </div>
                    <div class="b-line b-line--sort-mobile"></div>
                </div>
            </div>
            <div class="b-common-wrapper b-common-wrapper--visible js-catalog-wrapper">
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-15proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/royal-canin-2.jpg"
                                alt="Роял Канин" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>Роял Канин</strong> корм для собак крупных пород макси эдалт</span></span></a>
                        <div
                                class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="100"
                                            data-image="images/content/royal-canin-2.jpg">4 кг</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
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
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">100</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-gift.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/hills-cat.jpg"
                                alt="Хиллс" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>Хиллс</strong> корм для кошек тунец стерилайз</span></span></a>
                        <div class="b-common-item__rank"><a class="b-common-item__rank-text" href="javascript:void(0);"
                                                            title="Оставьте отзыв">Оставьте отзыв</a><span
                                    class="b-common-item__rank-text b-common-item__rank-text--red">4+1 в подарок при покупке</span>
                        </div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/hills-cat.jpg">3,5 кг</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
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
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">2 585</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-gift.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/hills-cat.jpg"
                                alt="Хиллс" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>Хиллс</strong> корм для кошек тунец стерилайз</span></span></a>
                        <div class="b-common-item__rank"><a class="b-common-item__rank-text" href="javascript:void(0);"
                                                            title="Оставьте отзыв">Оставьте отзыв</a><span
                                    class="b-common-item__rank-text b-common-item__rank-text--red">4+1 в подарок при покупке</span>
                        </div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/hills-cat.jpg">3,5 кг</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
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
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">2 585</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__info-wrap"><span class="b-common-item__text">Самовывоз</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating"></div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__info-wrap"><span class="b-common-item__text">Под заказ</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-gift.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/hills-cat.jpg"
                                alt="Хиллс" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>Хиллс</strong> корм для кошек тунец стерилайз</span></span></a>
                        <div class="b-common-item__rank"><a class="b-common-item__rank-text" href="javascript:void(0);"
                                                            title="Оставьте отзыв">Оставьте отзыв</a><span
                                    class="b-common-item__rank-text b-common-item__rank-text--red">4+1 в подарок при покупке</span>
                        </div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/hills-cat.jpg">3,5 кг</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
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
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">2 585</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <span class="b-common-item__add-to-cart b-common-item__add-to-cart--not-available"><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span>
                    <span
                            class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                        </span>
                        </span>
                        <div class="b-common-item__additional-information">
                            <div class="b-common-item__info-wrap"><span class="b-common-item__text">Нет в наличии</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-gift.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/hills-cat.jpg"
                                alt="Хиллс" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>Хиллс</strong> корм для кошек тунец стерилайз</span></span></a>
                        <div class="b-common-item__rank"><a class="b-common-item__rank-text" href="javascript:void(0);"
                                                            title="Оставьте отзыв">Оставьте отзыв</a><span
                                    class="b-common-item__rank-text b-common-item__rank-text--red">4+1 в подарок при покупке</span>
                        </div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/hills-cat.jpg">3,5 кг</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
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
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">2 585</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-gift.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/hills-cat.jpg"
                                alt="Хиллс" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>Хиллс</strong> корм для кошек тунец стерилайз</span></span></a>
                        <div class="b-common-item__rank"><a class="b-common-item__rank-text" href="javascript:void(0);"
                                                            title="Оставьте отзыв">Оставьте отзыв</a><span
                                    class="b-common-item__rank-text b-common-item__rank-text--red">4+1 в подарок при покупке</span>
                        </div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/hills-cat.jpg">3,5 кг</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
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
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">2 585</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <span class="b-common-item__add-to-cart b-common-item__add-to-cart--not-available"><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span>
                    <span
                            class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span>
                        </span>
                        </span>
                        <div class="b-common-item__additional-information">
                            <div class="b-common-item__info-wrap"><span class="b-common-item__text">Нет в наличии</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-gift.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/hills-cat.jpg"
                                alt="Хиллс" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>Хиллс</strong> корм для кошек тунец стерилайз</span></span></a>
                        <div class="b-common-item__rank"><a class="b-common-item__rank-text" href="javascript:void(0);"
                                                            title="Оставьте отзыв">Оставьте отзыв</a><span
                                    class="b-common-item__rank-text b-common-item__rank-text--red">4+1 в подарок при покупке</span>
                        </div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/hills-cat.jpg">3,5 кг</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
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
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">2 585</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active"><span class="b-icon"><svg
                                                class="b-icon__svg" viewBox="0 0 12 12 " width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-common-item b-common-item--catalog-item js-product-item"><span
                            class="b-common-item__sticker-wrap" style="background-color:;data-background:;"><img
                                class="b-common-item__sticker" src="images/inhtml/s-proc.svg" alt=""
                                role="presentation"/></span><span class="b-common-item__image-wrap"><img
                                class="b-common-item__image js-weight-img" src="images/content/clean-cat.jpg"
                                alt="CleanCat" title=""/></span>
                    <div
                            class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                        href="javascript:void(0);" title=""><span
                                    class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                        <div class="b-common-item__rank">
                            <div class="b-rating">
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                                <div class="b-rating__star-block"><span class="b-icon"><svg class="b-icon__svg"
                                                                                            viewBox="0 0 12 12 "
                                                                                            width="12px" height="12px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-star"></use></svg></span>
                                </div>
                            </div>
                            <span class="b-common-item__rank-text">На основе 12 отзывов</span>
                            <div class="b-common-item__rank-wrapper"><span
                                        class="b-common-item__rank-text b-common-item__rank-text--green">Новинка</span><span
                                        class="b-common-item__rank-text b-common-item__rank-text--red">Выгода 15%</span>
                            </div>
                        </div>
                        <div class="b-common-item__variant">Варианты фасовки</div>
                        <div class="b-weight-container b-weight-container--list">
                            <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                               href="javascript:void(0);" title=""></a>
                            <ul class="b-weight-container__list">
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price active-link"
                                            href="javascript:void(0);" data-price="353"
                                            data-image="images/content/clean-cat.jpg">5 л</a>
                                </li>
                                <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                        href="javascript:void(0);" data-price="915"
                                                                        data-image="images/content/pro-plan.jpg">10
                                        л</a>
                                </li>
                                <li class="b-weight-container__item"><a
                                            class="b-weight-container__link js-price unavailable-link"
                                            href="javascript:void(0);" data-price="2 585"
                                            data-image="images/content/brit.png">18 кг</a>
                                </li>
                            </ul>
                        </div>
                        <div class="b-common-item__moreinfo">
                            <div class="b-common-item__packing">Упаковка <strong>8шт.</strong>
                            </div>
                            <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong>
                            </div>
                            <div class="b-common-item__order">Только под заказ</div>
                            <div class="b-common-item__pickup">Самовызов</div>
                        </div>
                        <a class="b-common-item__add-to-cart" href="javascript:void(0);" title=""><span
                                    class="b-common-item__wrapper-link"><span class="b-cart"><span
                                            class="b-icon b-icon--cart"><svg class="b-icon__svg" viewBox="0 0 16 16 "
                                                                             width="16px" height="16px"><use
                                                    class="b-icon__use"
                                                    xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                        class="b-common-item__price js-price-block">353</span> <span
                                        class="b-common-item__currency"><span class="b-ruble">₽</span></span></span></a>
                        <div
                                class="b-common-item__additional-information">
                            <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 <span
                                            class="b-ruble b-ruble--prev-price">₽</span></span><span
                                        class="b-common-item__discount"><span
                                            class="b-common-item__disc">Скидка</span><span
                                            class="b-common-item__discount-price">200</span>
                            <span
                                    class="b-common-item__currency"><span
                                        class="b-ruble b-ruble--discount">₽</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-line b-line--catalog-filter"></div>
            <div class="b-pagination">
                <ul class="b-pagination__list">
                    <li class="b-pagination__item b-pagination__item--prev b-pagination__item--disabled"><span
                                class="b-pagination__link">Назад</span>
                    </li>
                    <li class="b-pagination__item"><a class="b-pagination__link active" href="javascript:void(0);"
                                                      title="1">1</a>
                    </li>
                    <li class="b-pagination__item"><a class="b-pagination__link" href="javascript:void(0);"
                                                      title="2">2</a>
                    </li>
                    <li class="b-pagination__item"><a class="b-pagination__link" href="javascript:void(0);"
                                                      title="3">3</a>
                    </li>
                    <li class="b-pagination__item hidden"><a class="b-pagination__link" href="javascript:void(0);"
                                                             title="4">4</a>
                    </li>
                    <li class="b-pagination__item"><span class="b-pagination__dot">&hellip;</span>
                    </li>
                    <li class="b-pagination__item hidden"><a class="b-pagination__link" href="javascript:void(0);"
                                                             title="5">5</a>
                    </li>
                    <li class="b-pagination__item"><a class="b-pagination__link" href="javascript:void(0);" title="13">13</a>
                    </li>
                    <li class="b-pagination__item b-pagination__item--next"><a class="b-pagination__link"
                                                                               href="javascript:void(0);"
                                                                               title="Вперед">Вперед</a>
                    </li>
                </ul>
            </div>
        </main>
    </div>
    <div class="b-container">
        <div class="b-line b-line--pet"></div>
        <section class="b-common-section">
            <div class="b-common-section__title-box b-common-section__title-box--viewed">
                <h2 class="b-title b-title--viewed">Просмотренные мной товары</h2>
            </div>
            <div class="b-common-section__content b-common-section__content--viewed js-scroll-viewed">
                <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                 title=""><span class="b-viewed-product__image-wrap"><img
                                    class="b-viewed-product__image" src="images/content/v-royal.png" alt="Роял Канин"
                                    title="Роял Канин"/></span><span class="b-viewed-product__description-wrap"><span
                                    class="b-viewed-product__label">Роял Канин</span><span
                                    class="b-viewed-product__description">корм для собак крупных пород макси эдалт</span></span></a>
                </div>
                <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                 title=""><span class="b-viewed-product__image-wrap"><img
                                    class="b-viewed-product__image" src="images/content/v-moderna.png" alt="Moderna"
                                    title="Moderna"/></span><span class="b-viewed-product__description-wrap"><span
                                    class="b-viewed-product__label">Moderna</span><span
                                    class="b-viewed-product__description">переноска с металической дверью и замком</span></span></a>
                </div>
                <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                 title=""><span class="b-viewed-product__image-wrap"><img
                                    class="b-viewed-product__image" src="images/content/v-hills.png" alt="Хиллс"
                                    title="Хиллс"/></span><span class="b-viewed-product__description-wrap"><span
                                    class="b-viewed-product__label">Хиллс</span><span
                                    class="b-viewed-product__description">корм для кошек стерилайз</span></span></a>
                </div>
                <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                 title=""><span class="b-viewed-product__image-wrap"><img
                                    class="b-viewed-product__image" src="images/content/v-rungo.png" alt="Rungo"
                                    title="Rungo"/></span><span class="b-viewed-product__description-wrap"><span
                                    class="b-viewed-product__label">Rungo</span><span
                                    class="b-viewed-product__description">маячок на ошейник круглый</span></span></a>
                </div>
                <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                 title=""><span class="b-viewed-product__image-wrap"><img
                                    class="b-viewed-product__image" src="images/content/v-cleancat.png" alt="CleanCat"
                                    title="CleanCat"/></span><span class="b-viewed-product__description-wrap"><span
                                    class="b-viewed-product__label">CleanCat</span><span
                                    class="b-viewed-product__description">наполнитель для кошачьего туалета силик что-то там</span></span></a>
                </div>
                <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                 title=""><span class="b-viewed-product__image-wrap"><img
                                    class="b-viewed-product__image" src="images/content/v-pet-hobby.png" alt="Pet Hobby"
                                    title="Pet Hobby"/></span><span class="b-viewed-product__description-wrap"><span
                                    class="b-viewed-product__label">Pet Hobby</span><span
                                    class="b-viewed-product__description">пуходёрка пластмассовая малая с каплечто-то там</span></span></a>
                </div>
                <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                 title=""><span class="b-viewed-product__image-wrap"><img
                                    class="b-viewed-product__image" src="images/content/v-pro-plan.png" alt="ПроПлан"
                                    title="ПроПлан"/></span><span class="b-viewed-product__description-wrap"><span
                                    class="b-viewed-product__label">ПроПлан</span><span
                                    class="b-viewed-product__description">корм для кастрированных / стерилизованных кого-то</span></span></a>
                </div>
                <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                 title=""><span class="b-viewed-product__image-wrap"><img
                                    class="b-viewed-product__image" src="images/content/v-beaver-boardyard.png"
                                    alt="Бобровый дворик" title="Бобровый дворик"/></span><span
                                class="b-viewed-product__description-wrap"><span class="b-viewed-product__label">Бобровый дворик</span><span
                                    class="b-viewed-product__description">ас-зоо домик №1 султан желтый</span></span></a>
                </div>
                <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                 title=""><span class="b-viewed-product__image-wrap"><img
                                    class="b-viewed-product__image" src="images/content/v-hills2.png" alt="Хиллс"
                                    title="Хиллс"/></span><span class="b-viewed-product__description-wrap"><span
                                    class="b-viewed-product__label">Хиллс</span><span
                                    class="b-viewed-product__description">корм для собак крупных пород с курицей</span></span></a>
                </div>
                <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                 title=""><span class="b-viewed-product__image-wrap"><img
                                    class="b-viewed-product__image" src="images/content/v-pet-max.png" alt="Petmax"
                                    title="Petmax"/></span><span class="b-viewed-product__description-wrap"><span
                                    class="b-viewed-product__label">Petmax</span><span
                                    class="b-viewed-product__description">подставка с мисками металл</span></span></a>
                </div>
            </div>
        </section>
    </div>
</div>
