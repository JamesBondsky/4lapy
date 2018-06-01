<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */
/** @var \CMain $APPLICATION */
/** @var array $arResult */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$APPLICATION->IncludeComponent(
    'fourpaws:personal.pets',
    '',
    [
        'isAvatarAuthorized' => $arResult['isAvatarAuthorized'],
    ],
    $component,
    ['HIDE_ICONS' => 'Y']
);
