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

    <meta property="og:title" content="Выиграй путешествие на 2-их в Лето!"/>
    <meta property="og:description" content=""/>
    <meta property="og:image" content="<?='https://'.$_SERVER['SERVER_NAME'].'/leto2020/img/leto2020-share.jpg'?>">

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
    <div class="b-container">
        <div class="header-leto2020__content">
            <div class="header-leto2020__left">
                <a href="/" class="header-leto2020__logo">
                    <img src="/leto2020/img/logo.svg" alt="leto2020" title="leto2020"/>
                </a>
                <div class="header-leto2020__canin-logo">
                    <img src="/leto2020/img/canin-logo.svg" alt="leto2020" title="leto2020"/>
                </div>
            </div>
            <div class="header-leto2020-menu" data-mobile-menu-landing="true">
                <ul class="header-leto2020-menu__list" data-list-mobile-menu-landing="true">
                    <li  class="header-leto2020-menu__item">
                        <a href="javascript:void(0);"  class="header-leto2020-menu__link" data-btn-scroll-landing="prizes">Призы</a>
                    </li>

                    <li class="header-leto2020-menu__item">
                        <a href="javascript:void(0);"  class="header-leto2020-menu__link" data-btn-scroll-landing="regulations">Принять участие</a>
                    </li>

                    <li  class="header-leto2020-menu__item" style="<?$APPLICATION->ShowViewContent('empty-winners');?>">
                        <a href="javascript:void(0);"  class="header-leto2020-menu__link" data-btn-scroll-landing="winners">Победители</a>
                    </li>

                    <li  class="header-leto2020-menu__item">
                        <a href="javascript:void(0);"  class="header-leto2020-menu__link" data-btn-scroll-landing="where-buy">Где купить</a>
                    </li>

                    <li  class="header-leto2020-menu__item">
                        <a href="javascript:void(0);"  class="header-leto2020-menu__link" data-btn-scroll-landing="questions">Вопросы и ответы</a>
                    </li>

                    <?/*<li  class="header-leto2020-menu__item">
                        <a href="/" class="header-leto2020-menu__link" target="_blank">Правила акции</a>
                    </li>*/?>
                </ul>
            </div>
            <div class="header-leto2020__toggle-mobile-menu" data-toggle-mobile-menu-landing="true"><span></span></div>
        </div>
    </div>
</header>

