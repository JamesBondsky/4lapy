<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CMain $APPLICATION
 */

use Bitrix\Main\Application;use Bitrix\Main\Page\Asset;use FourPaws\App\Application as PawsApplication;use FourPaws\App\MainTemplate;use FourPaws\Decorators\SvgDecorator;use FourPaws\Enum\IblockCode;use FourPaws\Enum\IblockType;use FourPaws\SaleBundle\Service\BasketViewService;use FourPaws\UserBundle\Enum\UserLocationEnum;

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
</head>
<body class="body-lending">
<?php $APPLICATION->ShowPanel(); ?>

<header class="header-lending" data-header-lending="true">
    <div class="container-lending">
        <div class="header-lending__content">
            <div class="header-lending__logo">
                <img src="/static/build/images/content/grandin-logo.svg" alt="Grandin" title="Grandin"/>
            </div>
            <div class="header-lending-menu" data-mobile-menu-lending="true">
                <ul class="header-lending-menu__list" data-list-mobile-menu-lending="true">
                    <li  class="header-lending-menu__item">
                        <a href="#" class="header-lending-menu__link" target="_blank">Правила акции</a>
                    </li>
                    <!-- Если НЕ авторизован -->
                    <li class="header-lending-menu__item">
                        <a href="javascript:void(0);"  class="header-lending-menu__link js-open-popup" data-popup-id="authorization">Принять участие</a>
                    </li>
                    <!-- End Если НЕ авторизован -->
                    <!-- Если авторизован -->
                    <li class="header-lending-menu__item">
                        <a href="javascript:void(0);"  class="header-lending-menu__link" data-btn-scroll-lending="regulations">Принять участие</a>
                    </li>
                    <!-- End Если авторизован -->
                    <li  class="header-lending-menu__item">
                        <a href="javascript:void(0);"  class="header-lending-menu__link" data-btn-scroll-lending="prizes">Призы</a>
                    </li>
                    <li  class="header-lending-menu__item">
                        <a href="javascript:void(0);"  class="header-lending-menu__link" data-btn-scroll-lending="winners">Победители</a>
                    </li>
                    <li  class="header-lending-menu__item">
                        <a href="javascript:void(0);"  class="header-lending-menu__link" data-btn-scroll-lending="where-buy">Где купить</a>
                    </li>
                    <li  class="header-lending-menu__item">
                        <a href="javascript:void(0);"  class="header-lending-menu__link" data-btn-scroll-lending="contacts">Контакты</a>
                    </li>
                </ul>
            </div>
            <div class="header-lending__toggle-mobile-menu" data-toggle-mobile-menu-lending="true"><span></span></div>
        </div>
    </div>
</header>

<div class="top-lending" data-top-lending="true">
    <section class="splash-screen-lending">
        <div class="splash-screen-lending__bg" style="background-image: url('/static/build/images/content/bg-splash-lending.png')"></div>
        <div class="splash-screen-lending__dog" style="background-image: url('/static/build/images/content/lending-splash-screen_dog.png')"></div>
        <div class="splash-screen-lending__cat" style="background-image: url('/static/build/images/content/lending-splash-screen_cat.png')"></div>
        <div class="splash-screen-lending__feed-left" style="background-image: url('/static/build/images/content/lending-splash-screen_left.png')"></div>
        <div class="splash-screen-lending__feed-right" style="background-image: url('/static/build/images/content/lending-splash-screen_right.png')"></div>

        <div class="container-lending">
            <div class="splash-screen-lending__content">
                <div class="splash-screen-lending__title">
                    <span>Как выиграть</span>
                    <span class="splash-screen-lending__title-wide">запас корма</span>
                </div>
                <div class="splash-screen-lending__subtitle">
                    <span class="splash-screen-lending__subtitle-label">на год</span>
                    <span>вперёд</span>
                </div>
                <div class="splash-screen-lending__date">С 1 по 28 февраля 2019 г</div>

                <!-- Если авторизован -->
                <div class="splash-screen-lending__btn-wrap">
                    <div class="splash-screen-lending__btn" data-btn-scroll-lending="registr-check">Зарегистрировать чек</div>
                </div>
                <!-- End Если авторизован -->
            </div>
        </div>
    </section>

    <section data-id-section-lending="regulations" class="regulations-lending">
        <div class="container-lending">
            <div class="lending-title">Как принять участие в&nbsp;акции</div>
            <ol class="regulations-lending__list">
                <li>Купите любые корма Grandin на&nbsp;сумму от&nbsp;1800&nbsp;р. и&nbsp;получите миску Grandin в&nbsp;подарок</li>
                <li>Зарегистрируйте покупку, и&nbsp;вы сможете принять участие в&nbsp;розыгрыше призов</li>
                <li>Проверяйте результаты розыгрыша каждую&nbsp;пятницу</li>
            </ol>
            <!-- Если НЕ авторизован -->
            <div class="regulations-lending__btn">
                <div class="lending-btn js-open-popup" data-popup-id="authorization">Принять участие</div>
            </div>
            <!-- End Если НЕ авторизован -->
        </div>
    </section>
</div>

<div class="b-page-wrapper lending-page-wrapper <?= $template->getWrapperClass() ?> js-this-scroll">

    <?php if ($template->hasMainWrapper()) { ?>
    <main class="b-wrapper<?= $template->getIndexMainClass() ?>" role="main">

<?php }

if ($template->hasContent()) {
    $asset->addCss('/include/static/style.css');
    $asset->addJs('/include/static/scripts.js');
}
