<?php

use FourPaws\BitrixOrm\Model\CropImageDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!\is_array($arResult['ITEMS']) || empty($arResult['ITEMS'])) {
    return;
}

foreach ($arResult['ITEMS'] as $key => &$item) {
    if (is_array($item['PREVIEW_PICTURE'])) {
        $image = new CropImageDecorator($item['PREVIEW_PICTURE']);
    } elseif (is_numeric($item['~PREVIEW_PICTURE']) && (int)$item['~PREVIEW_PICTURE'] > 0) {
        $image = CropImageDecorator::createFromPrimary($item['~PREVIEW_PICTURE']);
    }
    if ($image instanceof CropImageDecorator) {
        if ($key === 0) {
            $image->setCropWidth(630)->setCropHeight(210);
        } else {
            $image->setCropWidth(305)->setCropHeight(120);
        }
        $item['PREVIEW_PICTURE']['SRC'] = $image;
    }
}
unset($item);