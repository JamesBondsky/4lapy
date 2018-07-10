<?php

use FourPaws\App\Application;
use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;

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

$ecommerceService = Application::getInstance()->getContainer()->get(GoogleEcommerceService::class);
$mapper = $ecommerceService->getArrayMapper([
    'id' => function ($item, $k) {
        return $item['CODE'] ?: $item['ID'];
    },
    'name' => 'NAME',
    'creative' => 'NAME',
    'position' => function ($item, $k) {
        return \sprintf(
            'slot%d',
            $k + 1
        );
    }
]);

$arResult['ECOMMERCE_VIEW_SCRIPT'] = $ecommerceService->renderScript(
    $ecommerceService->buildPromotionFromArray($mapper, $arResult['ITEMS'], 'promoView'), true
);

foreach ($arResult['ITEMS'] as &$item) {
    $item['ECOMMERCE_CLICK_SCRIPT'] = $ecommerceService->renderScript(
        $ecommerceService->buildPromotionFromArray($mapper, [$item], 'promoClick'), false
    );
    dump($item['ECOMMERCE_CLICK_SCRIPT']);

    // изображение для десктопа
    $image = null;
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
    $image = null;
    if (!empty($item['PREVIEW_PICTURE']) && is_array($item['PREVIEW_PICTURE'])) {
        $image = new CropImageDecorator($item['PREVIEW_PICTURE']);
    } elseif (is_numeric($item['~PREVIEW_PICTURE']) && (int)$item['~PREVIEW_PICTURE'] > 0) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $image = CropImageDecorator::createFromPrimary($item['~PREVIEW_PICTURE']);
    }
    if ($image instanceof CropImageDecorator) {
        $image->setCropWidth(414)->setCropHeight(207);
        $item['MOBILE_PICTURE'] = $image;
    }

    // изображение для планшета
    $image = null;
    if (!empty($item['DISPLAY_PROPERTIES']['IMG_TABLET']['FILE_VALUE']) && is_array($item['DISPLAY_PROPERTIES']['IMG_TABLET']['FILE_VALUE'])) {
        $image = new CropImageDecorator($item['DISPLAY_PROPERTIES']['IMG_TABLET']['FILE_VALUE']);
    } elseif (is_numeric($item['DISPLAY_PROPERTIES']['IMG_TABLET']['VALUE']) && (int)$item['DISPLAY_PROPERTIES']['IMG_TABLET']['VALUE'] > 0) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $image = CropImageDecorator::createFromPrimary($item['DISPLAY_PROPERTIES']['IMG_TABLET']['VALUE']);
    }
    if ($image instanceof CropImageDecorator) {
        $image->setCropWidth(768)->setCropHeight(250);
        $item['TABLET_PICTURE'] = $image;
    }

    //фон
    $image = null;
    if (!empty($item['DISPLAY_PROPERTIES']['BACKGROUND']['FILE_VALUE']) && is_array($item['DISPLAY_PROPERTIES']['BACKGROUND']['FILE_VALUE'])) {
        $image = new CropImageDecorator($item['DISPLAY_PROPERTIES']['BACKGROUND']['FILE_VALUE']);
    } elseif (is_numeric($item['DISPLAY_PROPERTIES']['BACKGROUND']['VALUE']) && (int)$item['DISPLAY_PROPERTIES']['BACKGROUND']['VALUE'] > 0) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $image = CropImageDecorator::createFromPrimary($item['DISPLAY_PROPERTIES']['BACKGROUND']['VALUE']);
    }

    if ($image instanceof CropImageDecorator) {
        $image->setCropHeight(300);
        $item['BACKGROUND'] = $image;
    }
}

unset($item);
