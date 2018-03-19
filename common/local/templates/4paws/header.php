<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CMain $APPLICATION
 */

use Bitrix\Main\Application;use Bitrix\Main\Page\Asset;use FourPaws\App\Application as PawsApplication;use FourPaws\App\MainTemplate;use FourPaws\Decorators\SvgDecorator;use FourPaws\Enum\IblockCode;use FourPaws\Enum\IblockType;use FourPaws\SaleBundle\Service\BasketViewService;

/** @var MainTemplate $template */
$template = MainTemplate::getInstance(Application::getInstance()->getContext());
$markup = PawsApplication::markup();

?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui, user-scalable=no">
    <meta name="skype_toolbar" content="skype_toolbar_parser_compatible">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="google" content="notranslate">
    <meta name="format-detection" content="telephone=no">

    <script src="/static/build/js/jquery/jquery.min.js"></script>
    <script data-skip-moving="true">
        window.js_static = '/static/build/';
        window._global   = {};
    </script>
    <?php $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->ShowTitle() ?></title>
    <?php
    Asset::getInstance()->addCss($markup->getCssFile());
    Asset::getInstance()->addJs('https://api-maps.yandex.ru/2.1.56/?lang=ru_RU');
    Asset::getInstance()->addJs('https://www.google.com/recaptcha/api.js');
    ?>
