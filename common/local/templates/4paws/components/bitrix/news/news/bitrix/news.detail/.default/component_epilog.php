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

$APPLICATION->AddViewContent('news-detail-description', $arResult['PREVIEW_TEXT'] ?? '');
$APPLICATION->AddViewContent('news-detail-image', new \FourPaws\Decorators\FullHrefDecorator($arResult['DETAIL_PICTURE']['SRC']) ?? '');