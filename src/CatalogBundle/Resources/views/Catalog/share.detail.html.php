<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
ini_set('error_reporting', E_ALL);

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

/**
 * @var CatalogShareRequest    $catalogShareRequest
 * @var ProductSearchResult    $productSearchResult
 * @var SearchService          $searchService
 * @var PhpEngine              $view
 * @var CMain                  $APPLICATION
 * @var Request                $request
 */
global $APPLICATION;

$APPLICATION->SetPageProperty('PUBLICATION_DETAIL_CONTAINER_1', 'b-container b-container--news-detail');
$APPLICATION->SetPageProperty('PUBLICATION_DETAIL_CONTAINER_2', 'b-detail-page');


echo '111111';
//@todo тут начинается ?>

<?php
// $elementId = $APPLICATION->IncludeComponent(
//     'fourpaws:shares.detail',
//     '',
//     [
//         'IBLOCK_TYPE'               => $arParams['IBLOCK_TYPE'],
//         'IBLOCK_ID'                 => $arParams['IBLOCK_ID'],
//         'FIELD_CODE'                => $arParams['DETAIL_FIELD_CODE'],
//         'ELEMENT_ID'                => $arResult['VARIABLES']['ELEMENT_ID'],
//         'ELEMENT_CODE'              => $arResult['VARIABLES']['ELEMENT_CODE'],
//         'SECTION_ID'                => $arResult['VARIABLES']['SECTION_ID'],
//         'SECTION_CODE'              => $arResult['VARIABLES']['SECTION_CODE'],
//         'USE_SHARE'                 => $arParams['USE_SHARE'],
//         'ADD_ELEMENT_CHAIN'         => $arParams['ADD_ELEMENT_CHAIN'],
//         'STRICT_SECTION_CHECK'      => $arParams['STRICT_SECTION_CHECK'],
//         'URL_REDIRECT_404'          => $arParams['URL_REDIRECT_404'],
//         'arParams' => &$arParams,
//     ],
//     $component,
//     [
//         'HIDE_ICONS' => 'Y',
//     ]
// );

die;




?>
    <div class="b-catalog">
    <div class="b-container b-container--catalog-filter js-brand-container-catalog">
        <?php /** товары бренда */ ?>
        <?= $view->render(
            'FourPawsCatalogBundle:Catalog:brand.filter.container.html.php',
            [
                'catalogRequest'      => $catalogRequest,
                'searchService'       => $searchService,
                'ecommerceService'    => $ecommerceService,
                'dataLayerService'    => $dataLayerService,
                'productSearchResult' => $productSearchResult,
                'brand'               => $catalogRequest->getBrand()->getCode(),
            ]
        ) ?>
    </div>

<?php if (!$isAjax) {
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
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
} else { ?>
    </div>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
}
die();
