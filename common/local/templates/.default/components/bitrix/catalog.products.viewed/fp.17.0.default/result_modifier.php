<?php

use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var array $arParams
 * @var array $arResult
 * @global CDatabase $DB
 */

$arResult['PRINT_ITEMS'] = [];
$arParams['RESIZE_WIDTH'] = $arParams['RESIZE_WIDTH'] ?? 110;
$arParams['RESIZE_HEIGHT'] = $arParams['RESIZE_HEIGHT'] ?? 110;
$arParams['RESIZE_TYPE'] = $arParams['RESIZE_TYPE'] ?? 'BX_RESIZE_IMAGE_PROPORTIONAL_ALT';

if (!$arResult['ITEMS']) {
    return;
}

foreach ($arResult['ITEMS'] as $item) {
    if (empty($item['OFFERS'])) {
        continue;
    }

    $printOffer = [];
    foreach ($item['OFFERS'] as $offer) {
        if ($item['OFFER_ID_SELECTED']) {
            if ($offer['ID'] == $item['OFFER_ID_SELECTED']) {
                $printOffer = $offer;
            }
        } else {
            if ($offer['PRODUCT']['AVAILABLE'] === 'Y') {
                $printOffer = $offer;
            }
        }

        if ($printOffer) {
            break;
        }
    }

    if ($printOffer) {
        $img = null;
        $imgField = [];

        if (!empty($printOffer['DISPLAY_PROPERTIES']['IMG']['FILE_VALUE'][0])) {
            $imgField = $printOffer['DISPLAY_PROPERTIES']['IMG']['FILE_VALUE'][0];
        } elseif (!empty($printOffer['DISPLAY_PROPERTIES']['IMG']['FILE_VALUE'])) {
            $imgField = $printOffer['DISPLAY_PROPERTIES']['IMG']['FILE_VALUE'];
        }

        if ($imgField && !empty($arParams['RESIZE_WIDTH']) && !empty($arParams['RESIZE_HEIGHT'])) {
            try {
                $isCrop = isset($arParams['RESIZE_TYPE']) && $arParams['RESIZE_TYPE'] === 'BX_RESIZE_IMAGE_EXACT';
                if ($isCrop) {
                    $img = is_array($imgField) ? new CropImageDecorator($imgField) : CropImageDecorator::createFromPrimary($imgField);
                    $img->setCropWidth($arParams['RESIZE_WIDTH']);
                    $img->setCropHeight($arParams['RESIZE_HEIGHT']);
                } else {
                    $img = is_array($imgField) ? new ResizeImageDecorator($imgField) : ResizeImageDecorator::createFromPrimary($imgField);
                    $img->setResizeWidth($arParams['RESIZE_WIDTH']);
                    $img->setResizeHeight($arParams['RESIZE_HEIGHT']);
                }
            } catch (Exception $e) {
            }
        }

        $brandName = '';
        if (!empty($item['DISPLAY_PROPERTIES']['BRAND']['DISPLAY_VALUE'])) {
            $brandName = strip_tags($item['DISPLAY_PROPERTIES']['BRAND']['DISPLAY_VALUE']);
        }

        $printImg = [];
        if ($img) {
            $printImg = [
                'SRC' => $img->getSrc(),
                'TITLE' => $brandName,
                'ALT' => $brandName,
            ];
        }

        $arResult['PRINT_ITEMS'][] = [
            'NAME' => $printOffer['NAME'],
            'BRAND_NAME' => $brandName,
            'DETAIL_PAGE_URL' => $item['DETAIL_PAGE_URL'],
            'IMG' => $printImg,
        ];
    }
}
