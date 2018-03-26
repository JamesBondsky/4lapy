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

    $APPLICATION->IncludeComponent('fourpaws:city.selector', 'popup', [], null, ['HIDE_ICONS' => 'Y']);
    $APPLICATION->IncludeComponent('fourpaws:auth.form', 'popup', [], null, ['HIDE_ICONS' => 'Y']);

    if ($template->hasPersonalReferral()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.referral', 'popup', [], null, ['HIDE_ICONS' => 'Y']);
    }
    if ($template->hasPersonalAddress()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.address', 'popup', [], null, ['HIDE_ICONS' => 'Y']);
    }
    if ($template->hasPersonalPet()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.pets', 'popup', [], null, ['HIDE_ICONS' => 'Y']);
    }
    if ($template->hasPersonalProfile()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupChangePassword', [], null,
            ['HIDE_ICONS' => 'Y']);
        $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupChangeData', [], null,
            ['HIDE_ICONS' => 'Y']);
        $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupChangePhone', [], null,
            ['HIDE_ICONS' => 'Y']);
    }
    if ($template->hasOrderDeliveryPage()) {
        $APPLICATION->IncludeComponent('fourpaws:order.shop.list', 'popup', [], null, ['HIDE_ICONS' => 'Y']);
    }
    if ($template->hasFastOrder()) {
        $APPLICATION->IncludeComponent('fourpaws:fast.order', '', [], null, ['HIDE_ICONS' => 'Y']);
    }

    include __DIR__ . '/gifts_popup.php';
    include __DIR__ . '/modal_popup.php';
    ?>
</div>
