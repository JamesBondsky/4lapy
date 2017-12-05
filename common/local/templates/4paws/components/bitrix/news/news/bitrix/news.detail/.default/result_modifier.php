<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @noinspection PhpUndefinedClassInspection */
/** @global \CUser $USER */
/** @global \CMain $APPLICATION */
/** @global CDatabase $DB */

$arResult['NO_SHOW_VIDEO'] = false;
if (stripos($arResult['DETAIL_TEXT'], '#video#') !== false) {
    $arResult['DETAIL_TEXT']   = str_replace('#video#',
                                             !empty($arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE']) ? $arResult['DISPLAY_PROPERTIES']['VIDEO']['DISPLAY_VALUE'] : '',
                                             $arResult['DETAIL_TEXT']);
    $arResult['NO_SHOW_VIDEO'] = true;
}

$arResult['NO_SHOW_SLIDER'] = false;
if (is_array($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])
    && !empty($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'])
    && stripos($arResult['DETAIL_TEXT'], '#slider#') !== false) {
    $html = '';
    foreach ((array)$arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']['DISPLAY_VALUE'] as $photo) {
        if (is_numeric($photo)) {
            /** @noinspection PhpUndefinedClassInspection */
            $photo = ['SRC' => CFile::GetPath($photo)];
        }
        if (!empty($photo['SRC'])) {
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

$this->__component->SetResultCacheKeys(['DISPLAY_ACTIVE_FROM']);