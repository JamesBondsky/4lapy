<?php

use FourPaws\CatalogBundle\Dto\CatalogShareRequest;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use WebArch\BitrixCache\BitrixCache;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Application;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Product;
use FourPaws\EcommerceBundle\Service\DataLayerService;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\SearchService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\PhpEngine;
use FourPaws\CatalogBundle\Service\FilterHelper;

/**
 * @var CatalogShareRequest    $catalogRequest
 * @var ProductSearchResult    $productSearchResult
 * @var SearchService          $searchService
 * @var PhpEngine              $view
 * @var CMain                  $APPLICATION
 * @var Request                $request
 * @var array                  $arParams
 * @var DataLayerService       $dataLayerService
 * @var GoogleEcommerceService $ecommerceService
 */
/** @noinspection PhpUnhandledExceptionInspection */
$httpXRequestWith = Application::getInstance()->getContext()->getServer()->get('HTTP_X_REQUESTED_WITH');
$isAjax           = $httpXRequestWith === 'XMLHttpRequest';
if (!$isAjax) {
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
} else {
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
}

global $APPLICATION;

$APPLICATION->SetPageProperty('PUBLICATION_DETAIL_CONTAINER_1', 'b-container b-container--news-detail');
$APPLICATION->SetPageProperty('PUBLICATION_DETAIL_CONTAINER_2', 'b-detail-page');

if (!$isAjax) {
    $elementId = $APPLICATION->IncludeComponent(
        'fourpaws:shares.detail',
        '',
        [
            'IBLOCK_TYPE'          => $arParams['IBLOCK_TYPE'],
            'IBLOCK_ID'            => $arParams['IBLOCK_ID'],
            'FIELD_CODE'           => $arParams['DETAIL_FIELD_CODE'],
            'ELEMENT_ID'           => $arParams['VARIABLES']['ELEMENT_ID'],
            'ELEMENT_CODE'         => $arParams['VARIABLES']['ELEMENT_CODE'],
            // 'SECTION_ID'           => $arParams['VARIABLES']['SECTION_ID'],
            // 'SECTION_CODE'         => $arParams['VARIABLES']['SECTION_CODE'],
            'USE_SHARE'            => $arParams['USE_SHARE'],
            'ADD_ELEMENT_CHAIN'    => $arParams['ADD_ELEMENT_CHAIN'],
            'STRICT_SECTION_CHECK' => $arParams['STRICT_SECTION_CHECK'],
            'URL_REDIRECT_404'     => $arParams['URL_REDIRECT_404'],
            'arParams'             => &$arParams,
        ],
        $component,
        [
            'HIDE_ICONS' => 'Y',
        ]
    );
}
//@todo тут начинается
if ($productSearchResult->getQuery()) :
    ?>
    <div class="b-container"></div>
    <div class="b-catalog">
    <div class="b-container b-container--catalog-filter js-brand-container-catalog">
        <?php /** товары бренда */ ?>
        <?=$view->render(
            'FourPawsCatalogBundle:Catalog:share.filter.container.html.php',
            [
                'catalogRequest'      => $catalogRequest,
                'searchService'       => $searchService,
                'productSearchResult' => $productSearchResult,
                'request'             => $request,
                'dataLayerService'    => $dataLayerService,
                'ecommerceService'    => $ecommerceService,
            ]
        )?>
    </div>
    <?php if (!$isAjax) :
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
    ); ?>
    </div>
<?php endif;
endif;
if (!$isAjax) {
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
} else { ?>
    </div>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
}
die();
