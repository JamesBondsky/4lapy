<?php
/**
 * @var Request                               $request
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult                   $productSearchResult
 * @var PhpEngine                             $view
 * @var CMain                                 $APPLICATION
 */

use Bitrix\Main\Grid\Declension;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Filter\PriceFilter;
use FourPaws\Catalog\Model\Filter\RangeFilterInterface;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\Catalog\Model\Variant;
use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\PhpEngine;

global $APPLICATION;


?>
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
        <h1 class="b-title b-title--h1 b-title--catalog-filter">
            Корм для собак
        </h1>
    </div>
    <aside class="b-filter b-filter--popup js-filter-popup">
        <div class="b-filter__top">
            <a class="b-filter__close js-close-filter" href="javascript:void(0);" title=""></a>
            <div class="b-filter__title">
                Фильтры
            </div>
        </div>
        <div class="b-filter__wrapper b-filter__wrapper--scroll">
            <form class="b-form js-filter-form">
                <div class="b-filter__block b-filter__block--back">
                    <ul class="b-back">
                        <li class="b-back__item">
                            <a class="b-link b-link--back" href="javascript:void(0);"
                               title="Товары для собак">Товары для собак</a>
                        </li>
                    </ul>
                </div>
                <div class="b-filter__block b-filter__block--reset js-reset-link-block"><a
                            class="b-link b-link--reset js-reset-filter" href="javascript:void(0);"
                            title="Сбросить фильтры">Сбросить фильтры</a>
                </div>
                <div class="b-filter__block b-filter__block--select">
                    <h3 class="b-title b-title--filter-header">Категория
                    </h3>
                    <div class="b-select b-select--filter">
                        <ul class="b-filter-link-list b-filter-link-list--filter b-filter-link-list--select-filter js-accordion-filter-select js-filter-checkbox">
                            <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                    href="javascript:void(0);"
                                                                    title="Сухой">Сухой</a>
                            </li>
                            <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                    href="javascript:void(0);"
                                                                    title="Консервы">Консервы</a>
                            </li>
                            <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                    href="javascript:void(0);"
                                                                    title="Кормовая добавка и молоко">Кормовая
                                    добавка и
                                    молоко</a>
                            </li>
                            <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                    href="javascript:void(0);" title="Диетический">Диетический</a>
                            </li>
                            <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                    href="javascript:void(0);"
                                                                    title="Консервы">Консервы</a>
                            </li>
                            <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                    href="javascript:void(0);"
                                                                    title="Кормовая добавка и молоко">Кормовая
                                    добавка и
                                    молоко</a>
                            </li>
                            <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                    href="javascript:void(0);" title="Диетический">Диетический</a>
                            </li>
                        </ul>
                        <a class="b-link b-link--filter-more b-link--filter-select js-open-filter-all"
                           href="javascript:void(0);" title="Показать все">Показать все <span
                                    class="b-icon b-icon--more">
                      <svg class="b-icon__svg" viewBox="0 0 10 10 " width="10px" height="10px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-arrow-down">
                        </use>
                      </svg></span></a>
                    </div>
                </div>
                <?php

                $filterCollection = $catalogRequest->getCategory()->getFilters();

                foreach ($filterCollection->getVisibleFilters() as $filter) {
                    if ($filter instanceof PriceFilter) {
                        continue;
                    }
                    if ($filter instanceof RangeFilterInterface) {
                        continue;
                    }
                    if (!$filter->hasAvailableVariants()) {
                        continue;
                    }
                    if ($filter instanceof FilterBase) {
                        ?>
                        <div class="b-filter__block">
                            <h3 class="b-title b-title--filter-header">
                                <?= $filter->getName() ?>
                            </h3>
                            <ul class="b-filter-link-list b-filter-link-list--filter js-filter-checkbox">
                                <?php
                                /**
                                 * @var Variant $variant
                                 */
                                foreach ($filter->getAllVariants() as $id => $variant) {
                                    ?>
                                    <li class="b-filter-link-list__item">
                                        <label class="b-filter-link-list__label">
                                            <input
                                                    class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                                    type="checkbox"
                                                    name="<?= $filter->getFilterCode() ?>"
                                                    value="<?= $variant->getValue() ?>"
                                                    id="<?= $filter->getFilterCode() ?>-<?= $id ?>"/>
                                            <a
                                                    class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                                    href="javascript:void(0);"
                                                    title="<?= $variant->getName() ?>"
                                            ><?= $variant->getName() ?></a>
                                        </label>
                                    </li>
                                    <?php
                                } ?>
                        </div>
                        <?php
                    }
                }
                ?>
                <div class="b-filter__block b-filter__block--discount js-discount-mobile-here">
                </div>
            </form>
        </div>
        <div class="b-filter__bottom"><a class="b-filter__button" href="javascript:void(0);" title="">
                Показать 300 товаров
            </a>
        </div>
    </aside>
    <main class="b-catalog__main" role="main">
        <div class="b-catalog-filter js-permutation-desktop-here">
            <a
                    class="b-link b-link--open-filter js-permutation-filter js-open-filter"
                    href="javascript:void(0);"
                    title="Открыть фильтры"
            >
                <span class="b-icon b-icon--open-filter"><?= new SvgDecorator('icon-open-filter', 19, 14) ?></span>
            </a>
            <div class="b-catalog-filter__filter-part">
                <dl class="b-catalog-filter__row">
                    <dt class="b-catalog-filter__label">Часто ищут:
                    </dt>
                    <dd class="b-catalog-filter__block"><a class="b-link b-link--filter" href="javascript:void(0);"
                                                           title="Hills для взрослых собак среднего размера">Hills
                            для
                            взрослых собак среднего размера</a><a class="b-link b-link--filter"
                                                                  href="javascript:void(0);"
                                                                  title="Chappy для маленьких собак">Chappy для
                            маленьких собак</a>
                    </dd>
                </dl>
                <div class="b-line b-line--sort-desktop">
                </div>
                <div class="b-catalog-filter__row b-catalog-filter__row--sort">
                    <div class="b-catalog-filter__sort-part js-permutation-mobile-here">
                        <?php

                        $totalString = $productSearchResult->getResultSet()->getTotalHits();
                        $totalString .= (new Declension(' товар', ' товара', ' товаров'))->get($productSearchResult->getResultSet()->getTotalHits());
                        ?>
                        <span class="b-catalog-filter__label b-catalog-filter__label--amount"><?=$totalString?></span>
                        <span class="b-catalog-filter__sort"><span
                                    class="b-catalog-filter__label b-catalog-filter__label--sort">Сортировать по</span><span
                                    class="b-select b-select--sort js-filter-select">
                      <select class="b-select__block b-select__block--sort js-filter-select" name="sort">
                          <?php
                          /**
                           * @var Sorting $sort
                           */
                          foreach ($catalogRequest->getSorts() as $sort) {
                              ?>
                              <option value="<?= $sort->getValue() ?>" <?= $sort->isSelected() ? 'selected="selected"' : '' ?>><?= $sort->getName() ?></option>
                              <?php
                          }
                          ?>
                      </select><span class="b-select__arrow"></span></span></span><span
                                class="b-catalog-filter__discount js-discount-desktop-here">
                      <ul class="b-filter-link-list b-filter-link-list--filter js-discount-checkbox js-filter-checkbox">
                        <li class="b-filter-link-list__item">
                          <label class="b-filter-link-list__label"><input
                                      class="b-filter-link-list__checkbox js-discount-input js-filter-control"
                                      type="checkbox" name="filter-discount" value="filter-discount-0"
                                      id="filter-discount-0"/><a
                                      class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                      href="javascript:void(0);" title="Товары со скидкой">Товары со скидкой</a>
                          </label>
                        </li>
                      </ul></span>
                    </div>
                    <div class="b-catalog-filter__type-part"><a
                                class="b-link b-link--type active js-link-type-normal"
                                href="javascript:void(0);" title=""><span
                                    class="b-icon b-icon--type">
                      <svg class="b-icon__svg" viewBox="0 0 20 20 " width="20px" height="20px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-catalog-normal">
                        </use>
                      </svg></span></a><a class="b-link b-link--type js-link-type-line" href="javascript:void(0);"
                                          title=""><span class="b-icon b-icon--type">
                      <svg class="b-icon__svg" viewBox="0 0 20 20 " width="20px" height="20px">
                        <use class="b-icon__use" xlink:href="icons.svg#icon-catalog-line">
                        </use>
                      </svg></span></a>
                    </div>
                </div>
                <div class="b-line b-line--sort-mobile">
                </div>
            </div>
        </div>
        <div class="b-common-wrapper b-common-wrapper--visible js-catalog-wrapper">
            <?php
            foreach ($productSearchResult->getProductCollection() as $product) {
                $APPLICATION->IncludeComponent(
                    'fourpaws:catalog.element.snippet',
                    '',
                    ['PRODUCT' => $product]
                );
            }
            ?>
        </div>
        <div class="b-line b-line--catalog-filter">
        </div>
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
                <li class="b-pagination__item"><a class="b-pagination__link" href="javascript:void(0);"
                                                  title="13">13</a>
                </li>
                <li class="b-pagination__item b-pagination__item--next"><a class="b-pagination__link"
                                                                           href="javascript:void(0);"
                                                                           title="Вперед">Вперед</a>
                </li>
            </ul>
        </div>
    </main>
</div>
