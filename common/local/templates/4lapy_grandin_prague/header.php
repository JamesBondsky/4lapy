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

    <meta property="og:title" content="Выиграй путешествие в Прагу на двоих!"/>
    <meta property="og:description" content="Для участия в акции купите любой корм Grandin на сумму от 1800 рублей и зарегистрируйте покупку на сайте акции grandin.4lapy.ru."/>
    <meta property="og:image" content="<?='https://'.$_SERVER['SERVER_NAME'].'/img/grandin-prague-share.png'?>"/>

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

<header class="header-landing header-landing--grandin-prague" data-header-landing="true">
    <div class="container-landing">
        <div class="header-landing__content">
            <div class="header-landing__logo">
                <img src="/static/build/images/content/grandin-logo.svg" alt="Grandin" title="Grandin"/>
            </div>
            <div class="header-landing-menu" data-mobile-menu-landing="true">
                <ul class="header-landing-menu__list" data-list-mobile-menu-landing="true">
                    <li  class="header-landing-menu__item">
                        <a href="/grandin_rules.pdf?v=2" class="header-landing-menu__link" target="_blank">Правила акции</a>
                    </li>

                    <li class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="regulations">Принять участие</a>
                    </li>

                    <li  class="header-landing-menu__item">
                        <a href="javascript:void(0);"  class="header-landing-menu__link" data-btn-scroll-landing="prizes">Призы</a>
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
            <div class="header-landing__toggle-mobile-menu" data-toggle-mobile-menu-landing="true"><span></span></div>
        </div>
    </div>
</header>

<div class="top-landing" data-top-landing="true">
    <section class="splash-screen-grandin-prague">
        <div class="splash-screen-grandin-prague__bg" style="background-image: url('/img/bg-splash-grandin-prague.png')"></div>
        <div class="splash-screen-grandin-prague__img-prizes" style="background-image: url('/img/prizes-splash-grandin-prague.png')"></div>

        <div class="container-landing">
            <div class="splash-screen-grandin-prague__content">
                <div class="splash-screen-grandin-prague__title">
                    <span class="small-title">Выиграй</span>
                    <span class="middle-title">путешествие</span>
                    <span class="big-title">в Прагу</span>
                    <span class="small-title small-title--float">на двоих!</span>
                </div>
                <div class="splash-screen-grandin-prague__date">С 1 по 30 июня 2019</div>

            </div>
        </div>

        <div class="splash-screen-grandin-prague__info-prizes">
            <div class="title-prizes">Призы:</div>
            <div class="content-prizes">
                <div class="content-prizes__spoon">+ электронная мерная ложка</div>
                <div class="content-prizes__bonus">
                    <span class="content-prizes__bonus-number">+100</span>
                    <span>бонусов</span>
                    <span class="content-prizes__bonus-small">на карту</span>
                </div>
            </div>
        </div>
    </section>

    <section data-id-section-landing="regulations" class="regulations-landing">
        <div class="container-landing">
            <div class="landing-title">Как принять участие в&nbsp;акции</div>
            <ol class="regulations-landing__list regulations-landing__list--grandin-prague">
                <li>Купите любые корма Grandin на&nbsp;сумму от&nbsp;1800&nbsp;рублей.</li>
                <li>Зарегистрируйте покупку и&nbsp;получите 100&nbsp;бонусов на&nbsp;карту.</li>
                <li>Проверяйте результаты розыгрыша 7, 13, 19, 25 июня и&nbsp;1&nbsp;июля.</li>
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
