<?php

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\App\Application as App;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\MobileApiBundle\Services\Api\BannerService;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\UserBundle\Service\UserCitySelectInterface;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $USER;

$APPLICATION->SetPageProperty('title', 'Интернет-зоомагазин Четыре Лапы – продажа и доставка зоотоваров по Москве, Московской области и всей России');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetPageProperty('keywords', '');
$APPLICATION->SetPageProperty('NOT_SHOW_NAV_CHAIN', 'Y');
$APPLICATION->SetTitle('Интернет-зоомагазин Четыре Лапы – продажа и доставка зоотоваров по Москве, Московской области и всей России');

$selectedCityCode = null;

try {
	/** @var \FourPaws\UserBundle\Service\UserService $userService */
	$userService = App::getInstance()
		->getContainer()
		->get(UserCitySelectInterface::class);
	$selectedCity = $userService->getSelectedCity(); //FIXME убрать повторные запросы внутри метода, если он уже выполнялся ранее
	$selectedCityCode = $selectedCity['CODE'];
} catch (Exception $e) {}


global $filterSlider;

$filterSlider = [
	'PROPERTY_LOCATION' => [$selectedCityCode, false]
];

$APPLICATION->IncludeComponent('bitrix:news.list',
    'index.slider',
    [
        'COMPONENT_TEMPLATE'              => 'index.slider',
        'IBLOCK_TYPE'                     => IblockType::PUBLICATION,
        'IBLOCK_ID'                       => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS),
        'NEWS_COUNT'                      => '20',
        'SORT_BY1'                        => BannerService::BANNER_LIST_SORT_BY1,
        'SORT_ORDER1'                     => BannerService::BANNER_LIST_SORT_ORDER1,
        'SORT_BY2'                        => BannerService::BANNER_LIST_SORT_BY2,
        'SORT_ORDER2'                     => BannerService::BANNER_LIST_SORT_ORDER2,
        'FILTER_NAME'                     => 'filterSlider',
        'FIELD_CODE'                      => [
            0 => 'NAME',
            1 => 'PREVIEW_PICTURE',
            2 => 'DETAIL_PICTURE',
            3 => 'ACTIVE_TO',
        ],
        'PROPERTY_CODE'                   => [
            0 => 'LINK',
            1 => 'IMG_TABLET',
            2 => 'BACKGROUND',
            3 => 'LOCATION',
        ],
        'CHECK_DATES'                     => 'Y',
        'DETAIL_URL'                      => '',
        'AJAX_MODE'                       => 'N',
        'AJAX_OPTION_JUMP'                => 'N',
        'AJAX_OPTION_STYLE'               => 'N',
        'AJAX_OPTION_HISTORY'             => 'N',
        'AJAX_OPTION_ADDITIONAL'          => '',
        'CACHE_TYPE'                      => 'A',
        'CACHE_TIME'                      => '3600',
        'CACHE_FILTER'                    => 'Y',
        'CACHE_GROUPS'                    => 'N',
        'PREVIEW_TRUNCATE_LEN'            => '',
        'ACTIVE_DATE_FORMAT'              => '',
        'SET_TITLE'                       => 'N',
        'SET_BROWSER_TITLE'               => 'N',
        'SET_META_KEYWORDS'               => 'N',
        'SET_META_DESCRIPTION'            => 'N',
        'SET_LAST_MODIFIED'               => 'N',
        'INCLUDE_IBLOCK_INTO_CHAIN'       => 'N',
        'ADD_SECTIONS_CHAIN'              => 'N',
        'HIDE_LINK_WHEN_NO_DETAIL'        => 'N',
        'PARENT_SECTION'                  => '',
        'PARENT_SECTION_CODE'             => 'slider_main',
        'INCLUDE_SUBSECTIONS'             => 'N',
        'STRICT_SECTION_CHECK'            => 'N',
        'DISPLAY_DATE'                    => 'N',
        'DISPLAY_NAME'                    => 'N',
        'DISPLAY_PICTURE'                 => 'N',
        'DISPLAY_PREVIEW_TEXT'            => 'N',
        'PAGER_TEMPLATE'                  => '',
        'DISPLAY_TOP_PAGER'               => 'N',
        'DISPLAY_BOTTOM_PAGER'            => 'N',
        'PAGER_TITLE'                     => '',
        'PAGER_SHOW_ALWAYS'               => 'N',
        'PAGER_DESC_NUMBERING'            => 'N',
        'PAGER_DESC_NUMBERING_CACHE_TIME' => '',
        'PAGER_SHOW_ALL'                  => 'N',
        'PAGER_BASE_LINK_ENABLE'          => 'N',
        'SET_STATUS_404'                  => 'N',
        'SHOW_404'                        => 'N',
        'MESSAGE_404'                     => '',
    ],
    false,
    ['HIDE_ICONS' => 'Y']);

