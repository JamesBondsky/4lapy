<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Catalog\Model\Category;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var Category $category
 * @var PhpEngine $view
 */
global $APPLICATION;

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