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

    <meta property="og:title" content="Как выиграть запас корма Grandin на год вперед?"/>
    <meta property="og:description" content="Для участия в акции купите любой корм Grandin на сумму от 1800 рублей и зарегистрируйте покупку  на сайте акции grandin.4lapy.ru."/>
    <meta property="og:image" content="<?='https://'.$_SERVER['SERVER_NAME'].'/static/build/images/content/landing-grandin-share.png'?>"/>

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

<header class="header-landing" data-header-landing="true">
    <div class="container-landing">
        <div class="header-landing__content">
            <div class="header-landing__logo">
                <img src="/static/build/images/content/grandin-logo.svg" alt="Grandin" title="Grandin"/>
            </div>
            <div class="header-landing-menu" data-mobile-menu-landing="true">
                <ul class="header-landing-menu__list" data-list-mobile-menu-landing="true">
                    <li  class="header-landing-menu__item">
                        <a href="/grandin_rules.pdf" class="header-landing-menu__link" target="_blank">Правила акции</a>
                    </li>

                    <li class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="regulations">Принять участие</a>
                    </li>

                    <li  class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="prizes">Призы</a>
                    </li>

                    <?/**
                    <li  class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="winners">Победители</a>
                    </li>
                    */?>

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
    <section class="splash-screen-landing">
        <div class="splash-screen-landing__bg" style="background-image: url('/static/build/images/content/bg-splash-landing.png')"></div>
        <div class="splash-screen-landing__dog" style="background-image: url('/static/build/images/content/landing-splash-screen_dog.png')"></div>
        <div class="splash-screen-landing__cat" style="background-image: url('/static/build/images/content/landing-splash-screen_cat.png?v=1')"></div>
        <div class="splash-screen-landing__feed-left" style="background-image: url('/static/build/images/content/landing-splash-screen_left.png')"></div>
        <div class="splash-screen-landing__feed-right" style="background-image: url('/static/build/images/content/landing-splash-screen_right.png')"></div>

        <div class="container-landing">
            <div class="splash-screen-landing__content">
                <div class="splash-screen-landing__title">
                    <span>Как выиграть</span>
                    <span class="splash-screen-landing__title-wide">запас корма</span>
                </div>
                <div class="splash-screen-landing__subtitle">
                    <span class="splash-screen-landing__subtitle-label">на год</span>
                    <span>вперёд</span>
                </div>
                <div class="splash-screen-landing__date">С 1 по 28 февраля 2019 г</div>

                <div class="splash-screen-landing__btn-wrap">
                    <div class="landing-btn" data-btn-scroll-landing="regulations">Узнать подробности</div>
                </div>

            </div>
        </div>
    </section>

    <section data-id-section-landing="regulations" class="regulations-landing">
        <div class="container-landing">
            <div class="landing-title">Как принять участие в&nbsp;акции</div>
            <ol class="regulations-landing__list">
                <li>Купите любые корма Grandin на&nbsp;сумму от&nbsp;1800&nbsp;рублей и&nbsp;получите миску Grandin в&nbsp;подарок*</li>
                <li>Зарегистрируйте покупку, и&nbsp;вы сможете принять участие в&nbsp;розыгрыше призов</li>
                <li>Проверяйте результаты розыгрыша каждую&nbsp;пятницу</li>
            </ol>

            <?if ($USER->IsAuthorized()) {?>
                <div class="regulations-landing__btn">
                    <div class="landing-btn" data-btn-scroll-landing="registr-check">Зарегистрировать чек</div>
                </div>
            <?} else {?>
                <div class="regulations-landing__btn">
                    <div class="landing-btn js-open-popup" data-popup-id="authorization">Принять участие</div>
                </div>
            <?}?>

        </div>
    </section>
</div>

<div class="b-page-wrapper landing-page-wrapper <?= $template->getWrapperClass() ?> js-this-scroll">

    <?php if ($template->hasMainWrapper()) { ?>
    <main class="b-wrapper<?= $template->getIndexMainClass() ?>" role="main">

<?php }

if ($template->hasContent()) {
    $asset->addCss('/include/static/style.css');
    $asset->addJs('/include/static/scripts.js');
}