/** main sections by mobile */
$APPLICATION->IncludeComponent('bitrix:menu',
    'mobile.root_section_menu',
    [
        'COMPONENT_TEMPLATE'    => 'mobile.root_section_menu',
        'ROOT_MENU_TYPE'        => 'mobile_root_section',
        'MENU_CACHE_TYPE'       => 'A',
        'MENU_CACHE_TIME'       => '360000',
        'MENU_CACHE_USE_GROUPS' => 'Y',
        'CACHE_SELECTED_ITEMS'  => 'N',
        'TEMPLATE_NO_CACHE'     => 'N',
        'MENU_CACHE_GET_VARS'   => [],
        'MAX_LEVEL'             => '1',
        'CHILD_MENU_TYPE'       => 'mobile_root_section',
        'USE_EXT'               => 'N',
        'DELAY'                 => 'N',
        'ALLOW_MULTI_SELECT'    => 'N',
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
        'PATH'           => '/local/include/blocks/index.popular_products.php',
        'EDIT_TEMPLATE'  => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);
?>

<?php
$APPLICATION->IncludeComponent('articul:modified.slider', '', ['LOCATION' => $selectedCity['CODE']]);
?>

<?php
/**
 * Распродажа
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH'           => '/local/include/blocks/index.sale_products.php',
        'EDIT_TEMPLATE'  => '',
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
        'PATH'           => '/local/include/blocks/advantages.php',
        'EDIT_TEMPLATE'  => '',
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
        'PATH'           => '/local/include/blocks/index.popular_brands.php',
        'EDIT_TEMPLATE'  => '',
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
        'AREA_FILE_SHOW'     => 'file',
        'PATH'               => '/local/include/blocks/index.pet_block.php',
        'EDIT_TEMPLATE'      => '',
    ],
    false);
/**
 * Новости и события.
 */
$APPLICATION->IncludeComponent('fourpaws:items.list',
    '',
    [
        'ACTIVE_DATE_FORMAT'     => 'j F Y',
        'AJAX_MODE'              => 'N',
        'AJAX_OPTION_ADDITIONAL' => '',
        'AJAX_OPTION_HISTORY'    => 'N',
        'AJAX_OPTION_JUMP'       => 'N',
        'AJAX_OPTION_STYLE'      => 'Y',
        'CACHE_FILTER'           => 'Y',
        'CACHE_GROUPS'           => 'N',
        'CACHE_TIME'             => '36000000',
        'CACHE_TYPE'             => 'A',
        'CHECK_DATES'            => 'Y',
        'FIELD_CODE'             => [
            '',
        ],
        'FILTER_NAME'            => '',
        'IBLOCK_ID'              => [
            IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS),
            IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES),
        ],
        'IBLOCK_TYPE'            => IblockType::PUBLICATION,
        'NEWS_COUNT'             => '7',
        'PREVIEW_TRUNCATE_LEN'   => '',
        'PROPERTY_CODE'          => [
            'PUBLICATION_TYPE',
            'VIDEO',
        ],
        'SET_LAST_MODIFIED'      => 'N',
        'SORT_BY1'               => 'ACTIVE_FROM',
        'SORT_BY2'               => 'SORT',
        'SORT_ORDER1'            => 'DESC',
        'SORT_ORDER2'            => 'ASC',
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
if (!KioskService::isKioskMode()) {
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
}

echo '</div>';

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
