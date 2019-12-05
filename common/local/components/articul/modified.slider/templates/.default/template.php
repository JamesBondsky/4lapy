<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

try {
    $APPLICATION->IncludeComponent(
        'bitrix:news.list',
        'modified.slider',
        [
            'ACTIVE_DATE_FORMAT' => 'd.m.Y',
            'ADD_SECTIONS_CHAIN' => 'N',
            'AJAX_MODE' => 'N',
            'AJAX_OPTION_ADDITIONAL' => '',
            'AJAX_OPTION_HISTORY' => 'N',
            'AJAX_OPTION_JUMP' => 'N',
            'AJAX_OPTION_STYLE' => 'N',
            'CACHE_FILTER' => 'N',
            'CACHE_GROUPS' => 'N',
            'CACHE_TIME' => '36000000',
            'CACHE_TYPE' => 'A',
            'CHECK_DATES' => 'Y',
            'DETAIL_URL' => '',
            'DISPLAY_BOTTOM_PAGER' => 'N',
            'DISPLAY_TOP_PAGER' => 'N',
            'FIELD_CODE' => ['', ''],
            'FILTER_NAME' => 'modifiedSliderFilter',
            'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SLIDER_CONTROLLED),
            'IBLOCK_TYPE' => IblockType::PUBLICATION,
            'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
            'INCLUDE_SUBSECTIONS' => 'N',
            'MESSAGE_404' => '',
            'NEWS_COUNT' => '20',
            'PAGER_BASE_LINK_ENABLE' => 'N',
            'PAGER_DESC_NUMBERING' => 'N',
            'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
            'PAGER_SHOW_ALL' => 'N',
            'PAGER_SHOW_ALWAYS' => 'N',
            'PAGER_TEMPLATE' => '.default',
            'PAGER_TITLE' => 'Баннеры',
            'PARENT_SECTION' => '',
            'PARENT_SECTION_CODE' => '',
            'PREVIEW_TRUNCATE_LEN' => '',
            'PROPERTY_CODE' => [
                'COLOR',
                'BIG_TEXT',
                'LINK',
                'LOCATION',
                'BUTTON_TEXT',
                'BUTTON_COLOR',
                'HIDE_LOGO_MOBILE',
                'LEFT_SVG',
                'HASH_LEFT_COLOR',
            ],
            'SET_BROWSER_TITLE' => 'N',
            'SET_LAST_MODIFIED' => 'N',
            'SET_META_DESCRIPTION' => 'N',
            'SET_META_KEYWORDS' => 'N',
            'SET_STATUS_404' => 'N',
            'SET_TITLE' => 'N',
            'SHOW_404' => 'N',
            'SORT_BY1' => 'SORT',
            'SORT_BY2' => 'ACTIVE_FROM',
            'SORT_ORDER1' => 'ASC',
            'SORT_ORDER2' => 'DESC',
            'STRICT_SECTION_CHECK' => 'N',
        ]
    );
} catch (\Exception $e) {
}

