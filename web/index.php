<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Зоомагазин Четыре лапы – сеть магазинов зоотоваров');
$APPLICATION->SetPageProperty('NOT_SHOW_NAV_CHAIN', 'Y');
$APPLICATION->SetTitle('Главная страница');

$serializer = \FourPaws\Helpers\JmsSerializerHelper::getJmsSerializer();

$userService = $container->get(\FourPaws\UserBundle\Service\CurrentUserProviderInterface::class);
$curUser = $userService->getCurrentUser();
$users = $userService->getUserRepository()->findBy(['ACTIVE'=>'Y'],[],3);

$res = $serializer->serialize($users, 'csv', \JMS\Serializer\SerializationContext::create()->setGroups('read'));
echo $res;
$res = $serializer->deserialize($res, sprintf('array<%s>',\FourPaws\UserBundle\Entity\User::class),'csv',\JMS\Serializer\DeserializationContext::create()->setGroups('read'));
var_dump($res);

$APPLICATION->IncludeComponent('bitrix:news.list',
    'index.slider',
    [
        'COMPONENT_TEMPLATE' => 'index.slider',
        'IBLOCK_TYPE' => IblockType::PUBLICATION,
        'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS),
        'NEWS_COUNT' => '7',
        'SORT_BY1' => 'SORT',
        'SORT_ORDER1' => 'ASC',
        'SORT_BY2' => 'ACTIVE_FROM',
        'SORT_ORDER2' => 'DESC',
        'FILTER_NAME' => '',
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
        'PARENT_SECTION_CODE' => 'slider_main',
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

/** main sections by mobile */
$APPLICATION->IncludeComponent('bitrix:menu',
    'mobile.root_section_menu',
    [
        'COMPONENT_TEMPLATE' => 'mobile.root_section_menu',
        'ROOT_MENU_TYPE' => 'mobile_root_section',
        'MENU_CACHE_TYPE' => 'A',
        'MENU_CACHE_TIME' => '360000',
        'MENU_CACHE_USE_GROUPS' => 'Y',
        'CACHE_SELECTED_ITEMS' => 'N',
        'TEMPLATE_NO_CACHE' => 'N',
        'MENU_CACHE_GET_VARS' => [],
        'MAX_LEVEL' => '1',
        'CHILD_MENU_TYPE' => 'mobile_root_section',
        'USE_EXT' => 'N',
        'DELAY' => 'N',
        'ALLOW_MULTI_SELECT' => 'N',
    ],
    false);

/**
 * Популярные товары
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/local/include/blocks/index.popular_products.php',
        'EDIT_TEMPLATE' => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);

/**
 * Распродажа
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/local/include/blocks/index.sale_products.php',
        'EDIT_TEMPLATE' => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);

/**
 * Преимущества
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/local/include/blocks/advantages.php',
        'EDIT_TEMPLATE' => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'N',
    ]
);

/**
 * Контейнер страницы. Не должен редактироваться в визуальном редакторе. Закрывается перед подключением подвала.
 */
echo '<div class="b-container">';

/**
 * Популярные бренды
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/local/include/blocks/index.popular_brands.php',
        'EDIT_TEMPLATE' => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);

$APPLICATION->IncludeComponent('bitrix:main.include',
    'index.pet_block',
    [
        'COMPONENT_TEMPLATE' => '.default',
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/local/include/blocks/index.pet_block.php',
        'EDIT_TEMPLATE' => '',
    ],
    false);
/**
 * Новости и события.
 */
$APPLICATION->IncludeComponent('fourpaws:items.list',
    '',
    [
        'ACTIVE_DATE_FORMAT' => 'j F Y',
        'AJAX_MODE' => 'N',
        'AJAX_OPTION_ADDITIONAL' => '',
        'AJAX_OPTION_HISTORY' => 'N',
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_STYLE' => 'Y',
        'CACHE_FILTER' => 'N',
        'CACHE_GROUPS' => 'Y',
        'CACHE_TIME' => '36000000',
        'CACHE_TYPE' => 'A',
        'CHECK_DATES' => 'Y',
        'FIELD_CODE' => [
            '',
        ],
        'FILTER_NAME' => '',
        'IBLOCK_ID' => [
            IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS),
            IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES),
            //IblockUtils::getIblockId(IblockType::PUBLICATION, 'cloubs_and_nurderis'),//Раскоментить когда добавится инфоблок
        ],
        'IBLOCK_TYPE' => IblockType::PUBLICATION,
        'NEWS_COUNT' => '7',
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
    ],
    false,
    ['HIDE_ICONS' => 'Y']);

/**
 * Просмотренные товары
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/local/include/blocks/index.viewed_products.php',
        'EDIT_TEMPLATE' => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);

/**
 * Контейнер текста на странице
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/local/include/blocks/index.seo_text.php',
        'EDIT_TEMPLATE' => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'N',
    ]
);

echo '</div>';

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
