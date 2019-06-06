<?php

use FourPaws\Decorators\FullHrefDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Элемент детально в разделах: Акции, Новости, Статьи
 *
 * @updated: 01.01.2018
 */


global $APPLICATION;
if (!empty($arResult['DISPLAY_ACTIVE_FROM'])) {
    $sVal = '<div class="b-detail-page__date">'.$arResult['DISPLAY_ACTIVE_FROM'].'</div>';
    $APPLICATION->AddViewContent('header_news_display_date', $sVal);
}

/** добавляем для отправки в соц сети */

$APPLICATION->AddViewContent('social-share-description', $arResult['PREVIEW_TEXT'] ?? '');
$APPLICATION->AddViewContent(
    'social-share-image',
    !empty($arResult['DETAIL_PICTURE']['SRC']) ? new FullHrefDecorator($arResult['DETAIL_PICTURE']['SRC']) : ''
);

$activeTo = new DateTime($arResult['ACTIVE_TO']);
$currentDate = new DateTime();


if (($activeTo && $activeTo < $currentDate && $arResult['ACTIVE_TO']) || $arResult['ACTIVE'] != 'Y') {
    if (isset($arParams['URL_REDIRECT_404'])) {
        LocalRedirect($arParams['URL_REDIRECT_404']);
        return;
    }
} else {
    if ($arResult['ACTIVE'] != 'Y') {
        Bitrix\Iblock\Component\Tools::process404(
            trim($arParams["MESSAGE_404"]) ?: GetMessage("T_NEWS_DETAIL_NF")
            , true
            , true
            , true
            , $arParams["FILE_404"]
        );
    }
}