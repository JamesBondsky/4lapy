<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array                     $arParams
 * @var array                     $arResult
 */

foreach ($arResult['ITEMS'] as &$item) {
    /**
     * @todo image resize helper
     */
    $item['PICTURE'] = $item['DETAIL_PICTURE']['SRC'];
}
