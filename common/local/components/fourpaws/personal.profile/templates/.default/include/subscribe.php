<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var Cmain $APPLICATION */ ?>
<?php $APPLICATION->IncludeComponent(
    'fourpaws:expertsender.form',
    'profile',
    [],
    false,
    ['HIDE_ICONS' => 'Y']
); ?>
