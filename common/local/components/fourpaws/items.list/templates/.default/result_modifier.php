<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\KioskBundle\Service\KioskService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!\is_array($arResult['ITEMS']) || empty($arResult['ITEMS'])) {
    return;
}

foreach ($arResult['ITEMS'] as $key => &$item) {
    if (isset($image)) {
        unset($image);
    }
    if (is_array($item['PREVIEW_PICTURE']) && !empty($item['PREVIEW_PICTURE'])) {
        $image = new CropImageDecorator($item['PREVIEW_PICTURE']);
    } elseif (is_numeric($item['~PREVIEW_PICTURE']) && (int)$item['~PREVIEW_PICTURE'] > 0) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $image = CropImageDecorator::createFromPrimary($item['~PREVIEW_PICTURE']);
    }
    if ($image instanceof CropImageDecorator) {
        if ($key === 0) {
            if (KioskService::isKioskMode()) {
                $cropWidth  = 480;
                $cropHeight = 220;
            }else {
                $cropWidth = 630;
                $cropHeight = 210;
            }
        } else {
            if (KioskService::isKioskMode()) {
                $cropWidth  = 230;
                $cropHeight = 105;
            }else {
                $cropWidth  = 305;
                $cropHeight = 120;
            }
        }
        $item['PREVIEW_PICTURE']['SRC'] = $image->setCropWidth($cropWidth)->setCropHeight($cropHeight)->getSrc();
    }
}
unset($item);
