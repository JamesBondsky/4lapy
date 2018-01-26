<?php

use Bitrix\Main\Application;
use FourPaws\CatalogBundle\Dto\CatalogBrandRequest;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var CatalogBrandRequest $catalogRequest
 * @var ProductSearchResult $productSearchResult
 * @var PhpEngine           $view
 * @var CMain               $APPLICATION
 * @var Request             $request
 */

/** @noinspection PhpUnhandledExceptionInspection */
$httpXRequestWith = Application::getInstance()->getContext()->getServer()->get('HTTP_X_REQUESTED_WITH');
$isAjax           = $httpXRequestWith === 'XMLHttpRequest';
if (!$isAjax) {
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
}

global $APPLICATION;
if (!$isAjax) {
    ?>
    <div class="b-container">
        <?php
        $APPLICATION->IncludeComponent(
            'bitrix:news.detail',
            'fp.17.0.brand',
            [
                'ELEMENT_ID'                => '',
                'ELEMENT_CODE'              => $catalogRequest->getBrand()->getCode(),
                'IBLOCK_TYPE'               => IblockType::CATALOG,
                'IBLOCK_ID'                 => IblockCode::BRANDS,
                'FIELD_CODE'                => [
                    'NAME',
                    'PREVIEW_PICTURE',
                    'DETAIL_TEXT',
                    'DETAIL_PICTURE',
                ],
                'PROPERTY_CODE'             => [],
                'CACHE_GROUPS'              => 'N',
                'CACHE_TIME'                => '43200',
                'CACHE_TYPE'                => 'A',
                'DETAIL_URL'                => '',
                'RESIZE_WIDTH'              => '90',
                'RESIZE_HEIGHT'             => '90',
                'RESIZE_TYPE'               => 'BX_RESIZE_IMAGE_PROPORTIONAL',
                'ACTIVE_DATE_FORMAT'        => 'd.m.Y',
                'ADD_SECTIONS_CHAIN'        => 'N',
                'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                'INCLUDE_SUBSECTIONS'       => 'N',
                'PARENT_SECTION'            => '',
                'PARENT_SECTION_CODE'       => '',
                'PREVIEW_TRUNCATE_LEN'      => '',
                'SET_BROWSER_TITLE'         => 'Y',
                'SET_LAST_MODIFIED'         => 'Y',
                'SET_META_DESCRIPTION'      => 'Y',
                'SET_META_KEYWORDS'         => 'Y',
                'SET_STATUS_404'            => 'Y',
                'SET_TITLE'                 => 'Y',
                'SHOW_404'                  => 'Y',
                'FILE_404'                  => '/404.php',
                'CHECK_DATES'               => 'Y',
                'IBLOCK_URL'                => '',
                'SET_CANONICAL_URL'         => 'N',
                'BROWSER_TITLE'             => 'ELEMENT_META_TITLE',
                'META_KEYWORDS'             => 'ELEMENT_META_KEYWORDS',
                'META_DESCRIPTION'          => 'ELEMENT_META_DESCRIPTION',
                'ADD_ELEMENT_CHAIN'         => 'N',
                'USE_PERMISSIONS'           => 'N',
                'STRICT_SECTION_CHECK'      => 'N',
                'DISPLAY_TOP_PAGER'         => 'N',
                'DISPLAY_BOTTOM_PAGER'      => 'N',
                'MESSAGE_404'               => '',
                
                'PAGER_BASE_LINK_ENABLE'          => 'N',
                'PAGER_DESC_NUMBERING'            => 'N',
                'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                'PAGER_SHOW_ALL'                  => 'N',
                'PAGER_SHOW_ALWAYS'               => 'N',
                'PAGER_TEMPLATE'                  => '',
                'PAGER_TITLE'                     => '',
                'AJAX_MODE'                       => 'N',
                'AJAX_OPTION_ADDITIONAL'          => '',
                'AJAX_OPTION_HISTORY'             => 'N',
                'AJAX_OPTION_JUMP'                => 'N',
                'AJAX_OPTION_STYLE'               => 'N',
            ],
            null,
            [
                'HIDE_ICONS' => 'Y',
            ]
        ); ?>
    </div>
    <?php
} ?>
    <div class="b-catalog">
    <div class="b-container b-container--catalog-filter">
        <?= $view->render(
            'FourPawsCatalogBundle:Catalog:brand.filter.container.html.php',
            [
                'catalogRequest'      => $catalogRequest,
                'productSearchResult' => $productSearchResult,
            ]
        ) ?>
    </div>
<?php

if (!$isAjax) {
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
            <img class="b-preloader__image" src="/static/build/images/inhtml/spinner.svg" alt="spinner" title="" />
        </div>
    </div>
    </div>
    <?php
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
} else {
    ?>
    </div>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
}
die();
