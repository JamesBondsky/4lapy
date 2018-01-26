<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
$APPLICATION->IncludeComponent(
    'fourpaws:personal.referral',
    '',
    [],
    $component,
    ['HIDE_ICONS' => 'Y']
);