<?php

use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult                   $productSearchResult
 * @var GoogleEcommerceService                $ecommerceService
 * @var PhpEngine                             $view
 * @var CategoryCollection                    $categories
 * @var string                                $retailRocketViewScript
 * @var CMain                                 $APPLICATION
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $APPLICATION;
$APPLICATION->SetTitle($catalogRequest->getCategory()->getName()); ?>
    <div class="b-catalog">
        <?php if ($productSearchResult && !$productSearchResult->getProductCollection()->isEmpty()) { ?>
            <div class="b-container b-container--catalog-filter">
                <?= $view->render(
                    'FourPawsCatalogBundle:Catalog:search.filter.container.html.php',
                    \compact('catalogRequest', 'ecommerceService', 'productSearchResult', 'retailRocketViewScript')
                ) ?>
            </div>
        <?php } else { ?>
            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:search.empty.html.php', \compact('catalogRequest', 'categories')
            ) ?>
        <?php }

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
    </div>
<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
