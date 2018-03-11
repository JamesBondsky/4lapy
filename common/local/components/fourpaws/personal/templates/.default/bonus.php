<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
$APPLICATION->IncludeComponent(
    'fourpaws:personal.bonus',
    '',
    [],
    $component,
    ['HIDE_ICONS' => 'Y']
);