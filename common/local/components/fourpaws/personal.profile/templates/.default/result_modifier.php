<?php

use FourPaws\KioskBundle\Service\KioskService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var \CMain $APPLICATION */
/** @var array $arResult */

$appCont = \FourPaws\App\Application::getInstance()->getContainer();
$arResult['isAvatarAuthorized'] = $appCont->get(
    \FourPaws\UserBundle\Service\CurrentUserProviderInterface::class
)->isAvatarAuthorized();
$arResult['kiosk'] = KioskService::isKioskMode();

$arResult['canEditSocial'] = !$arResult['isAvatarAuthorized'] && !$arResult['kiosk'];
$arResult['canEditSubscribe'] = !$arResult['isAvatarAuthorized'] && !$arResult['kiosk'];
$arResult['canEditProfile'] = !$arResult['isAvatarAuthorized'] && !$arResult['kiosk'];
