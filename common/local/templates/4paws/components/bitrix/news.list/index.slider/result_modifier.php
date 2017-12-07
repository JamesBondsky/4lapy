<?php use FourPaws\BitrixOrm\Model\CropImageDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 */

foreach ($arResult['ITEMS'] as &$item) {
    if (\is_array($item['DETAIL_PICTURE']) && !empty($item['DETAIL_PICTURE'])) {
        /** todo set crop sizes */
        $item['PICTURE'] = new CropImageDecorator($item['DETAIL_PICTURE']);
    }
}
