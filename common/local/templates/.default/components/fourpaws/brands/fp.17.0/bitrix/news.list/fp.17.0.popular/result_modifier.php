<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
/**
 * Популярные бренды
 *
 * @updated: 21.12.2017
 */

if(!$arResult['ITEMS']) {
	return;
}

foreach($arResult['ITEMS'] as &$arItem) {
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
}
unset($arItem);
