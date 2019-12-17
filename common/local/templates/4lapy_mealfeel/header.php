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
    <meta name="yandex-verification" content="d69492b0ac6396cf" />

    <meta property="og:title" content="Выиграйте SPA-weekend, Роза Хутор Сочи"/>
    <meta property="og:description" content="Купите Mealfeel, регистрируйтесь и проверяйте результаты розыгрыша каждую пятницу июля. В розыгрыше 50 призов для правильного питания. Главный приз разыгрывается 1 августа. Удачи!"/>
    <meta property="og:image" content="<?='https://'.$_SERVER['SERVER_NAME'].'/img/mealfeel-share.png'?>">

    <link href="https://fonts.googleapis.com/css?family=PT+Sans+Narrow|Roboto+Condensed&display=swap" rel="stylesheet">

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
    $asset->addJs('//api-maps.yandex.ru/2.1/?apikey=8bb38591-0ddc-44f1-a86c-7e5d50e8cac3&lang=ru_RU');
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
<body class="body-landing body-landing_mealfeel">
<?php $APPLICATION->ShowPanel(); ?>

<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
    (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
    (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

    ym(52251391, "init", {
        id:52251391,
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true
    });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/52251391" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<header class="header-landing  header-landing_mealfeel" data-header-landing="true">
    <div class="container-landing">
        <div class="header-landing__content">
            <div class="header-landing__logo header-landing__logo_mealfeel">
                <img src="/img/mealfeel-logo.svg" alt="Mealfeel" title="Mealfeel"/>
            </div>
            <div class="header-landing-menu" data-mobile-menu-landing="true">
                <ul class="header-landing-menu__list" data-list-mobile-menu-landing="true">
                    <li  class="header-landing-menu__item">
                        <a href="/mealfeel_rules.pdf" class="header-landing-menu__link" target="_blank">Правила акции</a>
                    </li>

                    <li class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="regulations">Принять участие</a>
                    </li>

                    <li  class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="prizes">Призы</a>
                    </li>

                    <li  class="header-landing-menu__item" style="<?$APPLICATION->ShowViewContent('empty-winners');?>">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="winners">Победители</a>
                    </li>

                    <li  class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="where-buy">Где купить</a>
                    </li>

                    <li  class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="contacts">Контакты</a>
                    </li>
                </ul>
            </div>
            <div class="header-landing__toggle-mobile-menu" data-toggle-mobile-menu-landing="true"><span></span></div>
        </div>
    </div>
</header>

<div class="top-landing" data-top-landing="true">
    <section class="splash-screen-mealfeel">
        <div class="splash-screen-mealfeel__bg" style="background-image: url('/img/bg-splash-mealfeel.jpg')"></div>
        <div class="splash-screen-mealfeel__prizes" style="background-image: url('/img/prizes-splash-screen.png')"></div>
        <div class="splash-screen-mealfeel__container">
            <div class="splash-screen-mealfeel__content">
                <div class="splash-screen-mealfeel__title">
                    Выиграй отдых в&nbsp;Сочи для&nbsp;гурманов
                </div>
                <div class="splash-screen-mealfeel__subtitle">
                    <span class="main-prizes-subtitle">Главный приз</span>
                    <span>
                        SPA-weekend<br/>
                        Роза Хутор Сочи
                    </span>
                </div>
            </div>
        </div>
        <div class="splash-screen-mealfeel__separator"></div>
        <div class="splash-screen-mealfeel__primary">
            <div class="splash-screen-mealfeel__container">
                <span>Новые призы каждую неделю!</span>
            </div>
        </div>
    </section>

    <section id="info-prizes" data-id-section-landing="info-prizes" class="info-prizes info-prizes_mealfeel">
        <div class="container-landing">
            <div class="info-prizes__message">
                Регистрируйте чеки и&nbsp;выигрывайте!<br />
                Новые подарки каждую неделю!<br />
                50 победителей + 1 главный приз!
            </div>
        </div>
    </section>

    <section data-id-section-landing="regulations" class="regulations-mealfeel">
        <div class="container-landing">
            <div class="landing-title">Как принять участие в&nbsp;акции</div>
            <div class="regulations-mealfeel__list">
                <div class="regulations-mealfeel__step-wrap">
                    <div class="regulations-mealfeel__step">
                        <img class="regulations-mealfeel__number" src="/img/step1-regulations.png" alt="1">
                        <div class="regulations-mealfeel__descr">
                            Купите корм Mealfeel (сухой вместе с&nbsp;влажным) на&nbsp;сумму 1500 р. и&nbsp;получите гарантированный приз&nbsp;&mdash; бак для хранения корма.
                        </div>
                    </div>
                </div>
                <div class="regulations-mealfeel__step-wrap">
                    <div class="regulations-mealfeel__step">
                        <img class="regulations-mealfeel__number" src="/img/step2-regulations.png" alt="2">
                        <div class="regulations-mealfeel__descr">
                            Зарегистрируйте<br/> чек и&nbsp;участвуйте в&nbsp;розыгрыше<br/> призов
                        </div>
                    </div>
                </div>
                <div class="regulations-mealfeel__step-wrap">
                    <div class="regulations-mealfeel__step">
                        <img class="regulations-mealfeel__number" src="/img/step3-regulations.png" alt="3">
                        <div class="regulations-mealfeel__descr">
                            Проверяйте результаты розыгрыша каждую пятницу.<br />
                            Розыгрыш главного приза состоится <span class="red"><b>1&nbsp;августа</b></span>.
                        </div>
                    </div>
                </div>
            </div>
            <?if ($USER->IsAuthorized()) {?>
                <div class="regulations-landing__btn">
                    <div class="landing-btn landing-btn_mealfeel" data-btn-scroll-landing="registr-check">Зарегистрировать чек</div>
                </div>
            <?} else {?>
                <div class="regulations-landing__btn">
                    <div class="landing-btn landing-btn_mealfeel js-open-popup" data-popup-id="authorization">Принять участие</div>
                </div>
            <?}?>
        </div>
    </section>
</div>

<div class="b-page-wrapper landing-page-wrapper <?= $template->getWrapperClass() ?> js-this-scroll">

    <?php if ($template->hasMainWrapper()) { ?>
    <main class="b-wrapper<?= $template->getIndexMainClass() ?>" role="main">

<?php }
