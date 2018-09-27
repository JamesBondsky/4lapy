<?php use Adv\Bitrixtools\Tools\BitrixUtils;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Query\OfferQuery;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 * @var array $arParams
 */

foreach ($arResult['BASKET'] as $i => $item) {
    if ($item['IS_GIFT'] === BitrixUtils::BX_BOOL_TRUE) {
        unset($arResult['BASKET'][$i]);
        continue;
    }

    $offer = OfferQuery::getById($item['PRODUCT_ID']);
    /** @var ResizeImageDecorator $image */
    if ($image = $offer->getResizeImages(110, 110)->first()) {
        $arResult['BASKET'][$i]['IMAGE'] = $image->getSrc();
    }
    $arResult['BASKET'][$i]['BRAND'] = $offer->getProduct()->getBrandName();
}
