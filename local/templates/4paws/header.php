<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;use FourPaws\App\MainTemplate;

$template = MainTemplate::getInstance(Application::getInstance()->getContext());

?><!DOCTYPE html>
<html lang="ru">
<head>
    <?php $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->ShowTitle() ?></title>
</head>
<body>
<?php $APPLICATION->ShowPanel() ?>
<div class="b-page-wrapper js-this-scroll">
    <header class="b-header js-header">
        <div class="b-container">
            <?php $APPLICATION->IncludeComponent('fourpaws:auth.form', '', [], false, ['HIDE_ICONS' => 'Y']); ?>
        </div>
    </header>
    <main class="b-wrapper" role="main">
