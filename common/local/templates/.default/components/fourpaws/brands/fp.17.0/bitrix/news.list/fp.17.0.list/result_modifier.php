<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
/**
 * Популярные бренды
 *
 * @updated: 21.12.2017
 */

if(!$arResult['ITEMS']) {
	return;
}

$arResult['GROUPING'] = array();
$arResult['GROUPING']['#'] = array(
	'TITLE' => '#',
	'ANCHOR' => md5('#'),
	'ITEMS_ARRAY_KEYS' => array()
);

foreach($arResult['ITEMS'] as $mKey => &$arItem) {
	$arItem['PRINT_PICTURE'] = array();
	if(!empty($arParams['RESIZE_WIDTH']) && !empty($arParams['RESIZE_HEIGHT'])) {
		$arImg = \CFile::ResizeImageGet(
			$arItem['PREVIEW_PICTURE'] ? $arItem['PREVIEW_PICTURE'] : $arItem['DETAIL_PICTURE'],
			array(
				'width' => $arParams['RESIZE_WIDTH'],
				'height' => $arParams['RESIZE_HEIGHT'],
			),
			$arParams['RESIZE_TYPE'],
			false
		);
		if($arImg) {
			$arItem['PRINT_PICTURE'] = array(
				'SRC' => $arImg['src'],
			);
		}
	}

	$sFirstLetter = ToUpper(substr(trim($arItem['NAME']), 0, 1));
	$sFirstLetterReduced = $sFirstLetter;
	if(preg_match('#[^\p{L}]+#'.BX_UTF_PCRE_MODIFIER, $sFirstLetter)) {
		if(preg_match('#[0-9]+#'.BX_UTF_PCRE_MODIFIER, $sFirstLetter)) {
			$sFirstLetterReduced = '#';
		} else {
			$sFirstLetterReduced = '#';
		}
	}
	if(!isset($arResult['GROUPING'][$sFirstLetterReduced])) {
		$arResult['GROUPING'][$sFirstLetterReduced] = array(
			'TITLE' => $sFirstLetter,
			'ANCHOR' => md5($sFirstLetterReduced),
			'ITEMS_ARRAY_KEYS' => array()
		);
	}
	$arResult['GROUPING'][$sFirstLetterReduced]['ITEMS_ARRAY_KEYS'][] = $mKey;
}
unset($arItem);
