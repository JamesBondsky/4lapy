<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

if(!\Bitrix\Main\Loader::includeModule('iblock')) {
	return;
}

$sMessPrefix = 'FOURPAWS.IBLOCK_MAIN_MENU.';

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
		'CACHE_TIME' => array(
			'DEFAULT' => 43200
		),
	),
);
