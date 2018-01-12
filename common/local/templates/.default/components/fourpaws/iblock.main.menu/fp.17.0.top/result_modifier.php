<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Главное меню сайта
 * result_modifier.php
 *
 * @updated: 11.01.2018
 */

if (!$arResult['MENU_TREE']) {
    return;
}

$arParams['RESIZE_WIDTH'] = isset($arParams['RESIZE_WIDTH']) ? $arParams['RESIZE_WIDTH'] : 115;
$arParams['RESIZE_HEIGHT'] = isset($arParams['RESIZE_HEIGHT']) ? $arParams['RESIZE_HEIGHT'] : 43;
$arParams['RESIZE_TYPE'] = isset($arParams['RESIZE_TYPE']) ? $arParams['RESIZE_TYPE'] : 'BX_RESIZE_IMAGE_PROPORTIONAL';

$funcWalkRecursive = function($arData, $funcSelf) {
    foreach ($arData as &$arItem) {
        $arItem['_TEXT_'] = $arItem['NAME'];
        $arItem['_URL_'] = strlen($arItem['URL']) ? $arItem['URL'] : 'javascript:void(0);';
        $arItem['_LINK_ATTR1_'] = '';
        if ($arItem['TARGET_BLANK']) {
            $arItem['_LINK_ATTR1_'] .= ' target="_blank"';
        }
        $arItem['_LINK_ATTR2_'] = $arItem['_LINK_ATTR1_'];
        $arItem['_LINK_ATTR2_'] .= ' title="'.$arItem['NAME'].'"';
        if($arItem['NESTED']) {
            $arItem['NESTED'] = $funcSelf($arItem['NESTED'], $funcSelf);
        }
    }
    unset($arItem);

    return $arData;    
};
$arResult['MENU_TREE'] = $funcWalkRecursive($arResult['MENU_TREE'], $funcWalkRecursive);

// масштабирование изображений
if ($arResult['SECTIONS_POPULAR_BRANDS']) {
    foreach ($arResult['SECTIONS_POPULAR_BRANDS'] as &$arBrandsList) {
        foreach($arBrandsList as &$arItem) {
            $mImgField = false;
            if (!empty($arItem['PREVIEW_PICTURE']) || !empty($arItem['DETAIL_PICTURE'])) {
                $mImgField = !empty($arItem['PREVIEW_PICTURE']) ? $arItem['PREVIEW_PICTURE'] : $arItem['DETAIL_PICTURE'];
            }
            $arItem['PRINT_PICTURE'] = $mImgField && is_array($mImgField) ? $mImgField : array();
            if ($mImgField) {
                if (!empty($arParams['RESIZE_WIDTH']) && !empty($arParams['RESIZE_HEIGHT'])) {
                    try {
                        $bCrop = isset($arParams['RESIZE_TYPE']) && $arParams['RESIZE_TYPE'] == 'BX_RESIZE_IMAGE_EXACT';
                        if ($bCrop) {
                            if (is_array($mImgField)) {
                                $obImg = new \FourPaws\BitrixOrm\Model\CropImageDecorator($mImgField);
                            } else {
                                $obImg = \FourPaws\BitrixOrm\Model\CropImageDecorator::createFromPrimary($mImgField);
                            }
                            $obImg->setCropWidth($arParams['RESIZE_WIDTH']);
                            $obImg->setCropHeight($arParams['RESIZE_HEIGHT']);
                        } else {
                            if (is_array($mImgField)) {
                                $obImg = new \FourPaws\BitrixOrm\Model\ResizeImageDecorator($mImgField);
                            } else {
                                $obImg = \FourPaws\BitrixOrm\Model\ResizeImageDecorator::createFromPrimary($mImgField);
                            }
                            $obImg->setResizeWidth($arParams['RESIZE_WIDTH']);
                            $obImg->setResizeHeight($arParams['RESIZE_HEIGHT']);
                        }

                        $arItem['PRINT_PICTURE'] = array(
                            'SRC' => $obImg->getSrc(),
                            'TITLE' => isset($mImgField['TITLE']) ? $mImgField['TITLE'] : $arItem['NAME'],
                            'ALT' => isset($mImgField['ALT']) ? $mImgField['ALT'] : $arItem['NAME'],
                        );
                    } catch (\Exception $obException) {}
                }
            }
        }
        unset($arItem);
    }
    unset($arBrandsList);
}
