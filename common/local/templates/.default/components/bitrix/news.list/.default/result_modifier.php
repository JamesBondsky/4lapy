<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Список элементов в разделах: Акции, Новости, Статьи
 *
 * @updated: 10.01.2018
 */

if (!$arResult['ITEMS']) {
    return;
}

$arParams['RESIZE_WIDTH'] = isset($arParams['RESIZE_WIDTH']) ? $arParams['RESIZE_WIDTH'] : 305;
$arParams['RESIZE_HEIGHT'] = isset($arParams['RESIZE_HEIGHT']) ? $arParams['RESIZE_HEIGHT'] : 120;
$arParams['RESIZE_TYPE'] = isset($arParams['RESIZE_TYPE']) ? $arParams['RESIZE_TYPE'] : 'BX_RESIZE_IMAGE_EXACT';

foreach ($arResult['ITEMS'] as &$arItem) {
    $mImgField = false;
    if ($arItem['PREVIEW_PICTURE'] || $arItem['DETAIL_PICTURE']) {
        $mImgField = $arItem['DETAIL_PICTURE'] ? $arItem['DETAIL_PICTURE'] : $arItem['PREVIEW_PICTURE'];
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
                    'TITLE' => isset($mImgField['TITLE']) ? $mImgField['TITLE'] : '',
                    'ALT' => isset($mImgField['ALT']) ? $mImgField['ALT'] : $arItem['NAME'],
                );
            } catch (\Exception $obException) {}
        }
    }

    $arItem['PRINT_PUBLICATION_TYPES'] = [];
    if (isset($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])) {
        if (is_array($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])) {
            foreach ($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'] as $sVal) {
                if (strlen(trim($sVal))) {
                    $arItem['PRINT_PUBLICATION_TYPES'][] = $sVal;
                }
            }
        } else {
            if (strlen(trim($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE']))) {
                $arItem['PRINT_PUBLICATION_TYPES'][] = $arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'];
            }
        }
    }
    if (!$arItem['PRINT_PUBLICATION_TYPES'] && !empty($arParams['DEFAULT_PUBLICATION_TYPE_VALUE'])) {
        $arItem['PRINT_PUBLICATION_TYPES'][] = $arParams['DEFAULT_PUBLICATION_TYPE_VALUE'];
    }
}
unset($arItem);
