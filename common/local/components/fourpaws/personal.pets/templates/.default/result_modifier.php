<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var \CMain $APPLICATION */
/** @var array $arResult */

$appCont = \FourPaws\App\Application::getInstance()->getContainer();
$arResult['isAvatarAuthorized'] = $appCont->get(
    \FourPaws\UserBundle\Service\CurrentUserProviderInterface::class
)->isAvatarAuthorized();

$arResult['canEdit'] = !$arResult['isAvatarAuthorized'];
$arResult['canAdd'] = !$arResult['isAvatarAuthorized'];