<div class="page-leto2020">
    <div class="top-landing" data-top-landing="true">
        <section class="main-banner-leto2020">
            <div class="b-container">
                <div class="main-banner-leto2020__inner">
                    <div class="main-banner-leto2020__title">
                        Выиграй<br/> путешествие<br/> на&nbsp;<nobr>2-их</nobr> в&nbsp;Лето!
                    </div>
                    <div class="main-banner-leto2020__subtitle">
                        <p>+20 смартфонов</p>
                        <p>+200 power banks!</p>
                    </div>

                    <?if ($USER->IsAuthorized()) {?>
                        <div class="main-banner-leto2020__btn">
                            <div class="btn-leto2020" data-btn-scroll-landing="participate">Принять участие</div>
                        </div>
                    <?} else {?>
                        <div class="main-banner-leto2020__btn">
                            <div class="btn-leto2020 js-open-popup" data-popup-id="authorization">Принять участие</div>
                        </div>
                    <?}?>
                </div>
            </div>
        </section>

        <section data-id-section-landing="prizes" class="prizes-leto2020">
            <div class="b-container">
                <h2 class="title-leto2020 title-leto2020_blue">Общий призовой фонд</h2>
                <div class="prizes-leto2020__list">
                    <div class="item">
                        <div class="item-card">
                            <div class="item-card__img-wrap">
                                <div class="item-card__img" style="background-image: url('/leto2020/img/prizes1.png')"></div>
                            </div>
                            <div class="item-card__title">Мощные Power Banks</div>
                            <div class="item-card__descr">
                                Всего 200&nbsp;шт<br />
                                Розыгрыш по&nbsp;50&nbsp;шт в&nbsp;неделю:<br />
                                <b>6,&nbsp;13,&nbsp;20 и&nbsp;27&nbsp;января</b>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="item-card">
                            <div class="item-card__img-wrap">
                                <div class="item-card__img" style="background-image: url('/leto2020/img/prizes2.png')"></div>
                            </div>
                            <div class="item-card__title">Современные смартфоны</div>
                            <div class="item-card__descr">
                                Всего 20&nbsp;шт<br />
                                Розыгрыши по&nbsp;5&nbsp;шт в&nbsp;неделю:<br/>
                                <b>6,&nbsp;13,&nbsp;20 и&nbsp;27&nbsp;января</b>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="item-card">
                            <div class="item-card__img-wrap">
                                <div class="item-card__img" style="background-image: url('/leto2020/img/prizes3.png')"></div>
                            </div>
                            <div class="item-card__title">Главный приз</div>
                            <div class="item-card__descr">
                                Незабываемое путешествие<br />
                                на&nbsp;<nobr>2-их</nobr> в&nbsp;Лето!<br />
                                <b>Розыгрыш: 3&nbsp;февраля</b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section data-id-section-landing="regulations" class="regulations-leto2020">
            <div class="b-container">
                <div class="title-leto2020">Как принять участие в розыгрыше призов</div>
                <div class="regulations-leto2020__inner">
                    <div class="regulations-leto2020__list">
                        <div class="item">
                            <div class="item__icon item__icon_pink"></div>
                            <div class="item__descr">
                                Совершай покупки от&nbsp;500&nbsp;руб. с&nbsp;1 по&nbsp;31 января в&nbsp;розничных магазинах,
                                на&nbsp;сайте и&nbsp;в&nbsp;мобильном приложении &laquo;Четыре Лапы&raquo;.
                                Больше покупок от&nbsp;500&nbsp;руб&nbsp;&mdash; больше шансов выиграть призы.
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__icon item__icon_green"></div>
                            <div class="item__descr">
                                Регистрируйся на&nbsp;этом сайте с&nbsp;1 по&nbsp;31 января для участия в&nbsp;розыгрыше призов.
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__icon item__icon_blue"></div>
                            <div class="item__descr">
                                Ищи себя в&nbsp;списках победителей на&nbsp;этом сайте каждый понедельник 6,&nbsp;13,&nbsp;20,&nbsp;27&nbsp;января и&nbsp;3&nbsp;февраля!
                            </div>
                        </div>
                    </div>
                    <?if ($USER->IsAuthorized()) {?>
                        <div class="regulations-leto2020__btn">
                            <div class="btn-leto2020 btn-leto2020_blue" data-btn-scroll-landing="participate">Принять участие</div>
                        </div>
                    <?} else {?>
                        <div class="regulations-leto2020__btn">
                            <div class="btn-leto2020 btn-leto2020_blue js-open-popup" data-popup-id="authorization">Принять участие</div>
                        </div>
                    <?}?>
                </div>
            </div>
        </section>

        <section class="info-leto2020">
            <div class="b-container">
                <div class="title-leto2020 title-leto2020_blue">Получай двойные и&nbsp;тройные шансы</div>
                <div class="info-leto2020__list">
                    <div class="item">
                        <div class="item__count">
                            <span>X</span>
                            <span class="item__number">2</span>
                        </div>
                        <div class="item__info">
                            <div class="item__title">шансы за&nbsp;покупки<br/> товаров брендов</div>
                            <div class="item__subtitle">
                                GRANDIN, ROYAL CANIN, АВВА, MEALFEEL, FRESH STEP, MURMIX, EVER CLEAN, MONGE,
                                UNOCAT, TRAINER, WELLKISS, YUMMY, CHATELL, CHEWELL, MURMIX, НАГРАДА, PADOVAN
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="item__count item__count_pink">
                            <span>X</span>
                            <span class="item__number">3</span>
                        </div>
                        <div class="item__info">
                            <div class="item__title">шансы за&nbsp;покупки<br/> товаров из&nbsp;категории
                            </div>
                            <div class="item__subtitle">Одежда и&nbsp;обувь</div>
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
