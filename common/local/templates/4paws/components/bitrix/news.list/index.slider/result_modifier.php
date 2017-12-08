<?php use FourPaws\BitrixOrm\Model\CropImageDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 */

if(\is_array($arResult['ITEMS']) && !empty($arResult['ITEMS'])) {
    foreach ($arResult['ITEMS'] as &$item) {
        if (\is_array($item['DETAIL_PICTURE']) && !empty($item['DETAIL_PICTURE'])) {
            $image = new CropImageDecorator($arResult['DETAIL_PICTURE']);
            $image->setCropWidth(1440)->setCropHeight(300);
            $item['PICTURE'] = $image;
        }
    }
}
