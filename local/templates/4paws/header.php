<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;use FourPaws\App\MainTemplate;

$template = MainTemplate::getInstance(Application::getInstance()->getContext());

?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui, user-scalable=no">
    <meta name="skype_toolbar" content="skype_toolbar_parser_compatible">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="google" value="notranslate">
    <meta name="format-detection" content="telephone=no">
    <?php /** @todo Markup */ ?>
    <link href="/static/build/css/main.css?#{new Date().getTime()}" rel="stylesheet">
    <script src="/static/build/js/jquery/jquery.min.js"></script>
    <?php $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->ShowTitle() ?></title>
    <!--[if lte IE 9]>
    <script data-skip-moving="true" src="js/html5shiv/html5shiv.min.js"></script><![endif]-->
</head>
<body>
<?php $APPLICATION->ShowPanel() ?>
<div class="b-page-wrapper js-this-scroll">
    <header class="b-header js-header">
        <div class="b-container">
            <div class="b-header__info">
                <a class="b-hamburger js-hamburger-menu-main" href="javascript:void(0);" title="">
                    <span class="b-icon">
                        <svg class="b-icon__svg" viewBox="0 0 24 18 " width="24px" height="18px">
                            <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-hamburger"></use>
                        </svg>
                    </span>
                </a>
                <a class="b-logo" href="/" title="">
                    <img src="/static/build/images/inhtml/logo.svg" alt="Четыре лапы" title="Четыре лапы" />
                </a>
                <?php
                /**
                 * @todo Форма поиска. Заменить компонентом и удалить файл.
                 */
                require_once 'temp_search.php';
                ?>
                <div class="b-header-info">
                    <?php require_once 'blocks/header/phone_block.php' ?>
                    <?php $APPLICATION->IncludeComponent('fourpaws:auth.form',
                                                         '',
                                                         [],
                                                         false,
                                                         ['HIDE_ICONS' => 'Y']);
                    
                    $APPLICATION->IncludeComponent('bitrix:sale.basket.basket.line',
                                                   'header.basket',
                                                   [
                                                       'COMPONENT_TEMPLATE'   => 'header.basket',
                                                       'PATH_TO_BASKET'       => '/personal/cart/',
                                                       'PATH_TO_ORDER'        => '/personal/order/make/',
                                                       'SHOW_NUM_PRODUCTS'    => 'Y',
                                                       'SHOW_TOTAL_PRICE'     => 'Y',
                                                       'SHOW_EMPTY_VALUES'    => 'Y',
                                                       'SHOW_PERSONAL_LINK'   => 'Y',
                                                       'PATH_TO_PERSONAL'     => '/personal/',
                                                       'SHOW_AUTHOR'          => 'N',
                                                       'PATH_TO_REGISTER'     => '',
                                                       'PATH_TO_AUTHORIZE'    => '',
                                                       'PATH_TO_PROFILE'      => '/personal/',
                                                       'SHOW_PRODUCTS'        => 'Y',
                                                       'SHOW_DELAY'           => 'N',
                                                       'SHOW_NOTAVAIL'        => 'Y',
                                                       'SHOW_IMAGE'           => 'Y',
                                                       'SHOW_PRICE'           => 'Y',
                                                       'SHOW_SUMMARY'         => 'N',
                                                       'POSITION_FIXED'       => 'N',
                                                       'HIDE_ON_BASKET_PAGES' => 'N',
                                                   ],
                                                   false,
                                                   ['HIDE_ICONS' => 'Y']); ?>
                </div>
            </div>
            <div class="b-header__menu js-minimal-menu">
                <?php
                /**
                 * @todo Основное меню. Заменить компонентом и удалить файл.
                 *       Разница между temp_header_menu.php и temp_menu.php пока неясна.
                 */
                require_once 'temp_header_menu.php';
                ?>
                <?php
                /**
                 * @todo Выбор региона. Заменить компонентом и удалить файл.
                 */
                require_once 'temp_header_region.php';
                ?>
                <?php
                /**
                 * @todo Стоимость доставки (регионозависимая). Заменить компонентом и удалить файл.
                 */
                require_once 'temp_header_delivery.php';
                ?>
            </div>
        </div>
    </header>
    <?php
    /**
     * @todo Меню. Заменить компонентом и удалить файл.
     *       Разница между temp_header_menu.php и temp_menu.php пока неясна.
     */
    require_once 'temp_menu.php';
    ?>
    <main class="b-wrapper" role="main">
