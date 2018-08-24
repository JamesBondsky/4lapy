<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\PropertyTable;
use FourPaws\BitrixOrm\Utils\IblockPropEntityConstructor;
use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult $productSearchResult
 * @var PhpEngine $view
 * @var GoogleEcommerceService $ecommerceService
 * @var CMain $APPLICATION
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$category = $catalogRequest->getCategory();
if ($category->isLanding()) {
    $filterName = 'catalogSliderFilter';
    global ${$filterName};
    ${$filterName} = ['PROPERTY_SECTION' => $category->getId()];
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
            'PARENT_SECTION_CODE' => 'catalog_banner',
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
} ?>
    <div class="b-catalog js-preloader-fix">
        <div class="b-container b-container--catalog-filter">
            <?= $view->render(
                'FourPawsCatalogBundle:Catalog:catalog.filter.container.html.php',
                \compact('catalogRequest', 'productSearchResult', 'ecommerceService')
            ) ?>
        </div>
        <?php if ($category->isLanding()) {
            global $faqCategoryId;
            $faqCategoryId = $category->getUfFaqSection();
            $APPLICATION->IncludeComponent(
                'bitrix:main.include',
                '',
                [
                    'AREA_FILE_SHOW' => 'file',
                    'PATH' => '/local/include/blocks/faq.php',
                    'EDIT_TEMPLATE' => '',
                ],
                null,
                [
                    'HIDE_ICONS' => 'Y',
                ]
            );

            $filterName = 'catalogLandingNewsFilter';
            global ${$filterName};
            $iblocks = [
                IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS),
                IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES),
            ];
            $itemIds = [];
            $newLimit = $limit = 8;
            /** @todo это фикс поиска по id вместо свойств в компоненте, оставить до переделки компонента на поиск по d7 */
            foreach ($iblocks as $iblock) {
                if ($newLimit > 0) {
                    $propData = IblockPropEntityConstructor::getDataClass((int)$iblock);
                    $res = $propData::query()
                        ->where('PROPERTY_' . PropertyTable::query()
                                ->setSelect(['ID'])
                                ->where('IBLOCK_ID',
                                    $iblock)
                                ->where('CODE', 'IN_LANDING')
                                ->setCacheTtl(360000)
                                ->exec()->fetch()['ID'], 1)
                        ->setSelect(['IBLOCK_ELEMENT_ID'])
                        ->setLimit($newLimit)
                        ->exec();
                    while ($item = $res->fetch()) {
                        $itemIds[] = $item['IBLOCK_ELEMENT_ID'];
                        $newLimit--;
                    }
                }
            }
            ${$filterName} = [
                '=ID' => $itemIds,
                'SHOW_ALL_WO_SECTION' => 'Y',
            ];
            $APPLICATION->IncludeComponent('fourpaws:items.list',
                'in_catalog',
                [
                    'ACTIVE_DATE_FORMAT' => 'j F Y',
                    'AJAX_MODE' => 'N',
                    'AJAX_OPTION_ADDITIONAL' => '',
                    'AJAX_OPTION_HISTORY' => 'N',
                    'AJAX_OPTION_JUMP' => 'N',
                    'AJAX_OPTION_STYLE' => 'Y',
                    'CACHE_FILTER' => 'Y',
                    'CACHE_GROUPS' => 'N',
                    'CACHE_TIME' => '36000000',
                    'CACHE_TYPE' => 'A',
                    'CHECK_DATES' => 'Y',
                    'FIELD_CODE' => [
                        '',
                    ],
                    'FILTER_NAME' => $filterName,
                    'IBLOCK_ID' => $iblocks,
                    'IBLOCK_TYPE' => IblockType::PUBLICATION,
                    'NEWS_COUNT' => $limit,
                    'PREVIEW_TRUNCATE_LEN' => '',
                    'PROPERTY_CODE' => [
                        'PUBLICATION_TYPE',
                        'VIDEO',
                    ],
                    'SET_LAST_MODIFIED' => 'N',
                    'SORT_BY1' => 'ACTIVE_FROM',
                    'SORT_BY2' => 'SORT',
                    'SORT_ORDER1' => 'DESC',
                    'SORT_ORDER2' => 'ASC',
                    'CHECK_PERMISSIONS' => 'N',
                ],
                false,
                ['HIDE_ICONS' => 'Y']);
        }
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
        ); ?>
    </div>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
