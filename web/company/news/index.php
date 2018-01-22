<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Новости');
?>
<?php $APPLICATION->IncludeComponent(
    'bitrix:news',
    'news',
    [
        'ADD_ELEMENT_CHAIN'               => 'Y',
        'ADD_SECTIONS_CHAIN'              => 'Y',
        'AJAX_MODE'                       => 'N',
        'AJAX_OPTION_ADDITIONAL'          => '',
        'AJAX_OPTION_HISTORY'             => 'N',
        'AJAX_OPTION_JUMP'                => 'N',
        'AJAX_OPTION_STYLE'               => 'Y',
        'BROWSER_TITLE'                   => '-',
        'CACHE_FILTER'                    => 'N',
        'CACHE_GROUPS'                    => 'Y',
        'CACHE_TIME'                      => '36000000',
        'CACHE_TYPE'                      => 'A',
        'CATEGORY_CODE'                   => 'PRODUCTS',
        'CATEGORY_IBLOCK'                 => [
            0 => IblockUtils::getIblockId(
                IblockType::CATALOG,
                IblockCode::PRODUCTS
            ),
        ],
        'CATEGORY_ITEMS_COUNT'            => '5',
        'CATEGORY_THEME_' . IblockUtils::getIblockId(
            IblockType::CATALOG,
            IblockCode::PRODUCTS
        )                                 => 'photo',
        'CHECK_DATES'                     => 'Y',
        'COMPONENT_TEMPLATE'              => 'news',
        'DETAIL_ACTIVE_DATE_FORMAT'       => 'j F',
        'DETAIL_DISPLAY_BOTTOM_PAGER'     => 'N',
        'DETAIL_DISPLAY_TOP_PAGER'        => 'N',
        'DETAIL_FIELD_CODE'               => [
            0 => '',
            1 => '',
        ],
        'DETAIL_PAGER_SHOW_ALL'           => 'N',
        'DETAIL_PAGER_TEMPLATE'           => '',
        'DETAIL_PAGER_TITLE'              => 'Страница',
        'DETAIL_PROPERTY_CODE'            => [
            0 => 'VIDEO',
            1 => 'PRODUCTS',
            2 => 'MORE_PHOTO',
        ],
        'DETAIL_SET_CANONICAL_URL'        => 'Y',
        'DISPLAY_BOTTOM_PAGER'            => 'Y',
        'DISPLAY_DATE'                    => 'Y',
        'DISPLAY_NAME'                    => 'Y',
        'DISPLAY_PICTURE'                 => 'Y',
        'DISPLAY_PREVIEW_TEXT'            => 'Y',
        'DISPLAY_TOP_PAGER'               => 'N',
        'FILE_404'                        => '',
        'FORUM_ID'                        => 1,
        'HIDE_LINK_WHEN_NO_DETAIL'        => 'N',
        'IBLOCK_ID'                       => IblockUtils::getIblockId(
            IblockType::PUBLICATION,
            IblockCode::NEWS
        ),
        'IBLOCK_TYPE'                     => IblockType::PUBLICATION,
        'INCLUDE_IBLOCK_INTO_CHAIN'       => 'N',
        'LIST_ACTIVE_DATE_FORMAT'         => 'j F Y',
        'LIST_FIELD_CODE'                 => [
            0 => '',
            1 => '',
        ],
        'LIST_PROPERTY_CODE'              => [
            0 => 'PUBLICATION_TYPE',
            1 => '',
        ],
        'MESSAGE_404'                     => '',
        'META_DESCRIPTION'                => '-',
        'META_KEYWORDS'                   => '-',
        'NEWS_COUNT'                      => '8',
        'PAGER_BASE_LINK_ENABLE'          => 'N',
        'PAGER_DESC_NUMBERING'            => 'N',
        'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
        'PAGER_SHOW_ALL'                  => 'N',
        'PAGER_SHOW_ALWAYS'               => 'N',
        'PAGER_TEMPLATE'                  => 'pagination',
        'PAGER_TITLE'                     => 'Новости',
        'PREVIEW_TRUNCATE_LEN'            => '100',
        'SEF_FOLDER'                      => '/company/news/',
        'SEF_MODE'                        => 'Y',
        'SEF_URL_TEMPLATES'               => [
            'news'    => '',
            'section' => '',
            'detail'  => '#ELEMENT_CODE#/',
        ],
        'SET_LAST_MODIFIED'               => 'Y',
        'SET_STATUS_404'                  => 'Y',
        'SET_TITLE'                       => 'Y',
        'SHARE_HANDLERS'                  => [
            0 => 'facebook',
            1 => 'vk',
        ],
        'SHARE_HIDE'                      => 'N',
        'SHARE_SHORTEN_URL_KEY'           => '',
        'SHARE_SHORTEN_URL_LOGIN'         => '',
        'SHARE_TEMPLATE'                  => '',
        'SHOW_404'                        => 'Y',
        'SORT_BY1'                        => 'ACTIVE_FROM',
        'SORT_BY2'                        => 'SORT',
        'SORT_ORDER1'                     => 'DESC',
        'SORT_ORDER2'                     => 'ASC',
        'STRICT_SECTION_CHECK'            => 'Y',
        'USE_CATEGORIES'                  => 'Y',
        'USE_FILTER'                      => 'N',
        'USE_PERMISSIONS'                 => 'N',
        'USE_RATING'                      => 'N',
        'USE_REVIEW'                      => 'Y',
        'USE_RSS'                         => 'N',
        'USE_SEARCH'                      => 'N',
        'USE_SHARE'                       => 'Y',
    ],
    false,
    ['HIDE_ICONS' => 'Y']
); ?>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>