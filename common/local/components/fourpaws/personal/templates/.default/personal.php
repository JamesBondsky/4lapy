<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @var \CBitrixComponentTemplate $component
 *
 * @global CMain                  $APPLICATION
 */ ?>
<?php $APPLICATION->IncludeComponent(
    'fourpaws:personal.profile',
    '',
    [],
    $component,
    ['HIDE_ICONS' => 'Y']
); ?>
