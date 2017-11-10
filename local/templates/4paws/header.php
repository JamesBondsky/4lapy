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
                        <div class="b-form-inline b-form-inline--search">
                            <form class="b-form-inline__form">
                                <input class="b-input"
                                       type="text"
                                       id="header-search"
                                       placeholder="Найти лучшее для вашего питомца…" />
                                <button class="b-button b-button--form-inline b-button--search">
                                    <span class="b-icon">
                                        <svg class="b-icon__svg"
                                             viewBox="0 0 16 16 "
                                             width="16px"
                                             height="16px">
                                            <use class="b-icon__use"
                                                 xlink:href="/static/build/icons.svg#icon-search"></use>
                                        </svg>
                                    </span>
                                </button>
                            </form>
                            <a class="b-form-inline__mobile-search" href="javascript:void(0)" title="">
                                <span class="b-icon">
                                    <svg class="b-icon__svg"
                                         viewBox="0 0 20 20 "
                                         width="20px"
                                         height="20px">
                                        <use class="b-icon__use"
                                             xlink:href="/static/build/icons.svg#icon-search-header"></use>
                                    </svg>
                                </span>
                            </a>
                        </div>
                        <div class="b-header-info">
                            <div class="b-header-info__item b-header-info__item--phone">
                                <a class="b-header-info__link js-open-popover"
                                   href="javascript:void(0);"
                                   title="+7 473 202-76-26">
                                    <span class="b-icon">
                                        <svg class="b-icon__svg"
                                             viewBox="0 0 16 16 "
                                             width="16px"
                                             height="16px">
                                            <use class="b-icon__use"
                                                 xlink:href="/static/build/icons.svg#icon-phone-dark"></use>
                                        </svg>
                                    </span>
                                    <span class="b-header-info__inner">+7 473 202-76-26</span>
                                    <span class="b-icon b-icon--header b-icon--left-3">
                                        <svg class="b-icon__svg"
                                             viewBox="0 0 10 12 "
                                             width="10px"
                                             height="12px">
                                            <use class="b-icon__use"
                                                 xlink:href="/static/build/icons.svg#icon-arrow-down"></use>
                                        </svg>
                                    </span>
                                </a>
                                <div class="b-popover b-popover--phone js-popover">
                                    <div class="b-contact">
                                        <dl class="b-phone-pair">
                                            <dt class="b-phone-pair__phone">
                                                <a class="b-phone-pair__link"
                                                   href="tel:84732027626"
                                                   title="+7 473 202-76-26">+7 473 202-76-26</a>
                                            </dt>
                                            <dd class="b-phone-pair__description">
                                                Для Нижнего Новгорода. Доступен до 21:00
                                            </dd>
                                        </dl>
                                        <dl class="b-phone-pair">
                                            <dt class="b-phone-pair__phone">
                                                <a class="b-phone-pair__link"
                                                   href="tel:88007700022"
                                                   title="+7 800 770-00-22">+7 800 770-00-22</a>
                                            </dt>
                                            <dd class="b-phone-pair__description">Бесплатно по России. Круглосуточно
                                            </dd>
                                        </dl>
                                        <ul class="b-link-block b-link-block--border">
                                            <li class="b-link-block__item">
                                                <a class="b-link-block__link"
                                                   href="javascript:void(0);"
                                                   title="Перезвоните мне">
                                                    <span class="b-icon">
                                                        <svg class="b-icon__svg"
                                                             viewBox="0 0 16 16 "
                                                             width="16px"
                                                             height="16px">
                                                            <use class="b-icon__use"
                                                                 xlink:href="/static/build/icons.svg#icon-phone-header"></use>
                                                        </svg>
                                                    </span>
                                                    Перезвоните мне
                                                </a>
                                            </li>
                                            <li class="b-link-block__item">
                                                <a class="b-link-block__link"
                                                   href="javascript:void(0);"
                                                   title="Обратная связь">
                                                    <span class="b-icon">
                                                        <svg class="b-icon__svg"
                                                             viewBox="0 0 16 16 "
                                                             width="16px"
                                                             height="16px">
                                                            <use class="b-icon__use"
                                                                 xlink:href="/static/build/icons.svg#icon-email-header"></use>
                                                        </svg>
                                                    </span>
                                                    Обратная связь
                                                </a>
                                            </li>
                                            <li class="b-link-block__item">
                                                <a class="b-link-block__link" href="javascript:void(0);"
                                                   title="Чат с консультантом">
                                                    <span class="b-icon">
                                                        <svg class="b-icon__svg"
                                                             viewBox="0 0 16 16 "
                                                             width="16px"
                                                             height="16px">
                                                            <use class="b-icon__use"
                                                                 xlink:href="/static/build/icons.svg#icon-chat-header"></use>
                                                        </svg>
                                                    </span>
                                                    Чат с консультантом
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php $APPLICATION->IncludeComponent('fourpaws:auth.form',
                                                                 '',
                                                                 [],
                                                                 false,
                                                                 ['HIDE_ICONS' => 'Y']); ?>
                            <div class="b-header-info__item b-header-info__item--cart">
                                <a class="b-header-info__link"
                                   href="javascript:void(0);"
                                   title="Корзина">
                                    <span class="b-icon">
                                        <svg class="b-icon__svg"
                                             viewBox="0 0 16 16 "
                                             width="16px"
                                             height="16px">
                                            <use class="b-icon__use"
                                                 xlink:href="/static/build/icons.svg#icon-cart"></use>
                                        </svg>
                                    </span>
                                    <span class="b-header-info__inner">Корзина</span>
                                    <span class="b-header-info__number">1</span></a>
                                <div class="b-popover b-popover--cart">
                                    <div class="b-cart">
                                        <span class="b-cart__amount">1 товар</span>
                                        <a class="b-link"
                                           href="javascript:void(0);"
                                           title="Редактировать">Редактировать</a>
                                        <a class="b-button"
                                           href="javascript:void(0);"
                                           title="Оформить заказ">Оформить заказ</a>
                                        <div class="b-cart-item">
                                            <a class="b-cart-item__name"
                                               href="javascript:void(0);"
                                               title="Роял Канин корм для собак крупных пород ма…">
                                                Роял Канин корм для собак крупных пород ма…</a>
                                            <span class="b-cart-item__weight">15 кг</span>
                                            <span class="b-cart-item__amount">(1 шт.)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-header__menu js-minimal-menu">
                        <nav class="b-menu">
                            <ul class="b-menu__list">
                                <li class="b-menu__item b-menu__item--more">
                                    <a class="b-menu__link b-menu__link--more js-open-main-menu"
                                       href="javascript:void(0);"
                                       title="">
                                        Товары по&nbsp;питомцу
                                        <span class="b-icon b-icon--more b-icon--left-5">
                                            <svg class="b-icon__svg"
                                                 viewBox="0 0 10 10 "
                                                 width="10px"
                                                 height="10px">
                                                <use class="b-icon__use"
                                                     xlink:href="/static/build/icons.svg#icon-arrow-down"></use>
                                            </svg>
                                        </span>
                                    </a>
                                </li>
                                <li class="b-menu__item b-menu__item--more">
                                    <a class="b-menu__link b-menu__link--more" href="javascript:void(0);" title="">
                                        По бренду
                                        <span class="b-icon b-icon--more b-icon--left-5">
                                            <svg class="b-icon__svg"
                                                 viewBox="0 0 10 10 "
                                                 width="10px"
                                                 height="10px">
                                                <use class="b-icon__use"
                                                     xlink:href="/static/build/icons.svg#icon-arrow-down"></use>
                                            </svg>
                                        </span>
                                    </a>
                                </li>
                                <li class="b-menu__item">
                                    <a class="b-menu__link" href="javascript:void(0);" title="">Ветаптека</a>
                                </li>
                                <li class="b-menu__item">
                                    <a class="b-menu__link" href="javascript:void(0);" title="">Магазины</a>
                                </li>
                                <li class="b-menu__item">
                                    <a class="b-menu__link" href="javascript:void(0);" title="">Акции</a>
                                </li>
                                <li class="b-menu__item">
                                    <a class="b-menu__link" href="javascript:void(0);" title="">Сервисы</a>
                                </li>
                            </ul>
                        </nav>
                        <div class="b-header__wrapper-for-popover">
                            <a class="b-combobox b-combobox--header js-open-popover"
                               href="javascript:void(0);"
                               title="Нижний Новгород">
                                <span class="b-icon b-icon--location">
                                    <svg class="b-icon__svg"
                                         viewBox="0 0 14 16 "
                                         width="14px"
                                         height="16px">
                                        <use class="b-icon__use"
                                             xlink:href="/static/build/icons.svg#icon-delivery-header"></use>
                                    </svg>
                                </span>
                                Нижний Новгород
                                <span class="b-icon b-icon--delivery-arrow">
                                    <svg class="b-icon__svg" viewBox="0 0 10 13 "
                                         width="10px"
                                         height="13px">
                                        <use class="b-icon__use"
                                             xlink:href="/static/build/icons.svg#icon-arrow-down"></use>
                                    </svg>
                                </span>
                            </a>
                            <div class="b-popover b-popover--blue-arrow js-popover">
                                <p class="b-popover__text">Ваш город – Нижний Новгород?</p>
                                <a class="b-popover__link" href="javascript:void(0)" title="">Да</a>
                                <a class="b-popover__link b-popover__link--last" href="javascript:void(0)" title="">
                                    Нет, выбрать другой
                                </a>
                            </div>
                        </div>
                        <div class="b-header__wrapper-for-popover">
                            <a class="b-combobox b-combobox--delivery b-combobox--header js-open-popover"
                               href="javascript:void(0);"
                               title="Нижний Новгород">
                                <span class="b-icon b-icon--delivery-header">
                                    <svg class="b-icon__svg" viewBox="0 0 20 16 " width="20px" height="16px">
                                        <use class="b-icon__use"
                                             xlink:href="/static/build/icons.svg#icon-delivery"></use>
                                    </svg>
                                </span>
                                Бесплатная доставка
                                <span class="b-icon b-icon--delivery-arrow">
                                    <svg class="b-icon__svg" viewBox="0 0 10 13 " width="10px" height="13px">
                                        <use class="b-icon__use"
                                             xlink:href="/static/build/icons.svg#icon-arrow-down"></use>
                                    </svg>
                                </span>
                            </a>
                            <div class="b-popover b-popover--blue-arrow js-popover">
                                <p class="b-popover__text">200 ₽ при заказе от 500 ₽</p>
                                <p class="b-popover__text b-popover__text--last">Бесплатно при заказе от 2000 ₽</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        </div>
    </header>
    <?php
    /**
     * @todo Меню. Заменить компонентом и удалить файл.
     */
    require 'menu.php';
    ?>
    <main class="b-wrapper" role="main">
