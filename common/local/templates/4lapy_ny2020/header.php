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

    <meta property="og:title" content="Выиграйте главный приз: Путешествие на 4-х человек на родину Деда Мороза!"/>
    <meta property="og:description" content="Зарегистрируйтесь и выигрывайте призы каждую неделю!"/>
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
<body class="body-landing">
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

<header class="header-ny2020" data-header-landing="true">
    <div class="header-ny2020__container">
        <div class="header-ny2020__content">
            <a href="/" class="header-ny2020__logo">
                <img src="/ny2020/img/logo.svg" alt="NY2020" title="NY2020"/>
            </a>
            <div class="header-ny2020-menu" data-mobile-menu-landing="true">
                <ul class="header-ny2020-menu__list" data-list-mobile-menu-landing="true">
                    <?/*<li  class="header-ny2020-menu__item">
                        <a href="/" class="header-ny2020-menu__link" target="_blank">Правила акции</a>
                    </li>*/?>

                    <li class="header-ny2020-menu__item">
                        <a href="javascript:void(0);"  class="header-ny2020-menu__link" data-btn-scroll-landing="regulations">Принять участие</a>
                    </li>

                    <li  class="header-ny2020-menu__item">
                        <a href="javascript:void(0);"  class="header-ny2020-menu__link" data-btn-scroll-landing="prizes">Призы</a>
                    </li>

                    <li  class="header-ny2020-menu__item" style="<?$APPLICATION->ShowViewContent('empty-winners');?>">
                        <a href="javascript:void(0);"  class="header-ny2020-menu__link" data-btn-scroll-landing="winners">Победители</a>
                    </li>

                    <li  class="header-ny2020-menu__item">
                        <a href="javascript:void(0);"  class="header-ny2020-menu__link" data-btn-scroll-landing="where-buy">Где купить</a>
                    </li>

                    <li  class="header-ny2020-menu__item">
                        <a href="javascript:void(0);"  class="header-ny2020-menu__link" data-btn-scroll-landing="questions">Вопросы</a>
                    </li>
                </ul>
            </div>
            <div class="header-ny2020__toggle-mobile-menu" data-toggle-mobile-menu-landing="true"><span></span></div>
        </div>
    </div>
</header>

<div class="page-ny2020">
<div class="top-landing" data-top-landing="true">
    <section class="main-banner-ny2020">
        <div class="main-banner-ny2020__container">
            <div class="main-banner-ny2020__main">
                <div class="main-banner-ny2020__title">Выигрывай каждую неделю</div>
                <div class="main-banner-ny2020__subtitle">4 iPhone 11 Pro</div>
                <div class="main-banner-ny2020__prizes">
                    <div class="prizes-info">
                        <div class="prizes-info__title">
                            Розыгрыши каждую неделю
                        </div>
                        <ul class="prizes-info__list">
                            <li>iPhone 11 Pro</li>
                            <li>50 термокружек</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="main-banner-ny2020__info">
                <div class="info-title">Зарегистрируйтесь и&nbsp;выигрывайте призы каждую неделю!</div>
                <div class="info-subtitle">204&nbsp;победителя <nobr>+ 1</nobr> главный приз!</div>
            </div>
        </div>
    </section>

    <section data-id-section-landing="prizes" class="prizes-ny2020">
        <div class="container-landing">
            <div class="title-ny2020">Призы</div>
            <div class="prizes-ny2020__list">
                <div class="item">
                    <div class="item-card">
                        <div class="item-card__img-wrap">
                            <div class="item-card__img" style="background-image: url('/ny2020/img/prizes1.png')"></div>
                        </div>
                        <div class="item-card__title">Термокружка</div>
                        <div class="item-card__descr">
                            Всего 200 призов<br />
                            Разыгрываются по&nbsp;50шт<br />
                            каждый понедельник<br />
                            <b>9, 16, 23 и&nbsp;30 декабря</b>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="item-card">
                        <div class="item-card__img-wrap">
                            <div class="item-card__img" style="background-image: url('/ny2020/img/prizes2.png')"></div>
                        </div>
                        <div class="item-card__title">Iphone 11 PRO</div>
                        <div class="item-card__descr">
                            Всего 4 приза<br />
                            Разыгрывается по&nbsp;1шт<br/>
                            каждый понедельник<br />
                            <b>9, 16, 23 и&nbsp;30 декабря</b>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="item-card">
                        <div class="item-card__img-wrap">
                            <div class="item-card__img" style="background-image: url('/ny2020/img/prizes3.png')"></div>
                        </div>
                        <div class="item-card__title">Путешествие</div>
                        <div class="item-card__descr">
                            В&nbsp;Великий Устюг на&nbsp;родину <nobr>Деда Мороза</nobr><br />
                            1 приз на&nbsp;семью из&nbsp;4х человек<br />
                            <b>Розыгрыш 30 декабря</b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<div class="b-page-wrapper landing-page-wrapper <?= $template->getWrapperClass() ?> js-this-scroll">

    <?php if ($template->hasMainWrapper()) { ?>
    <main class="b-wrapper<?= $template->getIndexMainClass() ?>" role="main">

<?php }
