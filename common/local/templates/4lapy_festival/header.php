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
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
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

    <!-- GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Hind:300,400,500,600,700" rel="stylesheet" type="text/css">
    <link href="vendor/simple-line-icons/css/simple-line-icons.css" rel="stylesheet" type="text/css"/>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="css/main.css" rel="stylesheet" type="text/css"/>
    <link href="css/flying.css" rel="stylesheet" type="text/css"/>
    <link href="css/styles-form.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/styles-form.css') ?>" rel="stylesheet" type="text/css"/>

    <!-- PAGE LEVEL PLUGIN STYLES -->
    <link href="css/animate.css" rel="stylesheet">
    <link href="vendor/swiper/css/swiper.min.css" rel="stylesheet" type="text/css"/>
    <link href="vendor/magnific-popup/magnific-popup.css" rel="stylesheet" type="text/css"/>

    <!-- THEME STYLES -->
    <link href="css/elements.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/elements.css') ?>" rel="stylesheet" type="text/css"/>
    <link href="css/layout.min.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/layout.min.css') ?>" rel="stylesheet" type="text/css"/>
    <link href="css/interactive_map.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/interactive_map.css') ?>" rel="stylesheet" type="text/css"/>
    <link href="css/banner-top.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/banner-top.css') ?>" rel="stylesheet" type="text/css"/>

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.ico"/>

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

    <script data-skip-moving="true">
        window.configDefence = {
            cName: 'testcookie',
            cValue: 'e0a5fe5fe86ada300005a1978e97b378493ad3f'
        }
    </script>

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/blocks/counters_header.php'; ?>
</head>
<body id="body" class="body-landing-festival">
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/blocks/counters_body.php'; ?>
<?php $APPLICATION->ShowPanel(); ?>

    <?php include __DIR__ . '/blocks/banner-top.php'; ?>

    <!--========== HEADER ==========-->
    <header class="header navbar-static-top" data-header="true">
        <!-- Navbar -->
        <nav class="navbar" role="navigation" data-header-navbar="true">
            <div class="container">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="menu-container js_nav-item" data-header-menu-container-mobile="true">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="sr-only">Переключить навигацию</span>
                        <span class="toggle-icon"></span>
                    </button>

                    <!-- Logo -->
                    <div class="logo">
                        <a class="logo-wrap" href="#body">
                            <img class="logo-img logo-img-main" src="img/logo_01.png" alt="festival logo">
                            <a href="#body"><img class="logo-img logo-img-active" src="img/logo-dark.png" alt="Asentus Logo"></a>
                        </a>
                    </div>
                    <!-- End Logo -->
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse nav-collapse">
                    <div class="menu-container">
                        <ul class="nav navbar-nav">
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#map">карта</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#guest">Блогер-Шоу</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#dog_fest">dog fest</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#activity">активности</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#gala">главная сцена</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#dobrolap">добролап</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#konkurs">квест</a></li>
                            <!--<li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#interview">Я ПОЙДУ!</a></li>-->
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#partners">партнеры</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#contacts">контакты</a></li>
                            <li class="nav-item join-item"><a class="nav-item-child nav-item-hover js-open-popup" href="javascript:void(0);" onclick="ga('send', 'event', 'fest_go_menu', 'click', 'btn_menu_fest')" data-popup-id="form-festival"><span>Я ПОЙДУ</span></a></li>
                        </ul>
                    </div>
                </div>
                <!-- End Navbar Collapse -->
            </div>
        </nav>
        <!-- Navbar -->
    </header>
    <!--========== END HEADER ==========-->

    <div class="b-page-wrapper b-page-wrapper--festival">
        <!--========== SLIDER ==========-->
        <?/*<div class="promo-block">
            <video class="video-bg__video" preload="metadata" data-video-mute-btn="yes" data-video="{&quot;autoplay&quot;:true,&quot;loop&quot;:true,&quot;muted&quot;:false,&quot;controls&quot;:false,&quot;webkit-playsinline&quot;:&quot;&quot;,&quot;playsinline&quot;:&quot;&quot;}" id="video-bg-0" autoplay="" loop="" muted="false" webkit-playsinline="" playsinline="">
                <source src="img/1018230703-preview.mp4">
                Sorry, but your browser not support this format video
            </video>
            <div class="container" style="position: relative; z-index: 99;">
                <div class="margin-b-40">
                    <h1 class="promo-block-title">Прими участие</h1>
                    <p class="promo-block-text">Получи призы!</p>
                </div>

                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-filters">
                        <defs>
                            <filter id="filter-ripple-2">
                                <feImage xlink:href="img/ripple.png" x="30" y="20" width="0" height="0" result="ripple"></feImage>
                                <feDisplacementMap xChannelSelector="R" yChannelSelector="G" color-interpolation-filters="sRGB" in="SourceGraphic" in2="ripple" scale="20" result="dm" />
                                <feComposite operator="in" in2="ripple"></feComposite>
                                <feComposite in2="SourceGraphic"></feComposite>
                            </filter>
                        </defs>
                    </svg>
                    <button id="component-8" class="button button--8 btn-theme-md btn-white-bg" style="filter: url('#filter-ripple-2')"><i class="btn-icon icon-user"></i> Получить паспорт</button>





            </div>
        </div>*/?>
        <div class="promo-block" data-promo-block="true">
            <div class="desktop_hat">
                <img src="img/slide01.jpg" alt="" />
            </div>
            <div class="mobile_hat">
                <img src="img/mobile_head.jpg" alt="" />
            </div>
        </div>
        <!--========== SLIDER ==========-->

        <!--========== PAGE LAYOUT ==========-->
        <!-- Products -->
        <div id="about">



            <div class="container content-lg">
                <div class="[ c-shapes c-shapes--dynamic ]" data-shapes-dynamic="" data-shapes-set="home" id="scene-home">
                    <div class="[ c-shapes__wrapper ] scrollme" data-shapes-wrapper="">
                        <div class="[ c-shape c-shape--square ] animateme" id="shape-0" data-when="exit" data-from="1" data-to="0" data-translatex="-600" data-translatey="-486" data-rotatez="0"></div>
                        <div class="[ c-shape c-shape--square-o ] animateme" id="shape-1" data-when="exit" data-from="1" data-to="0" data-translatex="-250" data-translatey="-550" data-rotatez="180"></div>
                        <div class="[ c-shape c-shape--triangle ] animateme" id="shape-2" data-when="exit" data-from="1" data-to="0" data-translatex="-650" data-translatey="-520" data-rotatez="85"></div>
                        <div class="[ c-shape c-shape--triangle ] animateme" id="shape-3" data-when="exit" data-from="1" data-to="0" data-translatex="-600" data-translatey="-430" data-rotatez="45"></div>
                        <div class="[ c-shape c-shape--triangle ] animateme" id="shape-4" data-when="exit" data-from="1" data-to="0" data-translatex="-1080" data-translatey="-320" data-rotatez="360"></div>
                        <div class="[ c-shape c-shape--triangle ] animateme" id="shape-5" data-when="exit" data-from="1" data-to="0" data-translatex="-400" data-translatey="-200" data-rotatez="360"></div>
                        <div class="[ c-shape c-shape--circle ] animateme" id="shape-6" data-when="exit" data-from="1" data-to="0" data-translatex="-720" data-translatey="-150" data-rotatez="180"></div>
                        <div class="[ c-shape c-shape--circle-o ] animateme" id="shape-7" data-when="exit" data-from="1" data-to="0" data-translatex="-320" data-translatey="-200" data-rotatez="0"></div>
                        <div class="[ c-shape c-shape--rect ] animateme" id="shape-8" data-when="exit" data-from="1" data-to="0" data-translatex="498" data-translatey="-497" data-rotatez="270"></div>
                        <div class="[ c-shape c-shape--arc ] animateme" id="shape-9" data-when="exit" data-from="1" data-to="0" data-translatex="520" data-translatey="-400" data-rotatez="270"></div>
                        <div class="[ c-shape c-shape--arc ] animateme" id="shape-10" data-when="exit" data-from="1" data-to="0" data-translatex="920" data-translatey="-400" data-rotatez="0"></div>
                        <div class="[ c-shape c-shape--arc ] animateme" id="shape-11" data-when="exit" data-from="1" data-to="0" data-translatex="664" data-translatey="-431" data-rotatez="270"></div>
                        <div class="[ c-shape c-shape--arc ] animateme" id="shape-12" data-when="exit" data-from="1" data-to="0" data-translatex="836" data-translatey="-172" data-rotatez="180"></div>
                        <div class="[ c-shape c-shape--arc ] animateme" id="shape-13" data-when="exit" data-from="1" data-to="0" data-translatex="621" data-translatey="-337" data-rotatez="0"></div>
                        <div class="[ c-shape c-shape--arc ] animateme" id="shape-14" data-when="exit" data-from="1" data-to="0" data-translatex="520" data-translatey="50" data-rotatez="360"></div>
                        <div class="[ c-shape c-shape--arc ] animateme" id="shape-15" data-when="exit" data-from="1" data-to="0" data-translatex="802" data-translatey="-114" data-rotatez="180"></div>

                    </div>
                    <div class="row text-center margin-b-40">
                        <div class="col-sm-8 col-sm-offset-2">
                            <h2>ВСТРЕТИМСЯ НА ФЕСТИВАЛЕ</h2>
                            <hr class="header_line" />
                            <p>9&nbsp;июня в&nbsp;парке Сокольники на&nbsp;ярком летнем празднике ждем вас всей семьей, с&nbsp;друзьями, детьми и&nbsp;любимыми питомцами. С&nbsp;самого утра и&nbsp;до&nbsp;позднего вечера для гостей и&nbsp;четверолапых друзей открыты более 50&nbsp;развлекательных площадок, игры, мастер-классы, конкурсы и&nbsp;встречи.</p>
                            <p>Листайте ленту, регистрируйтесь и&nbsp;приходите!</p>
                        </div>

                        <div class="col-sm-12 col-lg-8 col-lg-offset-2">
                            <div class="icon_wrap">
                                <div class="icon">
                                    <img src="img/icons/ic01.png" alt="" />
                                </div>
                                <div class="icon">
                                    <img src="img/icons/ic02.png" alt="" />
                                </div>
                                <div class="icon">
                                    <img src="img/icons/ic03.png" alt="" />
                                </div>
                                <div class="icon">
                                    <img src="img/icons/ic04.png" alt="" />
                                </div>
                                <div class="icon">
                                    <img src="img/icons/ic05.png" alt="" />
                                </div>
                                <div class="icon">
                                    <img src="img/icons/ic06.png" alt="" />
                                </div>
                                <div class="icon">
                                    <img src="img/icons/ic07.png" alt="" />
                                </div>
                                <div class="icon">
                                    <img src="img/icons/ic08.png" alt="" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--// end row -->
            </div>
            <!-- Promo Banner -->
            <!--<div class="promo-banner">
                <div class="container-sm content-lg">
                    <p class="promo-banner-text">
                        <h2>ВКЛЮЧАЙСЯ!</h2>
                        <p>Взрывной заряд энергии на спортивных площадках Аджилити, Фрисби и DogFest для каждого участника.</p><p>Приходи с питомцем , зарядись настроением и покажи на что вы способны.</p>
                        <p>Друзья ждут!</p>
                    </p>
                </div>
            </div>-->
            <!-- End Promo Banner -->
        </div>

        <?php include __DIR__ . '/blocks/map.php'; ?>

        <!-- GUEST -->
        <div id="guest">
            <div class="container content-lg">
                <div class="row text-center margin-b-40">
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="schedule">
                            <a href="javascript:void(0);" class="js-open-popup" data-popup-id="schedule-bloggershow">Расписание</a>
                        </div>
                        <div class="bar"><h5>блогер-шоу</h5></div>
                        <h2>Общайся на Блогер-Шоу</h2>
                        <hr class="header_line" />
                        <p>В&nbsp;самый разгар праздника на&nbsp;блогер-шоу выступят популярные интернет-звезды, эксперты, актеры и&nbsp;ТВ ведущие. За&nbsp;их&nbsp;блогами следит вся страна, их&nbsp;питомцев знает каждый. Они расскажут яркие истории, поделятся секретами и&nbsp;полезными советами.<br/> Они будут рядом и&nbsp;вместе&nbsp;&mdash; не&nbsp;пропусти встречу!</p>
                    </div>
                    <!-- Latest Products -->
                </div>
            </div>
            <div class="festival-guest">
                <div class="festival-guest__list">
                    <div class="block_wrapper">
                        <div class="colour_descr violet">
                            <p>Звездный блогер, автор популярных каналов о&nbsp;жизни с&nbsp;животными Magic&nbsp;Family и&nbsp;Magic&nbsp;Pets. Хозяйка 4&nbsp;собачек и&nbsp;кошки.</p>
                            <div class="triangle violet-triangle-right"></div>
                        </div>
                        <div class="portrait">
                            <div class="portrait_title">
                                <h3 class="violet">Anny</h3>
                                <h3 class="second-portrait-title violet_text">Magic</h3>
                            </div>
                            <img src="img/anny_magic.jpg" alt="" />
                        </div>
                    </div>
                    <div class="block_wrapper">
                        <div class="colour_descr blue">
                            <p>самый юный видео-блогер, ведет канал на&nbsp;Youtube. Ждет в&nbsp;гости РЕБЯТ, которые любят снимать видео со&nbsp;своими питомцами</p>
                            <div class="triangle blue-triangle-right"></div>
                        </div>
                        <div class="portrait">
                            <div class="portrait_title">
                                <h3 class="blue">Viki</h3>
                                <h3 class="second-portrait-title blue_text">Show</h3>
                            </div>
                            <img src="img/vicky_show.jpg" alt="" />
                        </div>
                    </div>
                    <div class="block_wrapper">
                        <div class="colour_descr gray">
                            <p>Helen Yes - инстаграм-блогер, актриса, певица и&nbsp;самая весёлая мама двойняшек. Городские
                                приключения и&nbsp;полезные советы мамы, собаки и&nbsp;малышей.</p>
                            <div class="triangle gray-triangle-left"></div>
                        </div>
                        <div class="portrait">
                            <div class="portrait_title">
                                <h3 class="gray">Helen</h3>
                                <h3 class="second-portrait-title gray_text">Yes</h3>
                            </div>
                            <img src="img/sazhina.jpg?v=2" alt="" /></div>
                    </div>
                    <div class="block_wrapper">
                        <div class="colour_descr pink">
                            <p>друг и&nbsp;хозяин четырех хаски Юбэк, Бугатти, Фрэя и&nbsp;Даная. Автор историй с&nbsp;хаски в&nbsp;&laquo;Добром блоге Леши&raquo;</p>
                            <div class="triangle pink-triangle-left"></div>
                        </div>
                        <div class="portrait">
                            <div class="portrait_title">
                                <h3 class="pink">Alexey</h3>
                                <h3 class="second-portrait-title pink_text">Husky</h3>
                            </div>
                            <img src="img/sorokin.jpg" alt="" />
                        </div>
                    </div>
                    <div class="block_wrapper">
                        <div class="colour_descr green">
                            <p>Юлия Костюшкина – телеведущая, спортсменка, актриса и&nbsp;мама двух терьеров Олава и&nbsp;Оливии.</p>
                            <div class="triangle green-triangle-right"></div>
                        </div>
                        <div class="portrait">
                            <div class="portrait_title">
                                <h3 class="green">Юлия</h3>
                                <h3 class="second-portrait-title green_text">Костюшкина</h3>
                            </div>
                            <img src="img/kostyushkina.jpg" alt="" />
                        </div>
                    </div>
                    <div class="block_wrapper">
                        <div class="colour_descr blue">
                            <p>мама двоих детей, педиатр, ведущая рубрики &laquo;Супермама&raquo;, автор книг и&nbsp;любимый блогер всех мам</p>
                            <div class="triangle blue-triangle-right"></div>
                        </div>
                        <div class="portrait">
                            <div class="portrait_title">
                                <h3 class="blue">Анна</h3>
                                <h3 class="second-portrait-title blue_text">Левадная</h3>
                            </div>
                            <img src="img/levadnaya.jpg" alt="" />
                        </div>
                    </div>
                    <div class="block_wrapper">
                        <div class="colour_descr violet">
                            <p>ветеринар-эксперт, любящий папа для&nbsp;всех пушистых питомцев. зНАЕТ ВСЕ ОБ&nbsp;УХОДЕ ЗА&nbsp;ДОМАШНИМИ ЖИВОТНЫМИ</p>
                            <div class="triangle violet-triangle-left"></div>
                        </div>
                        <div class="portrait">
                            <div class="portrait_title">
                                <h3 class="violet">Кирилл</h3>
                                <h3 class="second-portrait-title violet_text">Скачков</h3>
                            </div>
                            <img src="img/skachkov.jpg" alt="" />
                        </div>
                    </div>
                    <div class="block_wrapper">
                        <div class="colour_descr pink">
                            <p>Виталий Орлов – педагог, руководитель кинологического центра «Школа Орлова», 30 лет
                                практики работы с&nbsp;домашними животными.</p>
                            <div class="triangle pink-triangle-left"></div>
                        </div>
                        <div class="portrait">
                            <div class="portrait_title">
                                <h3 class="pink">Виталий</h3>
                                <h3 class="second-portrait-title pink_text">Орлов</h3>
                            </div>
                            <img src="img/orlov.jpg?v=2" alt="" />
                        </div>
                    </div>
                </div>
            </div>
            <button class="join_btn js-open-popup" onclick="ga('send', 'event', 'fest_go_guest', 'click', 'btn_guest_fest')" data-popup-id="form-festival">я пойду!</button>
        </div>
        <!-- End guest -->
        <div id="dog_fest">
            <div class="container content-lg">
                <div class="row text-center margin-b-40" >
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="bar"><h5>DOG FEST</h5></div>
                        <h2>ИГРАЙ И ПОБЕЖДАЙ</h2>
                        <hr class="header_line" />
                        <p>Праздник для собак и&nbsp;про собак. Отличная возможность принять участие в&nbsp;спортивных играх с&nbsp;питомцем, поиграть в&nbsp;бассейне, стать звездой на&nbsp;костюмированном параде собак, найти новых друзей и&nbsp;попробовать силы в&nbsp;аджилити и&nbsp;фрисби. Принять участие может каждый.<br/> Приходи&nbsp;&mdash; друзья ждут!</p>

                        <div id="videoHolder"></div>

                        <button class="join_btn js-open-popup" onclick="ga('send', 'event', 'fest_go_dog', 'click', 'btn_dog_fest')" data-popup-id="form-festival">я пойду!</button>
                    </div>
                </div>
                <!--// end row -->
            </div>
        </div>
        <!-- ACTIVITY -->
        <div id="activity">
            <div class="container content-lg">
                <div class="row text-center margin-b-40">
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="bar"><h5>активности</h5></div>
                        <h2>отдыхай активно</h2>
                        <hr class="header_line" />
                        <p>Более 50&nbsp;веселых площадок для хорошего настроения. Самых маленьких ждут соревнования, мастер-классы и&nbsp;игры с&nbsp;питомцами. А&nbsp;еще мы&nbsp;подготовили аквагрим и&nbsp;фото-зону для 1000&nbsp;лайков. Если зажигательная ZUMBA, то&nbsp;обязательно на&nbsp;танцевальных площадках фестиваля.<br/> С&nbsp;друзьями, родителями, детьми и&nbsp;питомцами не&nbsp;останавливайся&nbsp;&mdash; участвуй во&nbsp;всем!</p>
                    </div>
                    <!-- Latest Products -->
                </div>
            </div>
            <div class="activity-frame">
                <div class="activity-frame__list">
                    <div class="image_wrapper">
                        <img src="img/relax01.jpg" alt="" />
                        <div class="milk_bar"><h3>ГИГАНТСКИЙ ТИР</h3></div>
                    </div>
                    <div class="image_wrapper">
                        <img src="img/relax02.jpg" alt="" />
                        <div class="milk_bar"><h3>МИНИ-ФУТБОЛ</h3></div>
                    </div>
                    <div class="image_wrapper">
                        <img src="img/relax03.jpg" alt="" />
                        <div class="milk_bar"><h3>МАСТЕР-КЛАССЫ</h3></div>
                    </div>
                    <div class="image_wrapper">
                        <img src="img/relax04.jpg" alt="" />
                        <div class="milk_bar"><h3>БАССЕЙН С ШАРИКАМИ</h3></div>
                    </div>
                    <div class="image_wrapper">
                        <img src="img/relax05.jpg" alt="" />
                        <div class="milk_bar"><h3>ЗУМБА</h3></div>
                    </div>
                    <div class="image_wrapper">
                        <img src="img/relax06.jpg" alt="" />
                        <div class="milk_bar"><h3>МИНИ-ГОЛЬФ</h3></div>
                    </div>
                    <div class="image_wrapper">
                        <img src="img/relax07.jpg" alt="" />
                        <div class="milk_bar"><h3>АКВАГРИМ</h3></div>
                    </div>
                    <div class="image_wrapper">
                        <img src="img/relax08.jpg" alt="" />
                        <div class="milk_bar"><h3>НАСТОЛЬНЫЕ ИГРЫ</h3></div>
                    </div>
                </div>
            </div>
            <button class="join_btn js-open-popup" onclick="ga('send', 'event', 'fest_go_activity', 'click', 'btn_activity_fest')" data-popup-id="form-festival">я пойду!</button>
        </div>
        <!-- End acitvity -->

        <!-- Work -->
        <div id="gala">
            <div class="content-md container">
                <div class="row text-center margin-b-40">
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="schedule">
                            <a href="javascript:void(0);" class="js-open-popup" data-popup-id="schedule-gala">Расписание</a>
                        </div>
                        <div class="bar"><h5>ГЛАВНАЯ СЦЕНА</h5></div>
                        <h2>зажигай на главной сцене</h2>
                        <hr class="header_line" />
                        <p>Вечер обещает быть жарким: на&nbsp;главной сцене Звездные гости Фестиваля&nbsp;&mdash; группа ARTIK&amp;ASTI и&nbsp;ведущие московские диджеи. Любимые хиты и&nbsp;впервые на&nbsp;открытой площадке новый альбом &laquo;7&raquo;. Музыкальная сцена ждет!</p>
                        <!-- Latest Products -->
                    </div>
                </div>
                <!-- Masonry Grid -->
                <div class="masonry-grid row row-space-2">
                    <div class="masonry-grid-sizer col-xs-6 col-sm-6 col-md-1"></div>
                    <div class="masonry-grid-item col-xs-12 col-sm-12 col-md-8 margin-b-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="full_width">
                                <img class="full-width img-responsive" src="img/stub-video-gala.png" alt="">
                                <iframe class="gala__video" src="https://www.youtube.com/embed/St9BslKyfgE" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>

                            <div class="mobile">
                                <img class="full-width img-responsive" src="img/stub-video-gala_mobile.png" alt="">
                                <iframe class="gala__video" src="https://www.youtube.com/embed/St9BslKyfgE" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                            <!--<div class="work-popup-overlay">
                                <div class="work-popup-content">
                                    <a href="javascript:void(0);" class="work-popup-close">Hide</a>
                                    <div class="margin-b-30">
                                        <h3 class="margin-b-5">Art Of Coding</h3>
                                        <span>Clean &amp; Minimalistic Design</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-8 work-popup-content-divider sm-margin-b-20">
                                            <div class="margin-t-10 sm-margin-t-0">
                                                <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud.</p>
                                                <ul class="list-inline work-popup-tag">
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Design,</a></li>
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Coding,</a></li>
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Portfolio</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="margin-t-10 sm-margin-t-0">
                                                <p class="margin-b-5"><strong>Project Leader:</strong> John Doe</p>
                                                <p class="margin-b-5"><strong>Designer:</strong> Alisa Keys</p>
                                                <p class="margin-b-5"><strong>Developer:</strong> Mark Doe</p>
                                                <p class="margin-b-5"><strong>Customer:</strong> Keenthemes</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>-->
                        </div>
                        <!-- End Work -->
                    </div>
                    <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 margin-b-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="work-overlay">
                                <img class="full-width img-responsive" src="img/a&a04.jpg" alt="">
                            </div>
                            <!--<div class="work-popup-overlay">
                                <div class="work-popup-content">
                                    <a href="javascript:void(0);" class="work-popup-close">Hide</a>
                                    <div class="margin-b-30">
                                        <h3 class="margin-b-5">Art Of Coding</h3>
                                        <span>Clean &amp; Minimalistic Design</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-8 work-popup-content-divider sm-margin-b-20">
                                            <div class="margin-t-10 sm-margin-t-0">
                                                <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud.</p>
                                                <ul class="list-inline work-popup-tag">
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Design,</a></li>
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Coding,</a></li>
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Portfolio</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="margin-t-10 sm-margin-t-0">
                                                <p class="margin-b-5"><strong>Project Leader:</strong> John Doe</p>
                                                <p class="margin-b-5"><strong>Designer:</strong> Alisa Keys</p>
                                                <p class="margin-b-5"><strong>Developer:</strong> Mark Doe</p>
                                                <p class="margin-b-5"><strong>Customer:</strong> Keenthemes</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>-->
                        </div>
                        <!-- End Work -->
                    </div>
                    <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 md-margin-b-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="work-overlay">
                                <img class="full-width img-responsive" src="img/a&a03.jpg" alt="">
                            </div>
                            <!--<div class="work-popup-overlay">
                                <div class="work-popup-content">
                                    <a href="javascript:void(0);" class="work-popup-close">Hide</a>
                                    <div class="margin-b-30">
                                        <h3 class="margin-b-5">Art Of Coding</h3>
                                        <span>Clean &amp; Minimalistic Design</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-8 work-popup-content-divider sm-margin-b-20">
                                            <div class="margin-t-10 sm-margin-t-0">
                                                <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud.</p>
                                                <ul class="list-inline work-popup-tag">
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Design,</a></li>
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Coding,</a></li>
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Portfolio</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="margin-t-10 sm-margin-t-0">
                                                <p class="margin-b-5"><strong>Project Leader:</strong> John Doe</p>
                                                <p class="margin-b-5"><strong>Designer:</strong> Alisa Keys</p>
                                                <p class="margin-b-5"><strong>Developer:</strong> Mark Doe</p>
                                                <p class="margin-b-5"><strong>Customer:</strong> Keenthemes</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>-->
                        </div>
                        <!-- End Work -->
                    </div>
                    <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 md-margin-b-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="work-overlay">
                                <img class="full-width img-responsive" src="img/a&a02.jpg" alt="">
                            </div>
                            <!--<div class="work-popup-overlay">
                                <div class="work-popup-content">
                                    <a href="javascript:void(0);" class="work-popup-close">Hide</a>
                                    <div class="margin-b-30">
                                        <h3 class="margin-b-5">Art Of Coding</h3>
                                        <span>Clean &amp; Minimalistic Design</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-8 work-popup-content-divider sm-margin-b-20">
                                            <div class="margin-t-10 sm-margin-t-0">
                                                <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud.</p>
                                                <ul class="list-inline work-popup-tag">
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Design,</a></li>
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Coding,</a></li>
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Portfolio</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="margin-t-10 sm-margin-t-0">
                                                <p class="margin-b-5"><strong>Project Leader:</strong> John Doe</p>
                                                <p class="margin-b-5"><strong>Designer:</strong> Alisa Keys</p>
                                                <p class="margin-b-5"><strong>Developer:</strong> Mark Doe</p>
                                                <p class="margin-b-5"><strong>Customer:</strong> Keenthemes</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>-->
                        </div>
                        <!-- End Work -->
                    </div>
                    <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="work-overlay">
                                <img class="full-width img-responsive" src="img/a&a01.jpg" alt="">
                            </div>
                            <!--<div class="work-popup-overlay">
                                <div class="work-popup-content">
                                    <a href="javascript:void(0);" class="work-popup-close">Hide</a>
                                    <div class="margin-b-30">
                                        <h3 class="margin-b-5">Art Of Coding</h3>
                                        <span>Clean &amp; Minimalistic Design</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-8 work-popup-content-divider sm-margin-b-20">
                                            <div class="margin-t-10 sm-margin-t-0">
                                                <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud.</p>
                                                <ul class="list-inline work-popup-tag">
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Design,</a></li>
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Coding,</a></li>
                                                    <li class="work-popup-tag-item"><a class="work-popup-tag-link" href="#">Portfolio</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="margin-t-10 sm-margin-t-0">
                                                <p class="margin-b-5"><strong>Project Leader:</strong> John Doe</p>
                                                <p class="margin-b-5"><strong>Designer:</strong> Alisa Keys</p>
                                                <p class="margin-b-5"><strong>Developer:</strong> Mark Doe</p>
                                                <p class="margin-b-5"><strong>Customer:</strong> Keenthemes</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>-->
                        </div>
                        <!-- End Work -->
                    </div>
                </div>
                <!-- End Masonry Grid -->
                <button class="join_btn js-open-popup" onclick="ga('send', 'event', 'fest_go_gala_fest', 'click', 'btn_gala_fest')" data-popup-id="form-festival">я пойду!</button>
            </div>
        </div>
        <!-- Work -->
        <div id="dobrolap">
            <div class="content-md container">
                <div class="row text-center margin-b-40">
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="bar"><h5>добролап</h5></div>
                        <h2>возьми друга домой</h2>
                        <hr class="header_line" />
                        <p>Если у&nbsp;вас дома еще не&nbsp;живет пушистый питомец, то&nbsp;забрать друга домой можно на&nbsp;выставке-пристройстве &laquo;Добролап&raquo;. В&nbsp;ожидании чуда на&nbsp;одной площадке собрались питомцы, которые будут рады новому хозяину. Приходите все вместе. Ты&nbsp;можешь больше, чем ты&nbsp;думаешь!</p>
                        <!-- Latest Products -->
                    </div>
                </div>
                <!-- Masonry Grid -->
                <div class="masonry-grid row row-space-2">
                    <div class="masonry-grid-sizer col-xs-6 col-sm-6 col-md-1"></div>

                    <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 margin-b-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="work-overlay">
                                <img class="full-width img-responsive" src="img/dobrol01.jpg" alt="">
                            </div>
                        </div>
                        <!-- End Work -->
                    </div>
                    <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 margin-b-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="work-overlay">
                                <img class="full-width img-responsive" src="img/dobrol02.jpg" alt="">
                            </div>
                        </div>
                        <!-- End Work -->
                    </div>
                    <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 md-margin-b-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="work-overlay">
                                <img class="full-width img-responsive" src="img/dobrol03.jpg" alt="">
                            </div>
                        </div>
                        <!-- End Work -->
                    </div>
                    <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 md-margin-b-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="work-overlay">
                                <img class="full-width img-responsive" src="img/dobrol04.jpg" alt="">
                            </div>
                        </div>
                        <!-- End Work -->
                    </div>
                    <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="work-overlay">
                                <img class="full-width img-responsive" src="img/dobrol05.jpg" alt="">
                            </div>
                        </div>
                        <!-- End Work -->
                    </div>
                    <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="work-overlay">
                                <img class="full-width img-responsive" src="img/dobrol06.jpg" alt="">
                            </div>
                        </div>
                        <!-- End Work -->
                    </div>
                </div>
                <!-- End Masonry Grid -->
                <button class="join_btn js-open-popup" onclick="ga('send', 'event', 'fest_go_dobrolap', 'click', 'btn_dobrolap_fest')" data-popup-id="form-festival">я пойду!</button>
            </div>
        </div>
        <div id="konkurs">
            <!-- Promo Banner -->
            <div class="promo-banner">
                <div class="video_wrapper">
                    <video loop muted autoplay class="fullscreen-bg__video">
                        <source src="img/paris.mp4" type="video/mp4">
                    </video>
                </div>
                <div class="container-sm content-lg">
                    <div class="row text-center margin-b-40">
                        <div class="col-sm-8 col-sm-offset-2">
                            <div class="bar"><h5>квест «Хочу в&nbsp;париж»</h5></div>
                            <h2>выиграй путешествие в&nbsp;париж</h2>
                            <hr class="header_line" />
                            <p class="promo-banner-text">всего четыре шага и&nbsp;ты у&nbsp;цели</p>
                        </div>
                    </div>
                    <div class="konkurs_scheme">
                        <div class="step">
                            <div class="step_inner">
                                <h5>шаг 1</h5>
                                <h3>Зарегистрируйся и&nbsp;получи Приглашение с&nbsp;индивидуальным кодом участника.</h3>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step_inner">
                                <h5>шаг 2</h5>
                                <h3>Приходи на&nbsp;Фестиваль 9&nbsp;июня, получи Паспорт участника и&nbsp;скидку&nbsp;10% на&nbsp;любые покупки в&nbsp;зоомагазине &laquo;Четыре лапы&raquo;.</h3>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step_inner">
                                <h5>шаг 3</h5>
                                <h3>Путешествуй по&nbsp;Фестивалю, отдыхай, участвуй в&nbsp;конкурсах.</h3>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step_inner">
                                <h5>шаг 4</h5>
                                <h3>Выиграй путешествие в&nbsp;Париж и&nbsp;еще множество призов</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="join_btn js-open-popup" onclick="ga('send', 'event', 'fest_go_konkurs', 'click', 'btn_konkurs_fest')" data-popup-id="form-festival">я пойду!</button>
        </div>
        <!-- End Promo Banner -->

        <!-- partners -->
        <div id="partners">
            <div class="content-md container">
                <div class="row text-center">
                    <div class="col-sm-8 col-sm-offset-2">
                        <h2>главные партнёры</h2>
                        <hr class="header_line" />
                        <!-- Latest Products -->
                    </div>
                </div>
                <div class="partners_wrap">
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/partners01.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/partners02.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/partners03.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/partners04.jpg" alt="">
                    </div>
                </div>
                <div class="clear"></div>

            </div>
            <div class="content-md container">
                <div class="row text-center">
                    <div class="col-sm-8 col-sm-offset-2">
                        <h2>партнеры</h2>
                        <hr class="header_line" />
                        <!-- Latest Products -->
                    </div>
                </div>
                <div class="partners_wrap partners_wrap_line1">
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/elanco.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/msd.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/bayer.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/hills.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/acana.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/freshstep.png" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/everclean.png" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/boehringeringelheim.jpg" alt="">
                    </div>
                </div>
            </div>
            <div class="content-md container">
                <div class="row text-center">
                    <div class="col-sm-8 col-sm-offset-2">
                        <h2>специальные партнеры</h2>
                        <hr class="header_line" />
                        <!-- Latest Products -->
                    </div>
                </div>
                <div class="partners_wrap">
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/partners12.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/partners15.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/partners13.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/partners14.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/partners16.jpg" alt="">
                    </div>
                    <div class="partner_item">
                        <img class="partner_logo" src="img/partners/partners17.jpg" alt="">
                    </div>
                </div>
            </div>
        </div>
        <!-- End partners -->
        <!-- contacts -->
        <div id="contacts">
            <div class="content-md container">
                <div class="row text-center">
                    <div class="col-sm-8 col-sm-offset-2">
                        <h2>Контакты</h2>
                        <hr class="header_line" />
                        <!-- Latest Products -->
                    </div>
                </div>
                <div class="contacts_wrap">
                    <div class="contacts_item">
                        <img class="contacts_logo" src="img/contacts01.jpg" alt="">
                    </div>
                    <div class="contacts_item">
                        <img class="contacts_logo" src="img/contacts02.jpg" alt="">
                    </div>
                    <div class="contacts_item">
                        <a href="https://4lapy.ru" target="_blank"><img class="contacts_logo" src="img/contacts03.jpg" alt=""></a>
                    </div>
                </div>
                <div class="clear"></div>
                <p>Организатор Фестиваля&nbsp;&mdash; первая профессиональная сеть магазинов для домашних животных &laquo;Четыре лапы&raquo; объединяет владельцев питомцев, компании-партнеров и&nbsp;зоо-экспертов вместе, чтобы создать Pet friendly среду&nbsp;&mdash; территорию, где самое важное&nbsp;&mdash; это искренняя забота и&nbsp;ответственность за&nbsp;качество жизни, воспитание и&nbsp;взаимная любовь к&nbsp;четверолапым друзьям.</p>
            </div>
        </div>
        <!-- End contacts -->
        <!--========== END PAGE LAYOUT ==========-->