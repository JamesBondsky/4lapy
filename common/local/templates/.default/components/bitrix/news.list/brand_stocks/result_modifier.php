<?php

use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Список элементов в разделах: Акции, Новости, Статьи
 *
 * @updated: 10.01.2018
 */

if (!$arResult['ITEMS'] || !\is_array($arResult['ITEMS'])) {
    return;
}

$arParams['RESIZE_WIDTH'] = $arParams['RESIZE_WIDTH'] ?? 305;
$arParams['RESIZE_HEIGHT'] = $arParams['RESIZE_HEIGHT'] ?? 120;
$arParams['RESIZE_TYPE'] = $arParams['RESIZE_TYPE'] ?? 'BX_RESIZE_IMAGE_EXACT';

foreach ($arResult['ITEMS'] as &$item) {
    $mImgField = false;
    if ($item['PREVIEW_PICTURE'] || $item['DETAIL_PICTURE']) {
        $mImgField = $item['DETAIL_PICTURE'] ?: $item['PREVIEW_PICTURE'];
    }
    $item['PRINT_PICTURE'] = $mImgField && is_array($mImgField) ? $mImgField : [];
    if ($mImgField && !empty($arParams['RESIZE_WIDTH']) && !empty($arParams['RESIZE_HEIGHT'])) {
        try {
            $bCrop = isset($arParams['RESIZE_TYPE']) && $arParams['RESIZE_TYPE'] === 'BX_RESIZE_IMAGE_EXACT';
            if ($bCrop) {
                if (is_array($mImgField)) {
                    $obImg = new CropImageDecorator($mImgField);
                } else {
                    $obImg = CropImageDecorator::createFromPrimary($mImgField);
                }
                $obImg->setCropWidth($arParams['RESIZE_WIDTH']);
                $obImg->setCropHeight($arParams['RESIZE_HEIGHT']);
            } else {
                if (is_array($mImgField)) {
                    $obImg = new ResizeImageDecorator($mImgField);
                } else {
                    $obImg = ResizeImageDecorator::createFromPrimary($mImgField);
                }
                $obImg->setResizeWidth($arParams['RESIZE_WIDTH']);
                $obImg->setResizeHeight($arParams['RESIZE_HEIGHT']);
            }


            $item['PRINT_PICTURE'] = [
                'SRC'   => $obImg->getSrc(),
                'TITLE' => $mImgField['TITLE'] ?? '',
                'ALT'   => $mImgField['ALT'] ?? $item['NAME'],
            ];
        } catch (\Exception $obException) {
        }
    }

    $arItem['PRINT_PUBLICATION_TYPES'] = [];
    if (isset($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])) {
        if (is_array($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'])) {
            foreach ($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'] as $sVal) {
                if (!empty(trim($sVal))) {
                    $arItem['PRINT_PUBLICATION_TYPES'][] = $sVal;
                }
            }
        } else {
            if (!empty(trim($arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE']))) {
                $arItem['PRINT_PUBLICATION_TYPES'][] = $arItem['DISPLAY_PROPERTIES']['PUBLICATION_TYPE']['DISPLAY_VALUE'];
            }
        }
    }
    if (!$arItem['PRINT_PUBLICATION_TYPES'] && !empty($arParams['DEFAULT_PUBLICATION_TYPE_VALUE'])) {
        $arItem['PRINT_PUBLICATION_TYPES'][] = $arParams['DEFAULT_PUBLICATION_TYPE_VALUE'];
    }
}
unset($item);