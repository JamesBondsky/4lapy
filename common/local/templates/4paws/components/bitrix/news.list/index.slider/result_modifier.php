<?php use FourPaws\BitrixOrm\Model\CropImageDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 */

if (!\is_array($arResult['ITEMS']) || empty($arResult['ITEMS'])) {
    return;
}

foreach ($arResult['ITEMS'] as &$item) {
    if (isset($image)) {
        unset($image);
    }
    if (is_array($item['DETAIL_PICTURE']) && !empty($item['DETAIL_PICTURE'])) {
        $image = new CropImageDecorator($item['DETAIL_PICTURE']);
    } elseif (is_numeric($item['~DETAIL_PICTURE']) && (int)$item['~DETAIL_PICTURE'] > 0) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $image = CropImageDecorator::createFromPrimary($item['~DETAIL_PICTURE']);
    }
    if ($image instanceof CropImageDecorator) {
        $image->setCropWidth(1440)->setCropHeight(300);
        $item['PICTURE'] = $image;
    }
}
