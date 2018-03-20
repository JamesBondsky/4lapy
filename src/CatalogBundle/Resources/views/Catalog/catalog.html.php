<?php
/**
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult $productSearchResult
 * @var PhpEngine $view
 * @var CMain $APPLICATION
 */

use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\Templating\PhpEngine;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php'; ?>
    <div class="b-catalog js-preloader-fix">
        <div class="b-container b-container--catalog-filter">
            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:catalog.filter.container.html.php',
                [
                    'catalogRequest' => $catalogRequest,
                    'productSearchResult' => $productSearchResult,
                ]
            ) ?>
        </div>
        <?php $APPLICATION->IncludeComponent(
            'bitrix:main.include',
            '',
            [
                'AREA_FILE_SHOW' => 'file',
                'PATH' => '/local/include/blocks/viewed_products.php',
                'EDIT_TEMPLATE' => '',
            ],
            null,
            [
                'HIDE_ICONS' => 'Y',
            ]
        ); ?>
        <div class="b-preloader b-preloader--catalog">
            <div class="b-preloader__spinner">
                <img class="b-preloader__image" src="/static/build/images/inhtml/spinner.svg" alt="spinner" title=""/>
            </div>
        </div>
    </div>
<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
