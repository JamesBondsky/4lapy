<?php

use FourPaws\BitrixOrm\Model\CropImageDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */

/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

$arResult['NO_SHOW_VIDEO'] = false;
if (stripos($arResult['DETAIL_TEXT'], '#video#') !== false) {
    $arResult['DETAIL_TEXT']   = str_replace(
        '#video#',
        !empty($arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE']) ? $arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'] : '',
        $arResult['DETAIL_TEXT']
    );
    $arResult['NO_SHOW_VIDEO'] = true;
}

foreach ((array)$arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'] as &$photo) {
    if (is_numeric($photo) && (int)$photo > 0) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $image = CropImageDecorator::createFromPrimary($photo);
        $image->setCropWidth(890)->setCropHeight(500);
        
        $photo = [
            'ID'  => $photo,
            'SRC' => $image,
        ];
    }
}

if (is_array($arResult['DETAIL_PICTURE']) && !empty($arResult['DETAIL_PICTURE'])) {
    if (is_array($arResult['DETAIL_PICTURE']) && !empty($arResult['DETAIL_PICTURE'])) {
        $image = new CropImageDecorator($arResult['DETAIL_PICTURE']);
    } elseif (is_numeric($arResult['~DETAIL_PICTURE']) && (int)$arResult['~DETAIL_PICTURE'] > 0) {
        $image = CropImageDecorator::createFromPrimary($arResult['~DETAIL_PICTURE']);
    }
    if ($image instanceof CropImageDecorator) {
        $image->setCropWidth(890)->setCropHeight(500);
        $arResult['DETAIL_PICTURE']['SRC'] = $image;
    }
}

$arResult['NO_SHOW_SLIDER'] = false;
if (is_array($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])
    && !empty($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])
    && stripos($arResult['DETAIL_TEXT'], '#slider#') !== false) {
    $html = '';
    foreach ((array)$arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'] as $photo) {
        if (is_numeric($photo)) {
            $html .= '
            <div class="b-detail-page-slider__item">
                <img src="' . $photo['SRC'] . '" />
            </div>
        ';
        }
    }
    $arResult['DETAIL_TEXT']    = str_replace('#slider#', $html, $arResult['DETAIL_TEXT']);
    $arResult['NO_SHOW_SLIDER'] = true;
}

/**  DETAIL_PICTURE и PREVIEW_TEXT для отправки в соц сети */
$this->__component->setResultCacheKeys(
    [
        'DISPLAY_ACTIVE_FROM',
        'DETAIL_PICTURE',
        'PREVIEW_TEXT',
    ]
);