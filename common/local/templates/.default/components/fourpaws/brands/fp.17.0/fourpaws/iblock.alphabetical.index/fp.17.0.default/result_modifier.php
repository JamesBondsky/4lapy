<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
/**
 * Алфавитный указатель
 * !шаблон не должен кэшироваться!
 *
 * @updated: 21.12.2017
 */

$arCharsCollect = array();
$arCharsCollect[] = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
$arCharsCollect[] = explode(',', \Bitrix\Main\Localization\Loc::getMessage('BRANDS_AI.RU_ALPHABET'));

$arResult['PRINT'] = array();
$arResult['PRINT'][] = array(
	'CAPTION' => \Bitrix\Main\Localization\Loc::getMessage('BRANDS_AI.ALL'),
	'EXISTS' => 'N',
	'SELECTED' => 'N',
	'ANCHOR' => '',
	'URL' => str_replace(
		array('#LETTER#', '#LETTER_REDUCED#', '#SITE_DIR#', '#SERVER_NAME#', '#IBLOCK_ID#', '#IBLOCK_CODE#'),
		array(urlencode('all'), urlencode('all'), SITE_DIR, SITE_SERVER_NAME, $arParams['IBLOCK_ID'], $arParams['IBLOCK_CODE']),
		$arParams['LETTER_PAGE_URL']
	),
);

$arResult['PRINT'][] = array(
	'CAPTION' => '#',
	'EXISTS' => $arResult['IS_NUM_EXISTS'] === 'Y' || $arResult['IS_SPEC_EXISTS'] === 'Y' ? 'Y' : 'N',
	'SELECTED' => 'N',
	'ANCHOR' => md5('#'),
	'URL' => str_replace(
		array('#LETTER#', '#LETTER_REDUCED#', '#SITE_DIR#', '#SERVER_NAME#', '#IBLOCK_ID#', '#IBLOCK_CODE#'),
		array(urlencode('extra'), urlencode('extra'), SITE_DIR, SITE_SERVER_NAME, $arParams['IBLOCK_ID'], $arParams['IBLOCK_CODE']),
		$arParams['LETTER_PAGE_URL']
	),
);

foreach($arCharsCollect as $arCollect) {
	foreach($arCollect as $sChar) {
		$arResult['PRINT'][] = array(
			'CAPTION' => $sChar,
			'EXISTS' => isset($arResult['LIST'][$sChar]) ? 'Y' : 'N',
			'SELECTED' => 'N',
			'ANCHOR' => md5($sChar),
			'URL' => $arResult['LIST'][$sChar]['LETTER_PAGE_URL'],
		);
	}
}
