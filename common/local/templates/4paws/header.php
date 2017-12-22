<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CMain $APPLICATION
 */

use Bitrix\Main\Application;use Bitrix\Main\Page\Asset;use FourPaws\App\Application as PawsApplication;use FourPaws\App\MainTemplate;use FourPaws\Decorators\SvgDecorator;

/** @var MainTemplate $template */
$template = MainTemplate::getInstance(Application::getInstance()->getContext());
$markup = PawsApplication::markup();

?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui, user-scalable=no">
    <meta name="skype_toolbar" content="skype_toolbar_parser_compatible">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="google" value="notranslate">
    <meta name="format-detection" content="telephone=no">
    
    <script src="/static/build/js/jquery/jquery.min.js"></script>
    <?php $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->ShowTitle() ?></title>
    <!--[if lte IE 9]>
    <script data-skip-moving="true" src="js/html5shiv/html5shiv.min.js"></script><![endif]-->
    <?php
    Asset::getInstance()->addCss($markup->getCssFile());
    Asset::getInstance()->addJs('https://api-maps.yandex.ru/2.1/?lang=ru_RU');
    ?>
</head>
<body>
<?php $APPLICATION->ShowPanel() ?>
<div class="b-page-wrapper js-this-scroll">
    <header class="b-header js-header">
        <div class="b-container">
            <div class="b-header__info">
                <a class="b-hamburger js-hamburger-menu-main" href="javascript:void(0);" title="">
                    <span class="b-icon">
                        <?= new SvgDecorator('icon-hamburger', 24, 18) ?>
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
                    <?php $APPLICATION->IncludeComponent(
                        'fourpaws:auth.form',
                        '',
                        [],
                        false,
                        ['HIDE_ICONS' => 'Y']
                    );
                    
                    $APPLICATION->IncludeComponent(
                        'bitrix:sale.basket.basket.line',
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
                        ['HIDE_ICONS' => 'Y']
                    ); ?>
                </div>
            </div>
            <div class="b-header__menu js-minimal-menu">
                <?php
                /**
                 * @todo Основное меню. Чать без dropdown Заменить компонентом и удалить файл.
                 */
                require_once 'temp_header_menu.php';
                ?>
                <?php $APPLICATION->IncludeComponent('fourpaws:city.selector') ?>
            </div>
            <?php $APPLICATION->IncludeComponent(
                'fourpaws:city.delivery.info',
                'template.header'
            ); ?>
        </div>
    </header>
    <?php
    /**
     * @todo добавить @see на место установки header_dropdown_menu
     */
    $APPLICATION->ShowViewContent('header_dropdown_menu');
    ?>
    <main class="b-wrapper<?=$template->getIndexMainClass()?>" role="main">
        <?php if ($template->hasHeaderPublicationListContainer()) {
            ?>
            <div class="b-container b-container--news">
                <div class="b-news">
                    <h1 class="b-title b-title--h1"><?php $APPLICATION->ShowTitle(false) ?></h1>
        <?php
        } ?>
        
        <?php if ($template->hasHeaderDetailPageContainer()) {
            ?>
            <div class="b-container b-container--news-detail">
                <div class="b-detail-page">
                    <?php
                    global $APPLICATION;
            $APPLICATION->IncludeComponent(
                        'bitrix:breadcrumb',
                        'breadcrumb',
                        [
                            'PATH'       => '',
                            'SITE_ID'    => SITE_ID,
                            'START_FROM' => '0',
                        ]
                    ); ?>
                    <h1 class="b-title b-title--h1">
                        <?php $APPLICATION->ShowTitle(false) ?>
                    </h1>
                    <?php
                    $APPLICATION->ShowViewContent('header_news_display_date'); ?>
                </div>
            </div>
        <?php
        } ?>
        
        <?php if ($template->hasHeaderPersonalContainer()) {
            ?>
            <div class="b-account">
                <div class="b-container b-container--account">
                    <div class="b-account__wrapper-title">
                        <h1 class="b-title b-title--h1"><?php $APPLICATION->ShowTitle(false) ?></h1>
                    </div>
                    <?php $APPLICATION->IncludeComponent('bitrix:menu',
                         'personal.menu',
                         [
                             'COMPONENT_TEMPLATE'    => 'personal.menu',
                             'ROOT_MENU_TYPE'        => 'personal_cab',
                             'MENU_CACHE_TYPE'       => 'A',
                             'MENU_CACHE_TIME'       => '360000',
                             'MENU_CACHE_USE_GROUPS' => 'N',
                             'MENU_CACHE_GET_VARS'   => [],
                             'MAX_LEVEL'             => '1',
                             'CHILD_MENU_TYPE'       => 'personal_cab',
                             'USE_EXT'               => 'N',
                             'DELAY'                 => 'N',
                             'ALLOW_MULTI_SELECT'    => 'N',
                         ],
                         false); ?>
                     <main class="b-account__content" role="main">
        <?php
        } ?>
        <?php if ($template->hasHeaderBlockShopList()) {?>
                    <div class="b-stores">
                        <div class="b-container">
                            <div class="b-stores__top">
                                <h1 class="b-title b-title--h1 b-title--stores-header"><?php $APPLICATION->ShowTitle(false) ?></h1>
                                <div class="b-stores__info">
                                    <p>Все магазины нашей сети работают без выходных и принимают банковские карты к оплате</p>
                                </div>
                            </div>
        <?php }?>