<?php

use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

/**
 * Популярные бренды на главной странице сайта
 *
 * @updated: 09.02.2018
 */

/** @var \CBitrixComponentTemplate $this */
/** @var array $arResult */

if (!$arResult['ITEMS']) {
    return;
}

$arParams['RESIZE_WIDTH'] = $arParams['RESIZE_WIDTH'] ?? 195;
$arParams['RESIZE_HEIGHT'] = $arParams['RESIZE_HEIGHT'] ?? 69;
$arParams['RESIZE_TYPE'] = $arParams['RESIZE_TYPE'] ?? 'BX_RESIZE_IMAGE_PROPORTIONAL';

foreach ($arResult['ITEMS'] as &$item) {
    $imgField = false;
    if ($item['PREVIEW_PICTURE'] || $item['DETAIL_PICTURE']) {
        $imgField = $item['PREVIEW_PICTURE'] ? $item['PREVIEW_PICTURE'] : $item['DETAIL_PICTURE'];
    }
    $item['PRINT_PICTURE'] = $imgField && is_array($imgField) ? $imgField : [];
    if ($imgField) {
        if (!empty($arParams['RESIZE_WIDTH']) && !empty($arParams['RESIZE_HEIGHT'])) {
            try {
                $isCrop = isset($arParams['RESIZE_TYPE']) && $arParams['RESIZE_TYPE'] == 'BX_RESIZE_IMAGE_EXACT';
                if ($isCrop) {
                    if (is_array($imgField)) {
                        $img = new CropImageDecorator($imgField);
                    } else {
                        $img = CropImageDecorator::createFromPrimary($imgField);
                    }
                    $img->setCropWidth($arParams['RESIZE_WIDTH']);
                    $img->setCropHeight($arParams['RESIZE_HEIGHT']);
                } else {
                    if (is_array($imgField)) {
                        $img = new ResizeImageDecorator($imgField);
                    } else {
                        $img = ResizeImageDecorator::createFromPrimary($imgField);
                    }
                    $img->setResizeWidth($arParams['RESIZE_WIDTH']);
                    $img->setResizeHeight($arParams['RESIZE_HEIGHT']);
                }

                $item['PRINT_PICTURE'] = array(
                    'SRC' => $img->getSrc(),
                    'TITLE' => $imgField['TITLE'] ?? '',
                    'ALT' => isset($imgField['ALT']) ? $imgField['ALT'] : $item['NAME'],
                );
            } catch (\Exception $exception) {}
        }
    }
}
unset($item);
