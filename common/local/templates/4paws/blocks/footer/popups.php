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

    ?><section class="b-popup-pick-city js-popup-section" data-popup="pick-city"></section><?
    $APPLICATION->IncludeComponent('fourpaws:auth.form', 'popup', [], null, ['HIDE_ICONS' => 'Y']);
    $APPLICATION->IncludeComponent('fourpaws:information.popup', '', [], false, ['HIDE_ICONS' => 'Y']);
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

    if ($template->hasPiggyBank()) {
        include __DIR__ . '/popup_email_kopilka.php';
    }

    if ($template->hasPersonalOffers()) {
        include __DIR__ . '/personal_offers_form.php';
    }

    include __DIR__ . '/change-subscribe-delivery.php';
    include __DIR__ . '/catalog-subscribe-delivery.php';
    include __DIR__ . '/stop-subscribe-delivery.php';
    include __DIR__ . '/message-stop-subscribe-delivery.php';
    include __DIR__ . '/renew-subscribe-delivery.php';
    include __DIR__ . '/message-renew-subscribe-delivery.php';
    include __DIR__ . '/message-change-subscribe-delivery.php';

    include __DIR__ . '/gifts_popup.php';
    include __DIR__ . '/modal_popup.php';
    //include __DIR__ . '/change_view_popup.php'; // Временно скрываем баннер перехода в мобильную версию

    // собираем данные с ЛК с кучей и кучей условий, поэтому выносим отдельно..
    include __DIR__ . '/collect_data_popup.php';
    ?>

    <div class="b-popup-preloader b-popup-preloader--fixed js-popup-preloader">
        <div class="b-popup-preloader__spinner">
            <img class="b-popup-preloader__image" src="/static/build/images/inhtml/spinner.svg" alt="spinner" title="">
        </div>
    </div>
</div>
