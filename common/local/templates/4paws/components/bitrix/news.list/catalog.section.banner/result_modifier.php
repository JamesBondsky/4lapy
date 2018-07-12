<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\EcommerceBundle\Preset\Bitrix\MapperPreset;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;

/**
 * @var array $arParams
 * @var array $arResult
 */

if (!\is_array($arResult['ITEMS']) || empty($arResult['ITEMS'])) {
    return;
}

$container = Application::getInstance()->getContainer();
$ecommerceService = $container->get(GoogleEcommerceService::class);
$mapper = $container->get(MapperPreset::class)->mapperSliderFactory();

$arResult['ITEMS'] = [reset($arResult['ITEMS'])];

$arResult['ECOMMERCE_VIEW_SCRIPT'] = $ecommerceService->renderScript(
    $ecommerceService->buildPromotionFromArray($mapper, $arResult['ITEMS'], 'promoView'), true
);

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

foreach ($arResult['ITEMS'] as $i => &$item) {
    $item['ECOMMERCE_CLICK_SCRIPT'] = $ecommerceService->renderScript(
        $ecommerceService->buildPromotionFromArray($mapper, [$item], 'promoClick'), false
    );

    $arResult['ITEMS'][$i]['DESKTOP_PICTURE'] = $getImage($item['~DETAIL_PICTURE'], 1020);
    $arResult['ITEMS'][$i]['TABLET_PICTURE'] = $getImage($item['DISPLAY_PROPERTIES']['IMG_TABLET']['VALUE'], 756);
    $arResult['ITEMS'][$i]['MOBILE_PICTURE'] = $getImage($item['~PREVIEW_PICTURE'], 767);
}
