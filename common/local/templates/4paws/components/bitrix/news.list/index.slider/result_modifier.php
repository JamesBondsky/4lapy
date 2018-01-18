<?php

use FourPaws\BitrixOrm\Model\CropImageDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 */

if (empty($arResult['ITEMS']) || !\is_array($arResult['ITEMS'])) {
    return;
}

foreach ($arResult['ITEMS'] as &$item) {
    if (isset($image)) {
        unset($image);
    }
    // изображение для десктопа
    if (!empty($item['DETAIL_PICTURE']) && is_array($item['DETAIL_PICTURE'])) {
        $image = new CropImageDecorator($item['DETAIL_PICTURE']);
    } elseif (is_numeric($item['~DETAIL_PICTURE']) && (int)$item['~DETAIL_PICTURE'] > 0) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $image = CropImageDecorator::createFromPrimary($item['~DETAIL_PICTURE']);
    }
    if ($image instanceof CropImageDecorator) {
        $image->setCropWidth(1440)->setCropHeight(300);
        $item['DESKTOP_PICTURE'] = $image;
    }

    // изображение для мобильного
    if (!empty($item['PREVIEW_PICTURE']) && is_array($item['PREVIEW_PICTURE'])) {
        $image = new CropImageDecorator($item['PREVIEW_PICTURE']);
    } elseif (is_numeric($item['~PREVIEW_PICTURE']) && (int)$item['~PREVIEW_PICTURE'] > 0) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $image = CropImageDecorator::createFromPrimary($item['~PREVIEW_PICTURE']);
    }
    if ($image instanceof CropImageDecorator) {
        $image->setCropWidth(1440)->setCropHeight(300);
        $item['MOBILE_PICTURE'] = $image;
    }

    // изображение для планшета
    if (!empty($item['DISPLAY_PROPERTIES']['IMG_TABLET']['FILE_VALUE']) && is_array($item['DISPLAY_PROPERTIES']['IMG_TABLET']['FILE_VALUE'])) {
        $image = new CropImageDecorator($item['DISPLAY_PROPERTIES']['IMG_TABLET']['FILE_VALUE']);
    } elseif (is_numeric($item['DISPLAY_PROPERTIES']['IMG_TABLET']['VALUE']) && (int)$item['DISPLAY_PROPERTIES']['IMG_TABLET']['VALUE'] > 0) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $image = CropImageDecorator::createFromPrimary($item['DISPLAY_PROPERTIES']['IMG_TABLET']['VALUE']);
    }
    if ($image instanceof CropImageDecorator) {
        $image->setCropWidth(1440)->setCropHeight(300);
        $item['TABLET_PICTURE'] = $image;
    }
}
unset($item);
