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

    $APPLICATION->IncludeComponent('fourpaws:city.selector', 'popup', [], false, ['HIDE_ICONS' => 'Y']);
    $APPLICATION->IncludeComponent('fourpaws:auth.form', 'popup', [], false, ['HIDE_ICONS' => 'Y']);

    if ($template->hasPersonalReferral()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.referral', 'popup', [], $component, ['HIDE_ICONS' => 'Y']);
    }
    if ($template->hasPersonalAddress()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.address', 'popup', [], $component, ['HIDE_ICONS' => 'Y']);
    }
    if ($template->hasPersonalPet()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.pets', 'popup', [], $component, ['HIDE_ICONS' => 'Y']);
    }
    if ($template->hasPersonalProfile()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupChangePassword', [], $component,
                                       ['HIDE_ICONS' => 'Y']);
        $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupChangeData', [], $component,
                                       ['HIDE_ICONS' => 'Y']);
        $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupChangePhone', [], $component,
                                       ['HIDE_ICONS' => 'Y']);
    }
    if ($template->isOrderDeliveryPage()) {
        $APPLICATION->IncludeComponent('fourpaws:order.shop.list', 'popup', [], $component, ['HIDE_ICONS' => 'Y']);
    } ?>
    <?php include 'tmp_gifts_popup.php' ?>
    <?php include 'modal_popup.php' ?>
</div>
