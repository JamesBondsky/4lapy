<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\KioskBundle\Service\KioskService;

/**
 * Главное меню сайта
 * result_modifier.php
 *
 * @updated: 16.02.2018
 */

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var string $templateName
 * @var string $componentPath
 */

if (!$arResult['MENU_TREE']) {
    return;
}

$arParams['RESIZE_WIDTH'] = $arParams['RESIZE_WIDTH'] ?? 115;
$arParams['RESIZE_HEIGHT'] = $arParams['RESIZE_HEIGHT'] ?? 43;
$arParams['RESIZE_TYPE'] = $arParams['RESIZE_TYPE'] ?? 'BX_RESIZE_IMAGE_PROPORTIONAL';

$isKioskMode = KioskService::isKioskMode();

$funcWalkRecursive = static function ($arData, $funcSelf, $isKioskMode) {
    foreach ($arData as &$arItem) {
        $arItem['_TEXT_'] = $arItem['NAME'];
        $arItem['_URL_'] = $arItem['URL'] !== '' ? $arItem['URL'] : 'javascript:void(0);';
        $arItem['_LINK_ATTR1_'] = '';
        $arItem['_LINK_ATTR2_'] = '';

        if (!$isKioskMode && $arItem['TARGET_BLANK'] && $arItem['URL'] !== '') {
            $arItem['_LINK_ATTR1_'] .= ' target="_blank"';
        }
        $arItem['_LINK_ATTR2_'] .= ' title="' . $arItem['NAME'] . '"';

        if ($arItem['NESTED']) {
            $arItem['NESTED'] = $funcSelf($arItem['NESTED'], $funcSelf, $isKioskMode);
        }
    }
    unset($arItem);

    return $arData;    
};

$arResult['MENU_TREE'] = $funcWalkRecursive($arResult['MENU_TREE'], $funcWalkRecursive, $isKioskMode);

// масштабирование изображений
if ($arResult['SECTIONS_POPULAR_BRANDS']) {
    foreach ($arResult['SECTIONS_POPULAR_BRANDS'] as &$arBrandsList) {
        foreach ($arBrandsList as &$arItem) {
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

                        $arItem['PRINT_PICTURE'] = [
                            'SRC' => $obImg->getSrc(),
                            'TITLE' => $mImgField['TITLE'] ?? $arItem['NAME'],
                            'ALT' => $mImgField['ALT'] ?? $arItem['NAME'],
                        ];
                    } catch (\Exception $obException) {}
                }
            }
        }
        unset($arItem);
    }
    unset($arBrandsList);
}
