<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CMain $APPLICATION
 */

use Bitrix\Main\Page\Asset;

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ru">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link type="image/x-icon" href="<?=SITE_DIR.'favicon.ico'?>" rel="icon">
        <!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js" type="text/javascript"></script><![endif]-->
        <title><?php
            $APPLICATION->ShowTitle();
        ?></title><?php
        $APPLICATION->ShowHead();

        Asset::getInstance()->addCss('/local/templates/4paws_front_office/css/style.css');
        Asset::getInstance()->addJs('/local/templates/4paws_front_office/js/jquery.js');
        // не используется больше
        //Asset::getInstance()->addJs('/local/templates/4paws_front_office/js/script.js');
    ?></head>
    <body><?php

        echo '<div id="panel" class="noprint">';
        $APPLICATION->ShowPanel();
        echo '</div>';

        ?><div id="page">
            <header id="header">
                <div class="container">
                    <a id="logo" href="/"></a>
                    <div class="phone-header">+7(495)221-72-26</div>
                </div>
            </header>
            <div id="main">
                <div class="container">
                    <div class="inner-large"><?php
                        // навигация по разделу
                        $APPLICATION->IncludeComponent(
                            'bitrix:menu',
                            'fo.17.0.top',
                            array(
                                'ALLOW_MULTI_SELECT' => 'N',
                                'DELAY' => 'N',
                                'MAX_LEVEL' => '1',
                                'MENU_CACHE_GET_VARS' => [],
                                'MENU_CACHE_TIME' => '3600',
                                'MENU_CACHE_TYPE' => 'A',
                                'MENU_CACHE_USE_GROUPS' => 'Y',
                                'ROOT_MENU_TYPE' => 'front_office_top',
                                'USE_EXT' => 'N',
                                'CHILD_MENU_TYPE' => '',
                            ),
                            null,
                            [
                                'HIDE_ICONS' => 'Y'
                            ]
                        );
