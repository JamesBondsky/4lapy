<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\BitrixOrm\Model\ResizeImageDecorator;

/**
 * @var array $arParams
 * @var array $arResult
 */

if (!\is_array($arResult['ITEMS']) || empty($arResult['ITEMS'])) {
    return;
}

$getImage = function ($id, $width) {
    if (!$id || (int)$id != $id) {
        return false;
    }
    /** @noinspection PhpUnhandledExceptionInspection */
    $image = ResizeImageDecorator::createFromPrimary($id);
    $proportions = $image->getHeight() / $image->getWidth();

    return $image->setResizeWidth($width)
                 ->setResizeHeight($width * $proportions);
};

foreach ($arResult['ITEMS'] as $i => $item) {
    $arResult['ITEMS'][$i]['DESKTOP_PICTURE'] = $getImage($item['~DETAIL_PICTURE'], 1020);
    $arResult['ITEMS'][$i]['TABLET_PICTURE'] = $getImage($item['DISPLAY_PROPERTIES']['IMG_TABLET']['VALUE'], 756);
    $arResult['ITEMS'][$i]['MOBILE_PICTURE'] = $getImage($item['~PREVIEW_PICTURE'], 767);
}

