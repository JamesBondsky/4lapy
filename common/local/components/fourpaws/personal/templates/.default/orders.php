<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
$APPLICATION->IncludeComponent(
    'fourpaws:personal.orders',
    '',
    [],
    $component,
    ['HIDE_ICONS' => 'Y']
);