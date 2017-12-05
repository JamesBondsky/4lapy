<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;
$html = '';
if (!empty($arResult['DISPLAY_ACTIVE_FROM'])) {
    ob_start(); ?>
    <div class="b-detail-page__date"><?= $arResult['DISPLAY_ACTIVE_FROM'] ?></div>
    <?php $html = ob_get_clean();
}
$APPLICATION->AddViewContent('header_news_display_date', $html);

/** добавляем для отправки в соц сети */

use Bitrix\Main\Application;
use FourPaws\App\MainTemplate;

$template = MainTemplate::getInstance(Application::getInstance()->getContext());
$APPLICATION->AddViewContent('news-detail-description', $arResult['PREVIEW_TEXT'] ?? '');
$APPLICATION->AddViewContent('news-detail-image', $template->getAbsolutePublicPath($arResult['DETAIL_PICTURE']['SRC']) ?? '');