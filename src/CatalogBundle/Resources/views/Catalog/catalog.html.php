<?php

use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var ChildCategoryRequest   $catalogRequest
 * @var ProductSearchResult    $productSearchResult
 * @var PhpEngine              $view
 * @var GoogleEcommerceService $ecommerceService
 * @var CatalogLandingService  $landingService
 * @var string                 $retailRocketViewScript
 * @var CMain                  $APPLICATION
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
global $APPLICATION;

/**
 * $category->isLanding() - старый лендинг, типа "Защита от блох и клещей"
 * $catalogRequest->isLanding() - новый лендинг, типа fashion
 */
$category = $catalogRequest->getCategory();

if ($category->isLanding() || $catalogRequest->isLanding()) {
    echo $view->render('FourPawsCatalogBundle:Catalog:landing.slider.html.php', \compact('category'));
}

if ($catalogRequest->isLanding()) {
    echo $view->render('FourPawsCatalogBundle:Catalog:landing.header.html.php',\compact('catalogRequest'));

    echo '<div class="b-catalog js-preloader-fix"><div class="b-container b-container--catalog-filter">';
} else { ?>
    <div class="b-catalog js-preloader-fix">
    <div class="b-container b-container--catalog-filter">
<?php }

echo $view->render(
    'FourPawsCatalogBundle:Catalog:catalog.filter.container.html.php',
    \compact('catalogRequest', 'productSearchResult', 'ecommerceService', 'request', 'landingService', 'retailRocketViewScript')
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

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
