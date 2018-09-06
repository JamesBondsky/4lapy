<?php

use Bitrix\Main\Grid\Declension;
use Bitrix\Main\Web\Uri;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\CatalogBundle\ParamConverter\Catalog\AbstractCatalogRequestConverter;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Helpers\WordHelper;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var Request                               $request
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult                   $productSearchResult
 * @var GoogleEcommerceService                $ecommerceService
 * @var string                                $retailRocketViewScript
 * @var PhpEngine                             $view
 * @var CMain                                 $APPLICATION
 */

echo $retailRocketViewScript;

global $APPLICATION;

$filterCollection = $catalogRequest->getCategory()->getFilters();
$count = $productSearchResult->getResultSet()->getTotalHits();

$queryUrl = new Uri($APPLICATION->GetCurDir());
$queryUrl->addParams([AbstractCatalogRequestConverter::SEARCH_STRING => $catalogRequest->getSearchString()]); ?>
<div class="b-catalog__wrapper-title b-catalog__wrapper-title--filter">
    <h1 class="b-title b-title--h1 b-title--catalog-filter"><?= $catalogRequest->getCategory()->getName() ?></h1>
    <?php if ($catalogRequest->getSearchString() !== $productSearchResult->getQuery()) { ?>
        <p class="b-title b-title--result">
            Возможно, вы имели ввиду «<span><?= $productSearchResult->getQuery() ?></span>»
        </p>
    <?php } ?>
</div>
<aside class="b-filter b-filter--popup js-filter-popup">
    <div class="b-filter__top">
        <a class="b-filter__close js-close-filter" href="javascript:void(0);" title=""></a>
        <div class="b-filter__title">
            Фильтры
        </div>
    </div>
    <div class="b-filter__wrapper b-filter__wrapper--scroll">
        <form class="b-form js-filter-form" action="<?= $APPLICATION->GetCurDir() ?>"
              data-url="/ajax/catalog/product-info/count-by-filter-search/">
            <div class="b-filter__block b-filter__block--reset js-reset-link-block"
                <?= $filterCollection->hasCheckedFilter() ? 'style="display:block"' : '' ?>>
                <a class="b-link b-link--reset js-reset-filter"
                   href="<?= $queryUrl->getUri() ?>"
                   title="Сбросить фильтры">Сбросить фильтры</a>
            </div>
            <div class="js-filter-input">
                <input type="hidden" name="query" value="<?= urlencode($productSearchResult->getQuery()) ?>">
            </div>
            <?php $filterToShow = $filterCollection->getFiltersToShow();
            $filterActions = $filterCollection->getActionsFilter(); ?>

            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:catalog.filter.list.html.php',
                [
                    'filters' => $filterToShow,
                ]
            ) ?>
            <div class="b-filter__block b-filter__block--discount js-discount-mobile-here">
                <?php
                /**
                 * @var FilterBase $filter
                 */
                foreach ($filterActions as $filter) { ?>
                    <ul class="b-filter-link-list b-filter-link-list--filter js-discount-checkbox js-filter-checkbox">
                        <?php foreach ($filter->getAvailableVariants() as $id => $variant) { ?>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label">
                                    <input class="b-filter-link-list__checkbox js-discount-input js-filter-control"
                                           type="checkbox"
                                           name="<?= $filter->getFilterCode() ?>"
                                           value="<?= $variant->getValue() ?>"
                                           id="<?= $filter->getFilterCode() ?>-<?= $id ?>"
                                        <?= $variant->isChecked() ? 'checked' : '' ?>/>
                                    <a class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                       href="javascript:void(0);"
                                       title="<?= $filter->getName() ?>">
                                        <?= $filter->getName() ?>
                                    </a>
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </div>
        </form>
    </div>
    <div class="b-filter__bottom">
        <a class="b-filter__button" href="javascript:void(0);" title="">
            Показать <?= $count . ' ' . WordHelper::declension($count, [
                'товар',
                'товара',
                'товаров'
            ]) ?>
        </a>
    </div>
</aside>
<main class="b-catalog__main" role="main" data-url="/ajax/catalog/product-info/">
    <div class="b-catalog-filter js-permutation-desktop-here">
        <div class="b-catalog-filter__filter-part">
            <dl class="b-catalog-filter__row">
                <dt class="b-catalog-filter__label b-catalog-filter__label--result">
                    По запросу «<span><?= $productSearchResult->getQuery() ?></span>» мы нашли
                </dt>
            </dl>
            <div class="b-line b-line--sort-desktop">
            </div>
            <div class="b-catalog-filter__row b-catalog-filter__row--sort">
                <div class="b-catalog-filter__sort-part js-permutation-mobile-here">
                    <a class="b-link b-link--open-filter js-permutation-filter js-open-filter"
                       href="javascript:void(0);"
                       title="Открыть фильтры">
                        <span class="b-icon b-icon--open-filter">
                            <?= new SvgDecorator('icon-open-filter', 19, 14) ?>
                        </span>
                    </a>
                    <span class="b-catalog-filter__label b-catalog-filter__label--amount"><?= $count
                                                                                              . (new Declension(' товар', ' товара', ' товаров'))->get($count) ?></span>
                    <?= $view->render(
                        'FourPawsCatalogBundle:Catalog:catalog.filter.sorts.html.php',
                        [
                            'sorts' => $catalogRequest->getSorts(),
                        ]
                    );

                    /**
                     * @var FilterBase $filter
                     */
                    foreach ($filterActions as $filter) { ?>
                        <span class="b-catalog-filter__discount js-discount-desktop-here">
                            <ul class="b-filter-link-list b-filter-link-list--filter js-discount-checkbox js-filter-checkbox">
                                <?php foreach ($filter->getAvailableVariants() as $id => $variant) { ?>
                                    <li class="b-filter-link-list__item">
                                        <label class="b-filter-link-list__label">
                                            <input class="b-filter-link-list__checkbox js-discount-input js-filter-control"
                                                   type="checkbox"
                                                   name="<?= $filter->getFilterCode() ?>"
                                                   value="<?= $variant->getValue() ?>"
                                                   id="<?= $filter->getFilterCode() ?>-<?= $id ?>"
                                                <?= $variant->isChecked() ? 'checked' : '' ?>/>
                                            <a class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                               href="javascript:void(0);"
                                               title="<?= $filter->getName() ?>">
                                                <?= $filter->getName() ?>
                                            </a>
                                        </label>
                                    </li>
                                <?php } ?>
                            </ul>
                        </span>
                    <?php } ?>
                </div>
                <div class="b-catalog-filter__type-part">
                    <a class="b-link b-link--type active js-link-type-normal" href="javascript:void(0);" title="">
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

        $productCollection = $productSearchResult->getProductCollection();
        echo $ecommerceService->renderScript(
            $ecommerceService->buildImpressionsFromProductCollection($productCollection, 'Поиск'),
            true
        );

        foreach ($productCollection as $product) {
            $APPLICATION->IncludeComponent(
                'fourpaws:catalog.element.snippet',
                '',
                [
                    'PRODUCT'               => $product,
                    'GOOGLE_ECOMMERCE_TYPE' => 'Поиск'
                ]
            );
        } ?>
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
            'AJAX_MODE'      => 'Y',
        ],
        null,
        [
            'HIDE_ICONS' => 'Y',
        ]
    ); ?>
</main>
