<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
/**
 * Бренды: главная страница
 *
 * @updated: 21.12.2017
 */

echo '<div class="b-container">';
echo '<h1 class="b-title b-title--h1 b-title--block b-title--catalog-h2">Бренды</h1>';
//
// Популярные бренды
//
$GLOBALS['arPopularBrandsFilterExt'] = array(
	'=PROPERTY_POPULAR' => 1
);
$APPLICATION->IncludeComponent(
	'bitrix:news.list',
	'fp.17.0.popular',
	array(
		'IBLOCK_TYPE' => 'catalog',
		'IBLOCK_ID' => 'brands',
		'SORT_BY1' => 'SORT',
		'SORT_ORDER1' => 'ASC',
		'SORT_BY2' => 'NAME',
		'SORT_ORDER2' => 'ASC',
		'FIELD_CODE' => array(
			'NAME',
			'PREVIEW_PICTURE',
			'DETAIL_PICTURE',
		),
		'FILTER_NAME' => 'arPopularBrandsFilterExt',
		'CACHE_FILTER' => 'Y',
		'CACHE_GROUPS' => 'N',
		'NEWS_COUNT' => '8',
		'CACHE_TIME' => isset($arParams['CACHE_TIME']) ? $arParams['CACHE_TIME'] : '43200',
		'CACHE_TYPE' => isset($arParams['CACHE_TYPE']) ? $arParams['CACHE_TYPE'] : 'A',
		'CHECK_DATES' => 'Y',
		'DETAIL_URL' => '',

		'RESIZE_WIDTH' => '115',
		'RESIZE_HEIGHT' => '43',
		'RESIZE_TYPE' => 'BX_RESIZE_IMAGE_PROPORTIONAL',

		'ACTIVE_DATE_FORMAT' => 'd.m.Y',
		'ADD_SECTIONS_CHAIN' => 'N',
		'AJAX_MODE' => 'N',
		'AJAX_OPTION_ADDITIONAL' => '',
		'AJAX_OPTION_HISTORY' => 'N',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_STYLE' => 'N',
		'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
		'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
		'INCLUDE_SUBSECTIONS' => 'N',
		'PAGER_BASE_LINK_ENABLE' => 'N',
		'PAGER_DESC_NUMBERING' => 'N',
		'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
		'PAGER_SHOW_ALL' => 'N',
		'PAGER_SHOW_ALWAYS' => 'N',
		'PAGER_TEMPLATE' => '',
		'PAGER_TITLE' => '',
		'PARENT_SECTION' => '',
		'PARENT_SECTION_CODE' => '',
		'PREVIEW_TRUNCATE_LEN' => '',
		'PROPERTY_CODE' => array(),
		'SET_BROWSER_TITLE' => 'N',
		'SET_LAST_MODIFIED' => 'N',
		'SET_META_DESCRIPTION' => 'N',
		'SET_META_KEYWORDS' => 'N',
		'SET_STATUS_404' => 'N',
		'SET_TITLE' => 'N',
		'SHOW_404' => 'N',
	),
	$component,
	array(
		'HIDE_ICONS' => 'Y'
	)
);
echo '</div>';



echo '<div class="b-container b-container--brand-list">';
//
// Алфавитный указатель
//
$APPLICATION->IncludeComponent(
	'fourpaws:iblock.alphabetical.index',
	'fp.17.0.default',
	array(
		'IBLOCK_TYPE' => 'catalog',
		'IBLOCK_CODE' => 'brands',
		'CACHE_TYPE' => 'N',
		'CACHE_TIME' => '43200',
		'CHARS_COUNT' => 1,
		'TEMPLATE_NO_CACHE' => 'Y',
		'LETTER_PAGE_URL' => '/brands/#LETTER_REDUCED#/',
	),
	$component,
	array(
		'HIDE_ICONS' => 'Y'
	)
);

//
// Список всех брендов
//
$APPLICATION->IncludeComponent(
	'bitrix:news.list',
	'fp.17.0.list',
	array(
		'IBLOCK_TYPE' => 'catalog',
		'IBLOCK_ID' => 'brands',
		'SORT_BY1' => 'SORT',
		'SORT_ORDER1' => 'ASC',
		'SORT_BY2' => 'NAME',
		'SORT_ORDER2' => 'ASC',
		'FIELD_CODE' => array(
			'NAME',
			'PREVIEW_PICTURE',
			'DETAIL_PICTURE',
		),
		'FILTER_NAME' => '',
		'CACHE_FILTER' => 'Y',
		'CACHE_GROUPS' => 'N',
		'NEWS_COUNT' => '9999',
		'CACHE_TIME' => isset($arParams['CACHE_TIME']) ? $arParams['CACHE_TIME'] : '43200',
		'CACHE_TYPE' => isset($arParams['CACHE_TYPE']) ? $arParams['CACHE_TYPE'] : 'A',
		'CHECK_DATES' => 'Y',
		'DETAIL_URL' => '',

		'RESIZE_WIDTH' => '115',
		'RESIZE_HEIGHT' => '43',
		'RESIZE_TYPE' => 'BX_RESIZE_IMAGE_PROPORTIONAL',

		'ACTIVE_DATE_FORMAT' => 'd.m.Y',
		'ADD_SECTIONS_CHAIN' => 'N',
		'AJAX_MODE' => 'N',
		'AJAX_OPTION_ADDITIONAL' => '',
		'AJAX_OPTION_HISTORY' => 'N',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_STYLE' => 'N',
		'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
		'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
		'INCLUDE_SUBSECTIONS' => 'N',
		'PAGER_BASE_LINK_ENABLE' => 'N',
		'PAGER_DESC_NUMBERING' => 'N',
		'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
		'PAGER_SHOW_ALL' => 'N',
		'PAGER_SHOW_ALWAYS' => 'N',
		'PAGER_TEMPLATE' => '',
		'PAGER_TITLE' => '',
		'PARENT_SECTION' => '',
		'PARENT_SECTION_CODE' => '',
		'PREVIEW_TRUNCATE_LEN' => '',
		'PROPERTY_CODE' => array(),
		'SET_BROWSER_TITLE' => 'N',
		'SET_LAST_MODIFIED' => 'N',
		'SET_META_DESCRIPTION' => 'N',
		'SET_META_KEYWORDS' => 'N',
		'SET_STATUS_404' => 'N',
		'SET_TITLE' => 'N',
		'SHOW_404' => 'N',
	),
	$component,
	array(
		'HIDE_ICONS' => 'Y'
	)
);
echo '</div>';
