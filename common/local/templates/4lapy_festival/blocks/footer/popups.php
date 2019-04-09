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
<div class="b-popup-wrapper js-popup-wrapper">
    <?php
    /**
     * Область для вставки инлайновых попапов
     */
    $APPLICATION->ShowViewContent('footer_popup_cont');

    $APPLICATION->IncludeComponent('fourpaws:auth.form', 'popup', ['BACK_URL_HASH' => 'registr-check'], null, ['HIDE_ICONS' => 'Y']);
    $APPLICATION->IncludeComponent('fourpaws:information.popup', '', [], false, ['HIDE_ICONS' => 'Y']);

    include __DIR__ . '/response-feedback-form-landing.php';

    include __DIR__ . '/modal_popup.php';
    ?>
</div>
