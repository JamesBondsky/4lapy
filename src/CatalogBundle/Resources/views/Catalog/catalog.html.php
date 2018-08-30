<?php

use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var ChildCategoryRequest   $catalogRequest
 * @var ProductSearchResult    $productSearchResult
 * @var PhpEngine              $view
 * @var GoogleEcommerceService $ecommerceService
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
    echo $view->render(
        'FourPawsCatalogBundle:Catalog:landing.header.html.php',
        [
            'landingCollection' => $catalogRequest->getLandingCollection(),
            'currentPath'       => $catalogRequest->getCurrentPath(),
        ]
    );

    echo '<div class="b-catalog js-preloader-fix"><div class="b-container b-container--catalog-filter">';
} else { ?>
    <div class="b-catalog js-preloader-fix">
    <div class="b-container b-container--catalog-filter">
<?php }

echo $view->render(
    'FourPawsCatalogBundle:Catalog:catalog.filter.container.html.php',
    \compact('catalogRequest', 'productSearchResult', 'ecommerceService', 'request')
);

if ($catalogRequest->isLanding()) {
    echo '</div></div>';
    echo $view->render('FourPawsCatalogBundle:Catalog:landing.fitting.html.php');

    if ($category->getFormTemplate()) {
        echo $view->render('FourPawsCatalogBundle:Catalog:landing.form.html.php', ['formTemplate' => $category->getFormTemplate()]);
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
