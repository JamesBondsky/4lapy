<?php
/**
 * @var Request $request
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult $productSearchResult
 * @var PhpEngine $view
 * @var CMain $APPLICATION
 */

use Bitrix\Main\Grid\Declension;
use FourPaws\Catalog\Model\Category;
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

/**
 * @var Category $category
 */
$category = $APPLICATION->IncludeComponent(
    'fourpaws:catalog.category',
    '',
    [
        'SECTION_CODE' => $catalogRequest->getCategory()->getCode(),
        'SET_TITLE'    => 'Y',
        'CACHE_TIME'   => 10
    ],
    null,
    ['HIDE_ICONS' => 'Y']
);
?>
<div class="b-catalog__wrapper-title b-catalog__wrapper-title--filter">
    <?php
    $APPLICATION->IncludeComponent(
        'fourpaws:breadcrumbs',
        '',
        [
            'IBLOCK_SECTION' => $category,
        ],
        null,
        ['HIDE_ICONS' => 'Y']
    );
    ?>
    <h1 class="b-title b-title--h1 b-title--catalog-filter">
        <?= implode(' ', [$category->getName(), $category->getParent()->getSuffix()]) ?>
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
            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:catalog.filter.backLink.html.php',
                [
                    'category' => $category,
                ]
            ) ?>
            <div class="b-filter__block b-filter__block--reset js-reset-link-block"><a
                        class="b-link b-link--reset js-reset-filter" href="javascript:void(0);"
                        title="Сбросить фильтры">Сбросить фильтры</a>
            </div>
            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:catalog.filter.category.list.html.php',
                [
                    'category' => $category,
                ]
            ) ?>
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
                    $totalString .= (new Declension(' товар', ' товара', ' товаров'))->get(
                        $productSearchResult->getResultSet()->getTotalHits()
                    );
                    ?>
                    <span class="b-catalog-filter__label b-catalog-filter__label--amount"><?= $totalString ?></span>
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
                              <option value="<?= $sort->getValue() ?>" <?= $sort->isSelected(
                              ) ? 'selected="selected"' : '' ?>><?= $sort->getName() ?></option>
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
                <div class="b-catalog-filter__type-part">
                    <a class="b-link b-link--type active js-link-type-normal"
                       href="javascript:void(0);" title="">
                            <span class="b-icon b-icon--type">
                                <?= new SvgDecorator('icon-catalog-normal', 20, 20) ?>
                            </span>
                    </a>
                    <a class="b-link b-link--type js-link-type-line" href="javascript:void(0);" title="">
                            <span class="b-icon b-icon--type">
                                <?= new SvgDecorator('icon-catalog-line', 20, 20) ?>
                            </span>
                    </a>
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
    <?php $APPLICATION->IncludeComponent(
        'bitrix:system.pagenavigation',
        'pagination',
        [
            'NAV_TITLE'      => '',
            'NAV_RESULT'     => $productSearchResult->getProductCollection()->getCdbResult(),
            'SHOW_ALWAYS'    => false,
            'PAGE_PARAMETER' => 'page',
        ],
        $component,
        [
            'HIDE_ICONS' => 'Y',
        ]
    ); ?>
</main>
