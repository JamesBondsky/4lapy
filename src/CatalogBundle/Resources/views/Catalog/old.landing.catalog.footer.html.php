<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\BitrixOrm\Utils\IblockPropEntityConstructor;
use FourPaws\Catalog\Model\Category;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var Category $category
 * @var PhpEngine $view
 */

global $faqCategoryId, $APPLICATION;
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
        try {
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
        } catch (ObjectPropertyException | SystemException | ArgumentException $e) {
            $itemIds = -1;
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