<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CMain $APPLICATION
 */

use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use FourPaws\App\Application as PawsApplication;
use FourPaws\App\MainTemplate;
use FourPaws\UserBundle\Enum\UserLocationEnum;

/** @var MainTemplate $template */
$template = MainTemplate::getInstance(Application::getInstance()
    ->getContext());
$markup = PawsApplication::markup(); 

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <base href="<?= PawsApplication::getInstance()
        ->getSiteDomain() ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui, user-scalable=no" \>
    <meta name="skype_toolbar" content="skype_toolbar_parser_compatible">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="google" content="notranslate">
    <meta name="format-detection" content="telephone=no">
    <meta name="yandex-verification" content="6266e34669b85ed6">

    <meta property="og:title" content="Главный приз: путешествие на родину породы"/>
    <meta property="og:description" content="Купите рационы породной линейки ROYAL CANIN® на сумму от 1000 рублей и получите 150 баллов на карту."/>
    <meta property="og:image" content="<?='https://'.$_SERVER['SERVER_NAME'].'/static/build/images/content/landing-canin-share.png'?>"/>

    <?php /** @todo Mobe onto right place  */ ?>
    <script src="/static/build/js/jquery/jquery.min.js"></script>
    <script data-skip-moving="true">
        window.js_static = '/static/build/';
        window._global = {};
        window._global.locationCookieCode = '<?= UserLocationEnum::DEFAULT_LOCATION_COOKIE_CODE ?>';
        window.dataLayer = window.dataLayer || [];
    </script>
    <?php $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->ShowTitle() ?></title>
    <?php
    $asset = Asset::getInstance();
    $asset->addCss($markup->getCssFile());
    $asset->addJs('//api-maps.yandex.ru/2.1/?apikey=ad666cd3-80be-4111-af2d-209dddf2c55e&lang=ru_RU');
    $asset->addJs('https://www.google.com/recaptcha/api.js?hl=ru');
    ?>

    <?/** уходи */?>
    <script>
        $(function() {
            setTimeout(function() {

                if(false
                    || window.location.pathname == '/personal/register/'
                    || window.location.pathname == '/personal/forgot-password/'
                ) {
                    $([document.documentElement, document.body]).animate({
                        scrollTop: $('.landing-page-wrapper').offset().top - 100
                    }, 2000);
                }

            }, 500);
        });
    </script>


</head>
<body class="body-landing body-landing_canin">
<?php $APPLICATION->ShowPanel(); ?>

<header class="header-landing header-landing_canin" data-header-landing="true">
    <div class="container-landing">
        <div class="header-landing__content">
            <div class="header-landing__toggle-mobile-menu" data-toggle-mobile-menu-landing="true"><span></span></div>

            <div class="header-landing__logo header-landing__logo_lapy hidden visible-sm">
                <img src="/static/build/images/inhtml/logo_big.svg" alt="Четыре лапы" title="Четыре лапы"/>
            </div>
            <div class="header-landing__logo header-landing__logo_canin">
                <img src="/static/build/images/inhtml/royal_canin_logo.svg" alt="ROYAL CANIN" title="ROYAL CANIN"/>
            </div>
            <div class="header-landing-menu" data-mobile-menu-landing="true">
                <ul class="header-landing-menu__list" data-list-mobile-menu-landing="true">
                    <li  class="header-landing-menu__item">
                        <a href="#" class="header-landing-menu__link" target="_blank">Правила акции</a>
                    </li>

                    <li class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="regulations">Принять участие</a>
                    </li>

                    <li  class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="where-buy">Где купить</a>
                    </li>

                    <li  class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="winners">Победители</a>
                    </li>


                    <li  class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="contacts">Контакты</a>
                    </li>
                </ul>
            </div>
            <div class="header-landing__logo header-landing__logo_lapy hidden-sm">
                <img src="/static/build/images/inhtml/logo_big.svg" alt="Четыре лапы" title="Четыре лапы"/>
            </div>
        </div>
    </div>
</header>

<div class="top-landing" data-top-landing="true">
    <section class="splash-screen-canin">
        <div class="splash-screen-canin__animals" style="background-image: url('/static/build/images/content/canin-splash-screen_animals.png')"></div>
        <div class="splash-screen-canin__globe" style="background-image: url('/static/build/images/content/canin-splash-screen_globe.png')"></div>

        <div class="container-landing">
            <div class="splash-screen-canin__content">
                <div class="splash-screen-canin__subtitle">
                    <span>Главный приз:</span>
                </div>
                <div class="splash-screen-canin__title">
                    Путешествие на&nbsp;родину породы
                </div>
                <div class="splash-screen-canin__date">С 8 апреля по 19 мая 2019 года</div>

                <div class="splash-screen-canin__btn-wrap">
                    <div class="btn-canin" data-btn-scroll-landing="regulations">Узнать подробности</div>
                </div>

            </div>
        </div>
    </section>

    <section data-id-section-landing="regulations" class="regulations-canin">
        <div class="container-landing">
            <div class="landing-title landing-title_gray">Как принять участие в&nbsp;акции</div>
            <ol class="regulations-canin__list">
                <li>
                    <span class="icon">
                        <img src="/static/build/images/content/canin_icon_diet.png" alt="">
                    </span>
                    <span class="red">Купите</span> рационы породной линейки ROYAL&nbsp;CANIN<sup><small>&reg;</small></sup> на&nbsp;сумму от&nbsp;1000&nbsp;рублей и&nbsp;получите 150 баллов на&nbsp;карту
                </li>
                <li>
                    <span class="icon">
                        <img src="/static/build/images/content/canin_icon_monitor.png" alt="">
                    </span>
                    <span class="red">Зарегистрируйте</span> чек на&nbsp;сайте
                </li>
                <li>
                    <span class="icon">
                        <img src="/static/build/images/content/canin_icon_prize.png" alt="">
                    </span>
                    <span class="red">Участвуйте</span> в&nbsp;розыгрыше ценных призов и&nbsp;Путешествия на&nbsp;родину породы
                </li>
            </ol>

            <?if ($USER->IsAuthorized()) {?>
                <div class="regulations-canin__btn">
                    <div class="btn-canin" data-btn-scroll-landing="registr-check">Загрузите чек</div>
                </div>
            <?} else {?>
                <div class="regulations-canin__btn">
                    <div class="btn-canin js-open-popup" data-popup-id="authorization">Принять участие</div>
                </div>
            <?}?>
        </div>
    </section>
</div>

<div class="b-page-wrapper landing-page-wrapper <?= $template->getWrapperClass() ?> js-this-scroll">

    <?php if ($template->hasMainWrapper()) { ?>
    <main class="b-wrapper<?= $template->getIndexMainClass() ?>" role="main">

<?php }
