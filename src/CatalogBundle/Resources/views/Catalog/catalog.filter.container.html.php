<?php
/**
 * @var Request                               $request
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult                   $productSearchResult
 * @var PhpEngine                             $view
 * @var CMain                                 $APPLICATION
 */

use Bitrix\Main\Grid\Declension;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Filter\ActionsFilter;
use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;
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
        'CACHE_TIME'   => 10,
    ],
    null,
    ['HIDE_ICONS' => 'Y']
);

$filterCollection = $catalogRequest->getCategory()->getFilters();
$count = $productSearchResult->getResultSet()->getTotalHits(); ?>
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
        <div class="b-filter__title">Фильтры</div>
    </div>
    <div class="b-filter__wrapper b-filter__wrapper--scroll">
        <form class="b-form js-filter-form" action="<?= $APPLICATION->GetCurDir() ?>">
            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:catalog.filter.backLink.html.php',
                [
                    'category' => $category,
                ]
            ) ?>
            <div class="b-filter__block b-filter__block--reset js-reset-link-block"
                <?= $filterCollection->hasCheckedFilter() ? 'style="display:block"' : '' ?>>
                <a class="b-link b-link--reset js-reset-filter"
                   href="<?= $APPLICATION->GetCurDir() ?>"
                   title="Сбросить фильтры">Сбросить фильтры</a>
            </div>
            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:catalog.filter.category.list.html.php',
                [
                    'category' => $category,
                ]
            ) ?>
            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:catalog.filter.list.html.php',
                [
                    'filters' => $filterCollection->getFiltersToShow(),
                ]
            ) ?>
            <div class="b-filter__block b-filter__block--discount js-discount-mobile-here">
            </div>
        </form>
    </div>
    <div class="b-filter__bottom">
        <a class="b-filter__button" href="javascript:void(0);" title="">
            Показать <?= $count . ' ' . WordHelper::declension($count, ['товар', 'товара', 'товаров']) ?>
        </a>
    </div>
</aside>
<main class="b-catalog__main" role="main" data-url="/ajax/catalog/product-info/">
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
                    <span class="b-catalog-filter__label b-catalog-filter__label--amount"><?= $count . (new Declension(' товар',
                            ' товара', ' товаров'))->get($count) ?></span>
                    <?= $view->render(
                        'FourPawsCatalogBundle:Catalog:catalog.filter.sorts.html.php',
                        [
                            'sorts' => $catalogRequest->getSorts(),
                        ]
                    ) ?>
                    <?php
                    /**
                     * @var FilterBase $filter
                     */
                    foreach ($filterCollection->getIterator() as $filter) {
                        if (!($filter instanceof ActionsFilter)) {
                            continue;
                        }
                        if (!$filter->hasAvailableVariants()) {
                            continue;
                        }
                        ?>
                        <span class="b-catalog-filter__discount js-discount-desktop-here">
                            <ul class="b-filter-link-list b-filter-link-list--filter js-discount-checkbox js-filter-checkbox">
                                <?php foreach ($filter->getAvailableVariants() as $id => $variant) {
                                    ?>
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
                                    <?php
                                } ?>
                            </ul>
                        </span>
                        <?php
                    } ?>
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
        $countItems = $productSearchResult->getProductCollection()->count();
        foreach ($productSearchResult->getProductCollection() as $product) {
            $i++;

            $APPLICATION->IncludeComponent(
                'fourpaws:catalog.element.snippet',
                '',
                ['PRODUCT' => $product],
                null,
                ['HIDE_ICONS' => 'Y']
            );

            if ($catalogRequest->getCategory()->isLanding() && !empty($catalogRequest->getCategory()->getUfLandingBanner())) {
                if ($i === 3 || ($i === $countItems && $i < 3)) {
                    ?>
                    <div class="b-fleas-protection-banner b-tablet">
                        <?= htmlspecialcharsback($catalogRequest->getCategory()->getUfLandingBanner()) ?>
                    </div>
                    <?php
                }
                if ($i === 4 || ($i === $countItems && $i < 4)) {
                    ?>
                    <div class="b-fleas-protection-banner">
                        <?= htmlspecialcharsback($catalogRequest->getCategory()->getUfLandingBanner()) ?>
                    </div>
                    <?php
                }
            }
        } ?>
    </div>
    <div class="b-line b-line--catalog-filter"></div>
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
