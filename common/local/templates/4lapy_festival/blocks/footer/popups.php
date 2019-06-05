<?php

use Bitrix\Main\Application;
use FourPaws\App\MainTemplate;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var MainTemplate $template */
/** @var CMain $APPLICATION */
/** @noinspection PhpUnhandledExceptionInspection */
$template = MainTemplate::getInstance(Application::getInstance()->getContext()); ?>
<div class="b-popup-wrapper b-popup-wrapper--festival js-popup-wrapper">
    <?php
    /**
     * Область для вставки инлайновых попапов
     */
    $APPLICATION->ShowViewContent('footer_popup_cont');

    $APPLICATION->IncludeComponent('fourpaws:auth.form', 'popup', ['BACK_URL_HASH' => 'registr-check'], null, ['HIDE_ICONS' => 'Y']);
    $APPLICATION->IncludeComponent('fourpaws:information.popup', '', [], false, ['HIDE_ICONS' => 'Y']);

    include __DIR__ . '/form-festival.php';
    include __DIR__ . '/forgot-passport-number.php';
    include __DIR__ . '/response-form-festival.php';

    include __DIR__ . '/response-feedback-form-landing.php';

    include __DIR__ . '/modal_popup.php';

    include __DIR__ . '/schedule-bloggershow.php';
    include __DIR__ . '/schedule-gala.php';
    include __DIR__ . '/schedule-dog-fest.php';
    include __DIR__ . '/schedule-map-lecturehall.php';
    ?>
</div>
