<?php

use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use FourPaws\EcommerceBundle\Service\DataLayerService;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\Templating\PhpEngine;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * @var ChildCategoryRequest   $catalogRequest
 * @var ProductSearchResult    $productSearchResult
 * @var PhpEngine              $view
 * @var DataLayerService       $dataLayerService
 * @var GoogleEcommerceService $ecommerceService
 * @var CatalogLandingService  $landingService
 * @var string                 $retailRocketViewScript
 * @var Product|bool           $productWithMinPrice
 * @var CMain                  $APPLICATION
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
global $APPLICATION;

/**
 * $category->isLanding() - старый лендинг, типа "Защита от блох и клещей"
 * $catalogRequest->isLanding() - новый лендинг, типа fashion
 */
$category = $catalogRequest->getCategory();
$filterSet = $catalogRequest->getFilterSet();

if ($category->isLanding() || $catalogRequest->isLanding()) {
    echo $view->render('FourPawsCatalogBundle:Catalog:landing.slider.html.php', \compact('category'));
} else {
    $filterName = 'catalogSliderFilter';
    global ${$filterName};
    ${$filterName} = ['PROPERTY_SECTION' => $catalogRequest->getCategory()->getId()];
    $APPLICATION->IncludeComponent('bitrix:news.list',
        'index.slider',
        [
            'COMPONENT_TEMPLATE' => 'index.slider',
            'IBLOCK_TYPE' => IblockType::PUBLICATION,
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS),
            'NEWS_COUNT' => '20',
            'SORT_BY1' => 'SORT',
            'SORT_ORDER1' => 'ASC',
            'SORT_BY2' => 'ACTIVE_FROM',
            'SORT_ORDER2' => 'DESC',
            'FILTER_NAME' => $filterName,
            'FIELD_CODE' => [
                0 => 'NAME',
                1 => 'PREVIEW_PICTURE',
                2 => 'DETAIL_PICTURE',
                3 => '',
            ],
            'PROPERTY_CODE' => [
                0 => 'LINK',
                1 => 'IMG_TABLET',
                2 => 'BACKGROUND',
            ],
            'CHECK_DATES' => 'Y',
            'DETAIL_URL' => '',
            'AJAX_MODE' => 'N',
            'AJAX_OPTION_JUMP' => 'N',
            'AJAX_OPTION_STYLE' => 'N',
            'AJAX_OPTION_HISTORY' => 'N',
            'AJAX_OPTION_ADDITIONAL' => '',
            'CACHE_TYPE' => 'A',
            'CACHE_TIME' => '36000000',
            'CACHE_FILTER' => 'Y',
            'CACHE_GROUPS' => 'N',
            'PREVIEW_TRUNCATE_LEN' => '',
            'ACTIVE_DATE_FORMAT' => '',
            'SET_TITLE' => 'N',
            'SET_BROWSER_TITLE' => 'N',
            'SET_META_KEYWORDS' => 'N',
            'SET_META_DESCRIPTION' => 'N',
            'SET_LAST_MODIFIED' => 'N',
            'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
            'ADD_SECTIONS_CHAIN' => 'N',
            'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
            'PARENT_SECTION' => '',
            'PARENT_SECTION_CODE' => '',
            'INCLUDE_SUBSECTIONS' => 'N',
            'STRICT_SECTION_CHECK' => 'N',
            'DISPLAY_DATE' => 'N',
            'DISPLAY_NAME' => 'N',
            'DISPLAY_PICTURE' => 'N',
            'DISPLAY_PREVIEW_TEXT' => 'N',
            'PAGER_TEMPLATE' => '',
            'DISPLAY_TOP_PAGER' => 'N',
            'DISPLAY_BOTTOM_PAGER' => 'N',
            'PAGER_TITLE' => '',
            'PAGER_SHOW_ALWAYS' => 'N',
            'PAGER_DESC_NUMBERING' => 'N',
            'PAGER_DESC_NUMBERING_CACHE_TIME' => '',
            'PAGER_SHOW_ALL' => 'N',
            'PAGER_BASE_LINK_ENABLE' => 'N',
            'SET_STATUS_404' => 'N',
            'SHOW_404' => 'N',
            'MESSAGE_404' => '',
        ],
        false,
        ['HIDE_ICONS' => 'Y']);
}

if ($catalogRequest->isLanding()) {
    echo $view->render('FourPawsCatalogBundle:Catalog:landing.header.html.php', \compact('catalogRequest'));

    echo '<div class="b-catalog js-preloader-fix"><div class="b-container b-container--catalog-filter">';
} else { ?>
    <div class="b-catalog js-preloader-fix">
    <div class="b-container b-container--catalog-filter">
<?php }

echo $view->render(
    'FourPawsCatalogBundle:Catalog:catalog.filter.container.html.php',
    \compact('catalogRequest', 'productSearchResult', 'ecommerceService', 'request', 'landingService', 'dataLayerService', 'retailRocketViewScript', 'productWithMinPrice', 'filterSet')
);

if ($catalogRequest->isLanding()) {
    echo '</div></div>';
    if ($category->isShowFitting()) {
        echo $view->render('FourPawsCatalogBundle:Catalog:landing.fitting.html.php');
    }

    if ($category->getLandingArticlesSectionId()) {
        echo $view->render('FourPawsCatalogBundle:Catalog:landing.articles.html.php', ['sectionId' => $category->getLandingArticlesSectionId()]);
    }

    if ($category->getFormTemplate()) {
        echo $view->render('FourPawsCatalogBundle:Catalog:landing.form.html.php', ['formTemplate' => $category->getFormTemplate()]);
    }

    if ($category->getRecommendedProductIds()) {
        $APPLICATION->IncludeComponent('fourpaws:catalog.snippet.list', 'catalog.recommended.products', [
            'COUNT'          => 12,
            'PRODUCT_FILTER' => [
                'ID' => $category->getRecommendedProductIds()
            ],
            'TITLE'          => 'Мы рекомендуем',
        ], false, ['HIDE_ICONS' => 'Y']);
    }

} else { ?>
    </div>
    <?php if ($category->isLanding()) {
        echo $view->render('FourPawsCatalogBundle:Catalog:old.landing.catalog.footer.html.php', \compact('category'));
    }

    $APPLICATION->IncludeComponent(
        'bitrix:main.include',
        '',
        [
            'AREA_FILE_SHOW' => 'file',
            'PATH'           => '/local/include/blocks/viewed_products.php',
            'EDIT_TEMPLATE'  => '',
        ],
        null,
        [
            'HIDE_ICONS' => 'Y',
        ]
    ); ?>
    </div>
<?php }


if ($filterSet) {
    $APPLICATION->SetTitle($filterSet->getTitle());
    $APPLICATION->SetPageProperty('title', $filterSet->getH1());
    $APPLICATION->SetPageProperty('description', $filterSet->getDescription());
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
