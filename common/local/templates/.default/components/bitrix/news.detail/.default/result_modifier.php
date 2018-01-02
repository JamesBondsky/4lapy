<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\BitrixOrm\Model\CropImageDecorator;

/**
 * Элемент детально в разделах: Акции, Новости, Статьи
 *
 * @updated: 01.01.2018
 */

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array $arResult
 */

$arResult['NO_SHOW_VIDEO'] = false;
if (stripos($arResult['DETAIL_TEXT'], '#video#') !== false) {
    $arResult['DETAIL_TEXT'] = str_replace(
        '#video#',
        !empty($arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE']) ? $arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'] : '',
        $arResult['DETAIL_TEXT']
    );
    $arResult['NO_SHOW_VIDEO'] = true;
}

$arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'] = [];
foreach ((array)$arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['VALUE'] as $key => $photo) {
    if ((int)$photo > 0) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $image = CropImageDecorator::createFromPrimary($photo);
        $image->setCropWidth(890)->setCropHeight(500);
        
        $arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'][$key] = [
            'ID'  => $image->getId(),
            'SRC' => $image->getSrc(),
        ];
    }
}

$image = null;
if (!empty($arResult['DETAIL_PICTURE']) && is_array($arResult['DETAIL_PICTURE'])) {
    $image = new CropImageDecorator($arResult['DETAIL_PICTURE']);
} elseif (is_numeric($arResult['~DETAIL_PICTURE']) && (int)$arResult['~DETAIL_PICTURE'] > 0) {
    /** @noinspection PhpUnhandledExceptionInspection */
    $image = CropImageDecorator::createFromPrimary($arResult['~DETAIL_PICTURE']);
}
if ($image instanceof CropImageDecorator) {
    $image->setCropWidth(890)->setCropHeight(500);
    $arResult['DETAIL_PICTURE']['SRC'] = $image;
}

$arResult['NO_SHOW_SLIDER'] = false;
if (!empty($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])) {
    if (is_array($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE']) && stripos($arResult['DETAIL_TEXT'], '#slider#') !== false) {
        $html = '';
        foreach ($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'] as $photo) {
            if (!is_numeric($photo)) {
                $html .= '<div class="b-detail-page-slider__item">';
                $html .= '<img src="' . $photo['SRC'] . '" alt="">';
                $html .= '</div>';
            }
        }
        if (!empty($html)) {
            $arResult['DETAIL_TEXT'] = str_replace('#slider#', $html, $arResult['DETAIL_TEXT']);
            $arResult['NO_SHOW_SLIDER'] = true;
        }
    }
}

if (!empty($arResult['ACTIVE_FROM']) && !empty($arResult['ACTIVE_TO'])) {
    $arResult['DISPLAY_ACTIVE_FROM'] = '';
    $arResult['DISPLAY_ACTIVE_FROM'] .= \CIBlockFormatProperties::DateFormat($arParams['ACTIVE_DATE_FORMAT'], MakeTimeStamp($arResult['ACTIVE_FROM'], \CSite::GetDateFormat()));
    $arResult['DISPLAY_ACTIVE_FROM'] .= '&nbsp;&mdash; ';
    $arResult['DISPLAY_ACTIVE_FROM'] .= \CIBlockFormatProperties::DateFormat($arParams['ACTIVE_DATE_FORMAT'], MakeTimeStamp($arResult['ACTIVE_TO'], \CSite::GetDateFormat()));
    $arResult['DISPLAY_ACTIVE_FROM'] = ToLower($arResult['DISPLAY_ACTIVE_FROM']);
}

/**  DETAIL_PICTURE и PREVIEW_TEXT для отправки в соц сети */
$this->__component->setResultCacheKeys(
    [
        'DISPLAY_ACTIVE_FROM',
        'DETAIL_PICTURE',
        'PREVIEW_TEXT',
    ]
);
