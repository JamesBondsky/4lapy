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
    <meta name="google-site-verification" content="YhnMv-eup_rK_sqgNqgHc8UrWyWaZQ22m5z7xnokuNs" />

    <meta property="og:title" content=""/>
    <meta property="og:description" content=""/>
    <?php /*<meta property="og:image" content="<?='https://'.$_SERVER['SERVER_NAME'].'/leto2020/img/leto2020-share.png'?>">*/?>

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

<header class="header-leto2020" data-header-landing="true">
    <div class="header-leto2020__container">
        <div class="header-leto2020__content">
            <a href="/" class="header-leto2020__logo">
                <img src="/leto2020/img/logo.svg" alt="leto2020" title="leto2020"/>
            </a>
            <div class="header-leto2020-menu" data-mobile-menu-landing="true">
                <ul class="header-leto2020-menu__list" data-list-mobile-menu-landing="true">
                    <li  class="header-leto2020-menu__item">
                        <a href="/" class="header-leto2020-menu__link" target="_blank">Правила акции</a>
                    </li>

                    <li class="header-leto2020-menu__item">
                        <a href="javascript:void(0);"  class="header-leto2020-menu__link" data-btn-scroll-landing="regulations">Принять участие</a>
                    </li>

                    <li  class="header-leto2020-menu__item">
                        <a href="javascript:void(0);"  class="header-leto2020-menu__link" data-btn-scroll-landing="prizes">Призы</a>
                    </li>

                    <li  class="header-leto2020-menu__item" style="<?$APPLICATION->ShowViewContent('empty-winners');?>">
                        <a href="javascript:void(0);"  class="header-leto2020-menu__link" data-btn-scroll-landing="winners">Победители</a>
                    </li>

                    <li  class="header-leto2020-menu__item">
                        <a href="javascript:void(0);"  class="header-leto2020-menu__link" data-btn-scroll-landing="where-buy">Где купить</a>
                    </li>

                    <li  class="header-leto2020-menu__item">
                        <a href="javascript:void(0);"  class="header-leto2020-menu__link" data-btn-scroll-landing="questions">Вопросы</a>
                    </li>
                </ul>
            </div>
            <div class="header-leto2020__toggle-mobile-menu" data-toggle-mobile-menu-landing="true"><span></span></div>
        </div>
    </div>
</header>

<div class="page-leto2020">
    <div class="top-landing" data-top-landing="true">
    </div>

<div class="b-page-wrapper landing-page-wrapper <?= $template->getWrapperClass() ?> js-this-scroll">

    <?php if ($template->hasMainWrapper()) { ?>
    <main class="b-wrapper<?= $template->getIndexMainClass() ?>" role="main">

<?php }
