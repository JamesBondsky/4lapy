<?

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Акции");
?>
<?php $APPLICATION->IncludeComponent(
    'bitrix:news',
    'fp.17.0.shares',
    [
        'COMPONENT_TEMPLATE' => 'fp.17.0.shares',
        'DEFAULT_PUBLICATION_TYPE_VALUE' => 'Акции',

        'ADD_ELEMENT_CHAIN' => 'N',
        'ADD_SECTIONS_CHAIN' => 'N',
        'AJAX_MODE' => 'N',
        'AJAX_OPTION_ADDITIONAL' => '',
        'AJAX_OPTION_HISTORY' => 'N',
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_STYLE' => 'N',
        'BROWSER_TITLE' => '-',
        'CACHE_FILTER' => 'Y',
        'CACHE_GROUPS' => 'Y',
        'CACHE_TIME' => '43200',
        'CACHE_TYPE' => 'A',
        'CHECK_DATES' => 'Y',
        'DETAIL_ACTIVE_DATE_FORMAT' => 'j F Y',
        'DETAIL_DISPLAY_BOTTOM_PAGER' => 'N',
        'DETAIL_DISPLAY_TOP_PAGER' => 'N',
        'DETAIL_FIELD_CODE' => [
            'ACTIVE_FROM', 'ACTIVE_TO'
        ],
        'DETAIL_PAGER_SHOW_ALL' => 'N',
        'DETAIL_PAGER_TEMPLATE' => '',
        'DETAIL_PAGER_TITLE' => 'Страница',
        'DETAIL_PROPERTY_CODE' => [
        ],
        'DETAIL_SET_CANONICAL_URL' => 'Y',
        'DISPLAY_TOP_PAGER' => 'N',
        'DISPLAY_BOTTOM_PAGER' => 'Y',
        'FILE_404' => '',
        'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
        'IBLOCK_ID' => IblockUtils::getIblockId(
            IblockType::PUBLICATION,
            IblockCode::SHARES
        ),
        'IBLOCK_TYPE' => IblockType::PUBLICATION,
        'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
        'LIST_ACTIVE_DATE_FORMAT' => 'j F Y',
        'LIST_FIELD_CODE' => [
        ],
        'LIST_PROPERTY_CODE' => [
        ],
        'MESSAGE_404' => '',
        'META_DESCRIPTION' => '-',
        'META_KEYWORDS' => '-',
        'NEWS_COUNT' => '16',
        'PAGER_BASE_LINK_ENABLE' => 'N',
        'PAGER_DESC_NUMBERING' => 'N',
        'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
        'PAGER_SHOW_ALL' => 'N',
        'PAGER_SHOW_ALWAYS' => 'N',
        'PAGER_TEMPLATE' => 'pagination',
        'PAGER_TITLE' => 'Акции',
        'PREVIEW_TRUNCATE_LEN' => '0',
        'SEF_FOLDER' => '/customer/shares/',
        'SEF_MODE' => 'Y',
        'SEF_URL_TEMPLATES' => [
            'news' => '',
            'section' => 'by_pet/#SECTION_CODE#/', // макрос #SECTION_CODE# используется для фильтрации по видам питомцев
            'detail' => '#ELEMENT_CODE#/',
        ],
        'SET_LAST_MODIFIED' => 'Y',
        'SET_STATUS_404' => 'Y',
        'SET_TITLE' => 'Y',
        'SHARE_HANDLERS' => [
            0 => 'facebook',
            1 => 'vk',
        ],
        'SHOW_404' => 'Y',
        'SORT_BY1' => 'ACTIVE_FROM',
        'SORT_ORDER1' => 'DESC,NULLS',
        'SORT_BY2' => 'SORT',
        'SORT_ORDER2' => 'ASC',
        'STRICT_SECTION_CHECK' => 'N',
        'USE_CATEGORIES' => 'N',
        'USE_FILTER' => 'Y',
        'USE_PERMISSIONS' => 'N',
        'USE_RATING' => 'N',
        'USE_REVIEW' => 'N',
        'USE_RSS' => 'N',
        'USE_SEARCH' => 'N',
        'USE_SHARE' => 'Y',
        'SHARE_HIDE' => 'N',
        'SHARE_SHORTEN_URL_KEY' => '',
        'SHARE_SHORTEN_URL_LOGIN' => '',
        'SHARE_TEMPLATE' => '',
        'DISPLAY_PREVIEW_TEXT' => 'N',
        'SHOW_PRODUCTS_SALE' => 'Y',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y'
    ]
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>