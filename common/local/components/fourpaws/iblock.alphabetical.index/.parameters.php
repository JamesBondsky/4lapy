<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

if(!\Bitrix\Main\Loader::includeModule('iblock')) {
	return;
}

$sMessPrefix = 'FOURPAWS.IBLOCK_ALPHABETICAL_INDEX.';

$arComponentParameters = array(
	'GROUPS' => array(
		'URL_SETTINGS' => array(
			'SORT' => 1000,
			'NAME' => \Bitrix\Main\Localization\Loc::getMessage($sMessPrefix.'G_URL_SETTINGS'),
		),
	),
	'PARAMETERS' => array(
		'IBLOCK_TYPE' => array(
			'PARENT' => 'BASE',
			'NAME' => '[IBLOCK_TYPE] '.\Bitrix\Main\Localization\Loc::getMessage($sMessPrefix.'IBLOCK_TYPE'),
			'TYPE' => 'STRING',
			'ADDITIONAL_VALUES' => 'N'
			'DEFAULT' => '',
		),
		'IBLOCK_CODE' => array(
			'PARENT' => 'BASE',
			'NAME' => '[IBLOCK_CODE] '.\Bitrix\Main\Localization\Loc::getMessage($sMessPrefix.'IBLOCK_CODE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
		'CHARS_COUNT' => array(
			'PARENT' => 'URL_SETTINGS',
			'NAME' => '[CHARS_COUNT] '.\Bitrix\Main\Localization\Loc::getMessage($sMessPrefix.'CHARS_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '1',
		),
		'LETTER_PAGE_URL' => array(
			'PARENT' => 'URL_SETTINGS',
			'NAME' => '[LETTER_PAGE_URL] '.\Bitrix\Main\Localization\Loc::getMessage($sMessPrefix.'LETTER_PAGE_URL'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
		'TEMPLATE_NO_CACHE' => array(
			'PARENT' => 'BASE',
			'NAME' => '[TEMPLATE_NO_CACHE] '.\Bitrix\Main\Localization\Loc::getMessage($sMessPrefix.'TEMPLATE_NO_CACHE'),
			'TYPE' => 'CHECKBOX',
			'VALUES' => '',
			'DEFAULT' => 'N'
		),
		'CACHE_TIME' => array(
			'DEFAULT' => 43200
		),
	),
);
