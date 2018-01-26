<?php
/**
 * @var Request $request
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult $productSearchResult
 * @var PhpEngine $view
 * @var CMain $APPLICATION
 */

use Bitrix\Main\Grid\Declension;
use Bitrix\Main\Web\Uri;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Filter\ActionsFilter;
use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\CatalogBundle\ParamConverter\Catalog\AbstractCatalogRequestConverter;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\PhpEngine;

global $APPLICATION;

/**
 * @var Category $category
 */
$category = $catalogRequest->getCategory();

$filterCollection = $catalogRequest->getCategory()->getFilters();

$queryUrl = new Uri($APPLICATION->GetCurDir());
$queryUrl->addParams([AbstractCatalogRequestConverter::SEARCH_STRING => $catalogRequest->getSearchString()]);
?>
<aside class="b-filter b-filter--popup js-filter-popup">
    <div class="b-filter__top">
        <a class="b-filter__close js-close-filter" href="javascript:void(0);" title=""></a>
        <div class="b-filter__title">
            Фильтры
        </div>
    </div>
    <div class="b-filter__wrapper b-filter__wrapper--scroll">
        <form class="b-form js-filter-form" action="<?= $APPLICATION->GetCurDir() ?>">
            <div class="b-filter__block b-filter__block--reset js-reset-link-block"
                <?= $filterCollection->hasCheckedFilterBrand() ? 'style="display:block"' : '' ?>>
                <a class="b-link b-link--reset js-reset-filter"
                   href="<?= $queryUrl->getUri() ?>"
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
                    'filters' => $filterCollection->getVisibleFilters()
                ]
            ) ?>
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
        <a class="b-link b-link--open-filter js-permutation-filter js-open-filter"
           href="javascript:void(0);"
           title="Открыть фильтры">
            <span class="b-icon b-icon--open-filter">
                <?= new SvgDecorator('icon-open-filter', 19, 14) ?>
            </span>
        </a>
        <div class="b-catalog-filter__filter-part">
            <div class="b-line b-line--sort-desktop"></div>
            <div class="b-catalog-filter__row b-catalog-filter__row--sort">
                <div class="b-catalog-filter__sort-part js-permutation-mobile-here">
                    <?php
                    
                    $totalString = $productSearchResult->getResultSet()->getTotalHits();
                    $totalString .= (new Declension(' товар', ' товара', ' товаров'))->get(
                        $productSearchResult->getResultSet()->getTotalHits()
                    );
                    ?>
                    <span class="b-catalog-filter__label b-catalog-filter__label--amount"><?= $totalString ?></span>
                    <?= $view->render(
                        'FourPawsCatalogBundle:Catalog:catalog.filter.sorts.html.php',
                        [
                            'sorts' => $catalogRequest->getSorts()
                        ]
                    ) ?>
                    <?php
                    /**
                     * @var FilterBase $filter
                     */
                    foreach ($filterCollection->getVisibleFilters() as $filter) {
                        if (!$filter->hasAvailableVariants()) {
                            continue;
                        }
                        if (!($filter instanceof ActionsFilter)) {
                            continue;
                        }
                        ?>
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
            'AJAX_MODE'      => 'Y'
        ],
        $component,
        [
            'HIDE_ICONS' => 'Y',
        ]
    ); ?>
</main>
