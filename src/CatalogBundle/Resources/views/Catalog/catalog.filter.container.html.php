<?php

use Bitrix\Catalog\Model\Product;
use Bitrix\Main\Grid\Declension;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\FilterSet;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\EcommerceBundle\Service\DataLayerService;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Helpers\WordHelper;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\PhpEngine;

global $APPLICATION;

/**
 * @var Category               $category
 * @var Request                $request
 * @var ChildCategoryRequest   $catalogRequest
 * @var CatalogLandingService  $landingService
 * @var DataLayerService       $dataLayerService
 * @var ProductSearchResult    $productSearchResult
 * @var GoogleEcommerceService $ecommerceService
 * @var FilterSet              $filterSet
 * @var PhpEngine              $view
 * @var string                 $currentPath
 * @var string                 $retailRocketViewScript
 * @var Product|bool           $productWithMinPrice
 * @var CMain                  $APPLICATION
 */

echo $retailRocketViewScript;

$filterCollection = $catalogRequest->getCategory()
                                   ->getFilters();
$count = $productSearchResult->getResultSet()
                             ->getTotalHits();

$category = $APPLICATION->IncludeComponent(
    'fourpaws:catalog.category',
    '',
    [
        'SECTION_CODE'      => $catalogRequest->getCategory()
                                              ->getCode(),
        'SET_TITLE'         => 'Y',
        'CACHE_TIME'        => 10,
        'PRODUCT_COUNT'     => \sprintf(
            '%d %s',
            $count,
            WordHelper::declension($count, [
                    'товар',
                    'товара',
                    'товаров'
                ]
            )
        ),
        'MIN_PRICE_PRODUCT' => $productWithMinPrice
    ],
    null,
    ['HIDE_ICONS' => 'Y']
);

/** @var Category $rootCategory */
$rootCategory = $category->getFullPathCollection()->last();

