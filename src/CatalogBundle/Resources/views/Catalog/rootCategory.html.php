<?php
/**
 * @var RootCategoryRequest $rootCategoryRequest
 * @var CMain $APPLICATION
 */

use FourPaws\App\Templates\ViewsEnum;
use FourPaws\Catalog\Model\Category;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use Symfony\Component\HttpFoundation\Request;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $APPLICATION;

$APPLICATION->AddChainItem('Каталог', '/catalog/');

/**
 * @var Category $category
 * @var Request $request
 */
$category = $APPLICATION->IncludeComponent(
    'fourpaws:catalog.category.root',
    '',
    [
        'SECTION_CODE' => $rootCategoryRequest->getCategorySlug(),
        'SET_TITLE' => 'Y',
        'CACHE_TIME' => 10
    ],
    null,
    ['HIDE_ICONS' => 'Y']
);

?>
    <div class="b-catalog">
        <div class="b-container b-container--catalog-main">
            <div class="b-catalog__wrapper-title">
                <?php
                $APPLICATION->IncludeComponent(
                    'fourpaws:breadcrumbs',
                    '',
                    [
                        'IBLOCK_SECTION' => $category,
                        'ADDITIONAL_CLASS' => 'b-breadcrumbs--catalog-main'
                    ],
                    null,
                    ['HIDE_ICONS' => 'Y']
                );
                ?>
                <h1 class="b-title b-title--h1"><?= $category->getCanonicalName() ?></h1>
            </div>
            <?php

            $APPLICATION->ShowViewContent(ViewsEnum::CATALOG_CATEGORY_ROOT_LEFT_BLOCK);
            $APPLICATION->ShowViewContent(ViewsEnum::CATALOG_CATEGORY_ROOT_MAIN_BLOCK);

            /**
             * Популярные бренды
             */
            $APPLICATION->IncludeFile(
                'blocks/components/popular_brands.php',
                [
                    'categoryRequest' => $rootCategoryRequest,
                ],
                [
                    'SHOW_BORDER' => false,
                    'NAME' => 'Блок популярных брендов',
                    'MODE' => 'php',
                ]
            );
            ?>
        </div>
        <?php
        /**
         * Просмотренные товары
         */
        $APPLICATION->IncludeComponent(
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
        );
    ?></div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
