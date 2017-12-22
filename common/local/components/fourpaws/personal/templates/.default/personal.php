<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<?php $APPLICATION->IncludeComponent(
    'fourpaws:personal.profile',
    '',
    [
    ],
    $component,
    ['HIDE_ICONS' => 'Y']
); ?>
