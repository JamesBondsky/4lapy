<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
/**
 * Карточка бренда
 *
 * @updated: 22.12.2017
 */

$arResult['PRINT_PICTURE'] = array();
if(!empty($arParams['RESIZE_WIDTH']) && !empty($arParams['RESIZE_HEIGHT']) && ($arResult['PREVIEW_PICTURE'] || $arResult['DETAIL_PICTURE'])) {
	$arImg = \CFile::ResizeImageGet(
		$arResult['DETAIL_PICTURE'] ? $arResult['DETAIL_PICTURE'] : $arResult['PREVIEW_PICTURE'],
		array(
			'width' => $arParams['RESIZE_WIDTH'],
			'height' => $arParams['RESIZE_HEIGHT'],
		),
		$arParams['RESIZE_TYPE'],
		false
	);
	if($arImg) {
		$arResult['PRINT_PICTURE'] = array(
			'SRC' => $arImg['src'],
		);
	}
}