</head>
<body>
<?php $APPLICATION->ShowPanel() ?>
<div class="b-page-wrapper <?= $template->getWrapperClass() ?> js-this-scroll">
    <header class="b-header <?= $template->getHeaderClass() ?> js-header">
        <div class="b-container">
            <?php if ($template->hasShortHeaderFooter()) { ?>
                <div class="b-header__info b-header__info--short-header">
                    <a class="b-logo"
                       href="/"
                       title="">
                        <img src="/static/build/images/inhtml/logo.svg"
                             alt="Четыре лапы"
                             title="Четыре лапы" />
                    </a>
                    <span class="b-header__phone-short-header">
                        <?php $APPLICATION->IncludeComponent('fourpaws:city.phone',
                                                             'template.header.short',
                                                             [],
                                                             false,
                                                             ['HIDE_ICONS' => 'Y']) ?>
                    </span>
                    <div class="b-header-info b-header-info--short-header js-hide-open-menu">
                        <?php require_once __DIR__ . '/blocks/header/phone_block.php' ?>
                    </div>
                </div>
            <?php } else { ?>
                <div class="b-header__info">
                    <a class="b-hamburger b-hamburger--mobile-menu js-hamburger-menu-mobile"
                       href="javascript:void(0);"
                       title="">
                        <span class="b-hamburger__hamburger-icon"></span>
                    </a>
                    <a class="b-hamburger js-hamburger-menu-main" href="javascript:void(0);" title="">
                    <span class="b-icon b-icon--hamburger">
                        <?= new SvgDecorator('icon-hamburger', 24, 18) ?>
                    </span>
                    </a>
                    <a class="b-logo" href="/" title="">
                        <img src="/static/build/images/inhtml/logo.svg" alt="Четыре лапы" title="Четыре лапы" />
                    </a>
                    <?php
                    $APPLICATION->IncludeComponent('fourpaws:catalog.search.form',
                                                   '',
                                                   [],
                                                   false,
                                                   ['HIDE_ICONS' => 'Y']);
                    ?>
                    <div class="b-header-info">
                        <?php require_once __DIR__ . '/blocks/header/phone_block.php' ?>
                        <?php $APPLICATION->IncludeComponent('fourpaws:auth.form',
                                                             '',
                                                             [],
                                                             false,
                                                             ['HIDE_ICONS' => 'Y']);
                        
                        echo PawsApplication::getInstance()
                                            ->getContainer()
                                            ->get(BasketViewService::class)
                                            ->getMiniBasketHtml(); ?>
                    </div>
                </div>
                <div class="b-header__menu js-minimal-menu js-nav-first-desktop">
                    <?php
                    /**
                     * Основное меню.
                     * dropdown передается через header_dropdown_menu
                     */
                    $APPLICATION->IncludeComponent(
                        'fourpaws:iblock.main.menu',
                        'fp.17.0.top',
                        [
                            'MENU_IBLOCK_TYPE'          => IblockType::MENU,
                            'MENU_IBLOCK_CODE'          => IblockCode::MAIN_MENU,
                            'PRODUCTS_IBLOCK_TYPE'      => IblockType::CATALOG,
                            'PRODUCTS_IBLOCK_CODE'      => IblockCode::PRODUCTS,
                            'CACHE_TIME'                => 3600,
                            'CACHE_TYPE'                => 'A',
                            'MAX_DEPTH_LEVEL'           => '4',
                            // N - шаблон кэшируется
                            'CACHE_SELECTED_ITEMS'      => 'N',
                            'TEMPLATE_NO_CACHE'         => 'N',
                            // количество популярных брендов в пункте меню "Товары по питомцу"
                            'BRANDS_POPULAR_LIMIT'      => '6',
                            // количество популярных брендов в пункте меню "По бренду"
                            'BRANDS_MENU_POPULAR_LIMIT' => '8',
                        ],
                        null,
                        [
                            'HIDE_ICONS' => 'Y'
                        ]
                    );
                    ?>
                    <?php $APPLICATION->IncludeComponent('fourpaws:city.selector',
                                                         '',
                                                         [],
                                                         false,
                                                         ['HIDE_ICONS' => 'Y']) ?>
                    <?php $APPLICATION->IncludeComponent('fourpaws:city.delivery.info',
                                                         'template.header',
                                                         [],
                                                         false,
                                                         ['HIDE_ICONS' => 'Y']); ?>
                </div>
            <?php } ?>
        </div>
    </header>
    <?php
    /**
     * Основное меню. dropdown
     */
    $APPLICATION->ShowViewContent('header_dropdown_menu');

    if ($template->hasMainWrapper()) { ?>
    <main class="b-wrapper<?= $template->getIndexMainClass() ?>" role="main">
        <?php if ($template->hasHeaderPublicationListContainer()) { ?>
        <div class="<?php $APPLICATION->ShowProperty('PUBLICATION_LIST_CONTAINER_1',
                                                     'b-container b-container--news') ?>">
            <div class="<?php $APPLICATION->ShowProperty('PUBLICATION_LIST_CONTAINER_2', 'b-news') ?>">
                <h1 class="b-title b-title--h1"><?php $APPLICATION->ShowTitle(false) ?></h1>
                <?php
                }

                if ($template->hasHeaderDetailPageContainer()) {
                    ?>
                    <div class="<?php $APPLICATION->ShowProperty('PUBLICATION_DETAIL_CONTAINER_1',
                                                                 'b-container b-container--news-detail') ?>">
                        <div class="<?php $APPLICATION->ShowProperty('PUBLICATION_DETAIL_CONTAINER_2',
                                                                     'b-detail-page') ?>">
                            <?php
                            $APPLICATION->IncludeComponent('bitrix:breadcrumb',
                                                           'breadcrumb',
                                                           [
                                                               'PATH'       => '',
                                                               'SITE_ID'    => SITE_ID,
                                                               'START_FROM' => '0',
                                                           ]); ?>
                            <h1 class="b-title b-title--h1">
                                <?php $APPLICATION->ShowTitle(false) ?>
                            </h1>
                            <?php
                            $APPLICATION->ShowViewContent('header_news_display_date'); ?>
                        </div>
                    </div>
                <?php }

                if ($template->hasHeaderPersonalContainer()) { ?>
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
                                                                 'MENU_CACHE_USE_GROUPS' => 'Y',
                                                                 'CACHE_SELECTED_ITEMS'  => 'N',
                                                                 'TEMPLATE_NO_CACHE'     => 'N',
                                                                 'MENU_CACHE_GET_VARS'   => [],
                                                                 'MAX_LEVEL'             => '1',
                                                                 'CHILD_MENU_TYPE'       => 'personal_cab',
                                                                 'USE_EXT'               => 'N',
                                                                 'DELAY'                 => 'N',
                                                                 'ALLOW_MULTI_SELECT'    => 'N',
                                                             ],
                                                             false); ?>
                        <main class="b-account__content" role="main">
                            <?php }

                            if ($template->hasHeaderBlockShopList()) { ?>
                            <div class="b-stores">
                                <div class="b-container">
                                    <div class="b-stores__top">
                                        <h1 class="b-title b-title--h1 b-title--stores-header"><?php $APPLICATION->ShowTitle(false) ?></h1>
                                        <div class="b-stores__info">
                                            <p><?= tplvar('shops_subtitle', true) ?></p>
                                        </div>
                                    </div>
<?php }
}

if ($template->hasContent()) {
    Asset::getInstance()->addCss('/include/static/style.css');
    Asset::getInstance()->addJs('/include/static/scripts.js');

    $APPLICATION->IncludeComponent('bitrix:main.include',
                                   '',
                                   [
                                       'AREA_FILE_SHOW' => 'file',
                                       'PATH'           => sprintf('/include/%s.php', trim($template->getPath(), '/')),
                                   ],
                                   false);
}
