<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
/**
 * Карточка бренда (в разделе брендов)
 *
 * @updated: 25.12.2017
 */


$mImgField = false;
if($arResult['PREVIEW_PICTURE'] || $arResult['DETAIL_PICTURE']) {
	$mImgField = $arResult['PREVIEW_PICTURE'] ? $arResult['PREVIEW_PICTURE'] : $arResult['DETAIL_PICTURE'];
}
$arResult['PRINT_PICTURE'] = $mImgField && is_array($mImgField) ? $mImgField : array();
if($mImgField) {
	if(!empty($arParams['RESIZE_WIDTH']) && !empty($arParams['RESIZE_HEIGHT'])) {
		try {
			$bCrop = isset($arParams['RESIZE_TYPE']) && $arParams['RESIZE_TYPE'] == 'BX_RESIZE_IMAGE_EXACT';

			if(is_array($mImgField)) {
				$obImg = new \FourPaws\BitrixOrm\Model\ResizeImageDecorator($mImgField);
			} else {
				$obImg = \FourPaws\BitrixOrm\Model\ResizeImageDecorator::createFromPrimary($mImgField);
			}
			$obImg->setResizeWidth(!$bCrop ? $arParams['RESIZE_WIDTH'] : max(array($arParams['RESIZE_HEIGHT'], $arParams['RESIZE_WIDTH'])));
			$obImg->setResizeHeight(!$bCrop ? $arParams['RESIZE_HEIGHT'] : max(array($arParams['RESIZE_HEIGHT'], $arParams['RESIZE_WIDTH'])));

			if($bCrop) {
				if(is_array($mImgField)) {
					$obImg = new \FourPaws\BitrixOrm\Model\CropImageDecorator($mImgField);
				} else {
					$obImg = \FourPaws\BitrixOrm\Model\CropImageDecorator::createFromPrimary($mImgField);
				}
				$obImg->setCropWidth($arParams['RESIZE_WIDTH']);
				$obImg->setCropHeight($arParams['RESIZE_HEIGHT']);
			}

			$arResult['PRINT_PICTURE'] = array(
				'SRC' => $obImg->getSrc(),
			);
		} catch(\Exception $obException) {}
	}
}
// в кэше это поле нужно только если будет использоваться component_epilog.php
$this->__component->SetResultCacheKeys(
	array(
		'PRINT_PICTURE',
	)
);