if (!$catalogRequest->isLanding()) { ?>
    <div class="b-catalog__wrapper-title b-catalog__wrapper-title--filter">
        <?php $APPLICATION->IncludeComponent(
            'fourpaws:breadcrumbs',
            '',
            [
                'IBLOCK_SECTION' => $category,
            ],
            null,
            ['HIDE_ICONS' => 'Y']
        ); ?>
        <h1 class="b-title b-title--h1 b-title--catalog-filter">
            <?php if ($filterSet) { ?>
                <?= $filterSet->getH1() ?>
            <?php } elseif($APPLICATION->GetTitle() == null || $APPLICATION->GetTitle() == '') { ?>
                <?= \in_array($category->getId(), [
                    148,
                    332
                ], true) ? $category->getName() : implode(' ', [
                    $category->getName(),
                    $category->getParent()
                             ->getSuffix()
                ]) ?>
            <?php } else { ?>
                <?= $APPLICATION->GetTitle(); ?>
            <? } ?>
        </h1>
    </div>
<?php } ?>
<aside class="b-filter b-filter--popup js-filter-popup">
    <div class="b-filter__top">
        <a class="b-filter__close js-close-filter" href="javascript:void(0);" title=""></a>
        <div class="b-filter__title">Фильтры</div>
    </div>
    <div class="b-filter__wrapper b-filter__wrapper--scroll">
        <?php /** @todo from server variable */ ?>
        <form class="b-form js-filter-form"
              data-location="<?= $filterSet ? 'ja' : 'nein' ?>"
              action="<?= $catalogRequest->isLanding() ? $catalogRequest->getLandingPath() : $APPLICATION->GetCurDir() ?>"
              data-url="/ajax/catalog/product-info/count-by-filter-list/">
            <div class="b-filter__block" style="visibility: hidden; height: 0;width: 0;overflow: hidden;">
                <ul class="b-filter-link-list b-filter-link-list--filter js-accordion-filter js-filter-checkbox"
                    style="visibility: hidden; height: 0;width: 0;overflow: hidden;">
                    <li class="b-filter-link-list__item"
                        style="visibility: hidden; height: 0;width: 0;overflow: hidden;">
                        <label class="b-filter-link-list__label"
                               style="visibility: hidden; height: 0;width: 0;overflow: hidden;">
                            <input type="checkbox" name="section_id" value="<?= $category->getId() ?>"
                                   checked="checked"
                                   class="b-filter-link-list__checkbox js-filter-control js-checkbox-change"
                                   style="visibility: hidden; height: 0;width: 0;overflow: hidden;">
                        </label>
                    </li>
                </ul>
            </div>
            <?= $view->render('FourPawsCatalogBundle:Catalog:catalog.filter.backLink.html.php', \compact('category', 'catalogRequest')) ?>
            <div class="b-filter__block b-filter__block--reset js-reset-link-block"
                <?= $filterCollection->hasCheckedFilter() ? 'style="display:block"' : '' ?>>
                <a class="b-link b-link--reset js-reset-filter"
                   href="<?= $catalogRequest->getBaseCategoryPath() ?>"
                   title="Сбросить фильтры">Сбросить фильтры</a>
            </div>
            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:catalog.filter.category.list.html.php',
                \compact('category', 'catalogRequest'))
            ?>
            <?php $filterToShow = $filterCollection->getFiltersToShow();
            $filterActions = $filterCollection->getActionsFilter(); ?>
            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:catalog.filter.list.html.php',
                [
                    'filters'          => $filterToShow,
                    'dataLayerService' => $dataLayerService
                ]
            ) ?>
            <div class="b-filter__block b-filter__block--discount js-discount-mobile-here">
                <?php
                /**
                 * @var FilterBase $filter
                 */
                foreach ($filterActions as $filter) { ?>
                    <ul class="b-filter-link-list b-filter-link-list--filter js-discount-checkbox js-filter-checkbox">
                        <?php foreach ($filter->getAvailableVariants() as $id => $variant) {
                            ?>
                            <li class="b-filter-link-list__item">
                                <label class="b-filter-link-list__label"
                                    <?= $variant->getOnclick()
                                        ? \sprintf(
                                            'onclick="%s"',
                                            $variant->getOnclick()
                                        )
                                        : ''
                                    ?>>
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
            Показать <?= \sprintf(
                '%d %s',
                $count,
                WordHelper::declension($count, [
                        'товар',
                        'товара',
                        'товаров'
                    ]
                )
            ) ?>
        </a>
    </div>
</aside>
<main class="b-catalog__main" role="main" data-url="/ajax/catalog/product-info/">
    <? if ($rootCategory && $rootCategory->isShowDelText()) { ?>
        <div class="b-information-message b-information-message--green"><?= Category::DEL_TEXT ?></div>
    <? } ?>
    <div class="b-catalog-filter js-permutation-desktop-here">
        <div class="b-catalog-filter__filter-part">
            <?php $APPLICATION->IncludeComponent(
                'fourpaws:catalog.often.seek',
                '',
                [
                    'SECTION_ID'   => $category->getId(),
                    'LEFT_MARGIN'  => $category->getLeftMargin(),
                    'RIGHT_MARGIN' => $category->getRightMargin(),
                    'DEPTH_LEVEL'  => $category->getDepthLevel(),
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            ); ?>
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
                                                                                              . (new Declension(' товар',
                            ' товара', ' товаров'))->get($count) ?></span>
                    <?= $view->render(
                        'FourPawsCatalogBundle:Catalog:catalog.filter.sorts.html.php',
                        [
                            'sorts'            => $catalogRequest->getSorts(),
                            'dataLayerService' => $dataLayerService,
                        ]
                    ) ?>
                    <?php
                    /**
                     * @var FilterBase $filter
                     */
                    foreach ($filterActions as $filter) { ?>
                        <span class="b-catalog-filter__discount js-discount-desktop-here">
                            <ul class="b-filter-link-list b-filter-link-list--filter js-discount-checkbox js-filter-checkbox">
                                <?php foreach ($filter->getAvailableVariants() as $id => $variant) {
                                    ?>
                                    <li class="b-filter-link-list__item">
                                        <label class="b-filter-link-list__label"
                                            <?= $variant->getOnclick()
                                                ? \sprintf(
                                                    'onclick="%s"',
                                                    $variant->getOnclick()
                                                )
                                                : ''
                                            ?>>
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
            <div class="b-line b-line--sort-mobile"></div>
        </div>
    </div>
    <div class="b-common-wrapper b-common-wrapper--visible js-catalog-wrapper">
        <?php $i = 0;

        $collection = $productSearchResult->getProductCollection();
        $countItems = $collection->count();

        echo $ecommerceService->renderScript(
            $ecommerceService->buildImpressionsFromProductCollection($collection, 'Каталог по питомцу'),
            true
        );

        echo $landingService->replaceLinksToLanding($view->render('FourPawsCatalogBundle:Catalog:catalog.snippet.list.html.php', \compact('collection', 'catalogRequest')), $request); ?>
    </div>
    <div class="b-line b-line--catalog-filter"></div>
    <?php $APPLICATION->IncludeComponent(
        'bitrix:system.pagenavigation',
        'pagination',
        [
            'NAV_TITLE'      => '',
            'NAV_RESULT'     => $productSearchResult->getProductCollection()
                                                    ->getCdbResult(),
            'SHOW_ALWAYS'    => true,
            'PAGE_PARAMETER' => 'page',
            'AJAX_MODE'      => 'Y',
            'BASE_URI'       => $catalogRequest->getBaseCategoryPath(),
        ],
        null,
        [
            'HIDE_ICONS' => 'Y',
        ]
    ); ?>
    <?php if ($filterSet) { ?>
        <div class="b-line b-line--catalog-filter"></div>
        <div class="b-description-tab__column">
            <p>
                <?= $filterSet->getSeoText() ?>
            </p>
        </div>
    <?php } ?>
</main>
