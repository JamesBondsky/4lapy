<?php
/**
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult $productSearchResult
 * @var PhpEngine $view
 * @var CategoryCollection $categories
 * @var CMain $APPLICATION
 */

use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\Templating\PhpEngine;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $APPLICATION;
$APPLICATION->SetTitle($catalogRequest->getCategory()->getName());
?>
    <div class="b-catalog">
        <?php if ($productSearchResult && !$productSearchResult->getProductCollection()->isEmpty()) { ?>
            <div class="b-container b-container--catalog-filter">
                <?= $view->render(
                    'FourPawsCatalogBundle:Catalog:search.filter.container.html.php',
                    [
                        'catalogRequest'      => $catalogRequest,
                        'productSearchResult' => $productSearchResult,
                    ]
                ) ?>
            </div>
        <?php } else { ?>
            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:search.empty.html.php',
                [
                    'catalogRequest' => $catalogRequest,
                    'categories' => $categories
                ]
            ) ?>
        <?php } ?>
        <?php

        /**
         * Просмотренные товары
         */
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
        );

        ?>
        <div class="b-preloader b-preloader--catalog">
            <div class="b-preloader__spinner">
                <img class="b-preloader__image" src="/static/build/images/inhtml/spinner.svg" alt="spinner" title=""/>
            </div>
        </div>
    </div>
<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
