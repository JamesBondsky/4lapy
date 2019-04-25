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
    <base href="<?= PawsApplication::getInstance()
        ->getSiteDomain() ?>">

    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>

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
    <link href="http://fonts.googleapis.com/css?family=Hind:300,400,500,600,700" rel="stylesheet" type="text/css">
    <link href="vendor/simple-line-icons/css/simple-line-icons.css" rel="stylesheet" type="text/css"/>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="css/main.css" rel="stylesheet" type="text/css"/>
    <link href="css/flying.css" rel="stylesheet" type="text/css"/>
    <link href="css/styles-form.css" rel="stylesheet" type="text/css"/>

    <!-- PAGE LEVEL PLUGIN STYLES -->
    <link href="css/animate.css" rel="stylesheet">
    <link href="vendor/swiper/css/swiper.min.css" rel="stylesheet" type="text/css"/>
    <link href="vendor/magnific-popup/magnific-popup.css" rel="stylesheet" type="text/css"/>

    <!-- THEME STYLES -->
    <link href="css/layout.min.css" rel="stylesheet" type="text/css"/>

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
</head>
<body id="body" data-spy="scroll" data-target=".header">
<?php $APPLICATION->ShowPanel(); ?>

    <!--========== HEADER ==========-->
    <header class="header navbar-fixed-top">
        <!-- Navbar -->
        <nav class="navbar" role="navigation">
            <div class="container">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="menu-container js_nav-item">
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
                        <ul class="nav navbar-nav navbar-nav-right">
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
                            <li class="nav-item join-item"><a class="nav-item-child nav-item-hover js-open-popup" href="#" data-popup-id="form-festival"><span>Я ПОЙДУ</span></a></li>
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
        <!--<div class="promo-block">
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
        </div>-->
        <div class="promo-block">
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

                        <div class="col-sm-8 col-sm-offset-2">
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
        <div id="map">
            <div class="container content-lg">
                <div class="row text-center margin-b-40" >
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="bar"><h5>карта</h5></div>
                        <h2>ВКЛЮЧАЙСЯ!</h2>
                        <hr class="header_line" />
                        <p>Проведи праздник с&nbsp;удовольствием. На&nbsp;пути к&nbsp;главному призу построй маршрут: попробуй свои силы в&nbsp;соревнованиях с&nbsp;питомцем, перекуси и&nbsp;обязательно загляни на&nbsp;блогер-шоу. Зарегистрируйся, чтобы получить паспорт участника и&nbsp;приходи на&nbsp;праздник с&nbsp;друзьями.</p>
                        <div class="interactive_map">
                            <img src="img/map.jpg" alt="" />
                            <div class="work-popup-overlay" id="permanent_popup">
                                <div class="work-popup-content">
                                    <div class="row">

                                        <div class="col-sm-3">
                                            <img src="img/img05_1.jpg" alt="" />
                                        </div>
                                        <div class="col-sm-2">
                                            <img src="img/img02_1.jpg" alt="" />
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="margin-t-10 sm-margin-t-0">
                                                <h2>НАЧНИ ПУТЕШЕСТВИЕ</h2>
                                                <p>Выбери площадку на карте Фестиваля и кликни на иконку, чтобы узнать больше.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <img src="img/img01_3.jpg" alt="" />
                                        </div>
                                        <div class="col-sm-3">
                                            <img src="img/img03_3.jpg" alt="" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="work work-popup-trigger">
                                <svg version="1.1" id="icon1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" xml:space="preserve">

                                        <g>
                                            <g>
                                                <g class="st0">
                                                    <g>
                                                        <polygon class="st1" points="65.5,94.4 61,95.4 45.5,79.8 50,78.8" />
                                                    </g>
                                                    <g class="st0">
                                                        <defs>
                                                            <path id="icon1_1_" class="st0" d="M55.2,39.5l4.5-1c-10.1,2.4-17.6,11.7-17.5,22.8c0.1,4.6,1.4,9,3.7,12.6
                                                                c0.7,1.1,1.4,2.1,2.3,3c0.6,0.6,1.2,1.2,1.8,1.8l-4.5,1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3
                                                                c-2.3-3.6-3.7-8-3.7-12.6C37.6,51.2,45.1,41.9,55.2,39.5z" />
                                                        </defs>
                                                        <clipPath id="icon1_2_">
                                                            <use xlink:href="#icon1_1_" style="overflow:visible;" />
                                                        </clipPath>
                                                        <g class="st2">
                                                            <path class="st3" d="M50,78.8l-4.5,1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.6-3.7-8-3.7-12.6
                                                                c-0.1-10.1,6-18.6,14.6-22l4.5-1c-8.7,3.3-14.7,11.9-14.6,22c0.1,4.6,1.4,9,3.7,12.6c0.7,1.1,1.4,2.1,2.3,3
                                                                C48.8,77.6,49.4,78.2,50,78.8" />
                                                            <path class="st4" d="M56.8,39.4l-4.5,1c0.9-0.4,1.9-0.7,2.9-0.9l4.5-1C58.7,38.7,57.8,39,56.8,39.4" />
                                                        </g>
                                                    </g>
                                                    <g>
                                                        <path class="st5" d="M64.8,37.9c12.6,0,23,10.5,23.1,23.4c0.1,4.6-1.2,9-3.4,12.6c-0.7,1.1-1.4,2.1-2.2,3l-1.7,1.8L65.5,94.4
                                                            L50,78.8c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.6-3.7-8-3.7-12.6C42.1,48.4,52.2,37.9,64.8,37.9z" />
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    <g>
                                        <g>
                                            <g>
                                                <g>
                                                    <path class="st6" d="M54.8,57.4l-1-0.9c2.8-3,6.5-4.9,10.6-5.2l0.1,1.3C60.8,52.9,57.3,54.6,54.8,57.4z" />
                                                </g>
                                                <g>
                                                    <path class="st6" d="M81.7,67.9h-8.6v-0.7c0-1.4-0.4-2.7-1.1-3.9l1.1-0.7c0.7,1.2,1.1,2.5,1.2,3.9h6c-0.2-4.8-2.7-9.1-6.7-11.7
                                                            l0.7-1.1c4.6,3,7.3,8,7.3,13.5V67.9z"/>
                                                </g>
                                                <g>
                                                    <path class="st6" d="M54.4,67.9h-4.8v-0.7c0-2.9,0.8-5.7,2.3-8.2l1.1,0.7c-1.2,2.1-2,4.5-2.1,6.9h3.5V67.9z" />
                                                </g>
                                            </g>
                                        </g>
                                        <g>
                                            <g>
                                                <g>
                                                    <path class="st6" d="M64.2,46.2c0.1,0,0.2,0,0.3,0.1l3.7,2.4l5.5,0c0.5,0,0.8,0.4,0.8,0.8c-0.1,1.2,0.1,2.2-0.3,3.4
                                                            c-0.5,1.3-3.4,2.1-3.4,2.1l0,3.7c0,0,0.8-0.3,1.8-0.3c0.7,0,1.4,0.1,2,0.5c0.6,0.4,1.2,0.8,1.6,1.2c0.3,0.3,0.5,0.7,0.5,1.1
                                                            c0.1,1-0.8,1.8-1.7,1.8c-0.3,0-0.6-0.1-0.8-0.2L72.5,62c0,0-0.7,1.4-3,1.8c-0.2,0-0.4,0-0.6,0c-2,0-3.2-1.4-3.2-1.4l-2.4,1.3
                                                            c0,0,0.4,1.4-0.8,3c-1.3,1.6-2.5,1.9-3.2,1.9c-0.4,0-0.7-0.1-0.7-0.1l-0.2,1.7c-0.8,2.1-2.3,2.8-3.4,2.8c-0.7,0-1.3-0.3-1.5-0.7
                                                            c-0.5-1.1,1.3-2,1.3-2v-2.2c0-0.5,0.2-1.1,0.6-1.4l1.2-1.2l-0.7-4.8c-2.1-0.9-3.4-1.7-4.6-2.9c-0.2-0.2-0.3-0.5-0.3-0.8
                                                            c0-0.4,0.3-0.6,0.7-0.6c0.1,0,0.2,0,0.3,0.1c2.2,1.2,3.7,1.5,4.5,1.5c0.5,0,0.7-0.1,0.7-0.1c3.4-0.4,7-2.8,7-2.8
                                                            c0.4-0.6,0.7-5,0.7-5l-1.2-3C63.5,46.5,63.8,46.2,64.2,46.2 M64.2,44.9c-0.6,0-1.2,0.3-1.6,0.9c-0.3,0.5-0.3,1.1-0.1,1.7l1,2.7
                                                            c-0.1,1.5-0.3,3.2-0.4,4c-0.9,0.6-3.6,2.1-6,2.4l-0.2,0l-0.1,0c-0.1,0-0.2,0-0.4,0c-0.6,0-1.9-0.2-3.9-1.3
                                                            c-0.3-0.2-0.6-0.3-1-0.3c-1,0-1.9,0.8-2,1.8c-0.1,0.7,0.2,1.4,0.7,1.9c1.3,1.3,2.6,2.1,4.4,2.9l0.5,3.4l-0.7,0.7
                                                            c-0.6,0.6-1,1.5-1,2.4v1.5c-1.1,0.8-1.8,2-1.2,3.3c0.4,0.9,1.4,1.5,2.7,1.5c1.6,0,3.6-0.9,4.6-3.6l0.1-0.2l0-0.2l0.1-0.5
                                                            c1.1-0.1,2.5-0.7,3.8-2.4c0.9-1.2,1.1-2.2,1.2-3l0.8-0.4c0.7,0.5,1.9,1.1,3.4,1.1c0.3,0,0.5,0,0.8-0.1c1.5-0.2,2.5-0.8,3.2-1.4
                                                            l0.5,0.3c0.4,0.2,1,0.4,1.5,0.4c0.8,0,1.7-0.3,2.2-1c0.6-0.6,0.8-1.4,0.8-2.2c0-0.8-0.4-1.5-0.9-2c-0.4-0.4-1-0.8-1.8-1.3
                                                            c-0.7-0.5-1.6-0.7-2.7-0.7c-0.2,0-0.3,0-0.5,0l0-1.2c0.2-0.1,0.5-0.2,0.7-0.3c1.4-0.6,2.3-1.4,2.6-2.3c0.4-1.1,0.4-2,0.4-2.9
                                                            c0-0.3,0-0.6,0-1c0-0.6-0.2-1.1-0.5-1.6c-0.4-0.4-0.9-0.7-1.5-0.7l0,0l0,0l-5.1,0l-3.4-2.2l0,0l0,0
                                                            C64.8,44.9,64.5,44.9,64.2,44.9L64.2,44.9z" />
                                                </g>
                                            </g>
                                        </g>
                                    </g>
                                        </svg>
                                <div class="work-popup-overlay">
                                    <div class="work-popup-content">
                                        <a href="javascript:void(0);" class="work-popup-close">×</a>
                                        <div class="row">

                                            <div class="col-sm-3">
                                                <img src="img/img05_1.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img05_2.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="margin-t-10 sm-margin-t-0">
                                                    <h2>DOG FEST</h2>
                                                    <p>Взрывной заряд энергии на спортивных площадках Аджилити и Фрисби , парад собак и игры в бассейне.</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img05_3.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-3">
                                                <img src="img/img05_4.jpg" alt="" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="work work-popup-trigger">
                                <svg version="1.1" id="icon2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" xml:space="preserve">
                                        <g>
                                            <g>
                                                <g class="st0">
                                                    <g>
                                                        <polygon class="st1" points="28.9,57.9 24.3,59 8.9,43.2 13.4,42.2               "/>
                                                    </g>
                                                    <g class="st0">
                                                        <defs>
                                                            <path id="icon2_1_" class="st0" d="M18.6,2.6l4.5-1.1C13,3.9,5.4,13.3,5.6,24.6c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                c0.6,0.7,1.2,1.3,1.8,1.8l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,14.4,8.4,5,18.6,2.6z"/>
                                                        </defs>
                                                        <clipPath id="icon2_2_">
                                                            <use xlink:href="#icon2_1_"  style="overflow:visible;"/>
                                                        </clipPath>
                                                        <g class="st2">
                                                            <path class="st3" d="M13.4,42.2l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,15.5,7.1,6.8,15.7,3.5l4.5-1.1c-8.7,3.3-14.8,12-14.7,22.1c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                C12.2,41,12.8,41.6,13.4,42.2"/>
                                                            <path class="st4" d="M20.3,2.5l-4.5,1.1c0.9-0.4,1.9-0.7,2.9-0.9l4.5-1.1C22.2,1.8,21.2,2.1,20.3,2.5"/>
                                                        </g>
                                                    </g>
                                                    <g>
                                                        <path class="st5" d="M28.2,1c12.6,0,23,10.6,23.2,23.6c0.1,4.7-1.2,9-3.4,12.7c-0.7,1.1-1.4,2.1-2.2,3.1L44,42.1L28.9,57.9
                                                            L13.4,42.2c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7C5.4,11.6,15.5,1,28.2,1z"/>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    <g>
                                        <g>
                                            <g>
                                                <g>
                                                    <g>
                                                        <g>
                                                            <path class="st6" d="M12,19.8c-0.3,0-0.7-0.2-0.8-0.5c-0.2-0.4-0.1-1,0.4-1.2L29,9.1c0.4-0.2,1-0.1,1.2,0.4
                                                                    c0.2,0.4,0.1,1-0.4,1.2l-17.5,8.9C12.3,19.7,12.1,19.8,12,19.8z"/>
                                                        </g>
                                                        <g>
                                                            <path class="st6" d="M46.8,19.8c-0.1,0-0.3,0-0.4-0.1l-17.5-8.9c-0.4-0.2-0.6-0.8-0.4-1.2c0.2-0.4,0.8-0.6,1.2-0.4l17.5,8.9
                                                                    c0.4,0.2,0.6,0.8,0.4,1.2C47.5,19.6,47.2,19.8,46.8,19.8z"/>
                                                        </g>
                                                    </g>
                                                    <g>
                                                        <g>
                                                            <rect x="15.7" y="16.3" class="st6" width="1.8" height="19.4"/>
                                                        </g>
                                                        <g>
                                                            <rect x="15.8" y="34.7" class="st6" width="27.2" height="1.8"/>
                                                        </g>
                                                        <g>
                                                            <rect x="41.3" y="16.3" class="st6" width="1.8" height="19.4"/>
                                                        </g>
                                                    </g>
                                                </g>
                                                <g>
                                                    <g>
                                                        <path class="st6" d="M27.5,29.7c-0.1,0-0.2,0-0.3-0.1c-1-0.2-1.7-1.4-1.1-2.6c0.2-0.3,0.4-0.5,0.8-0.6c0.7-0.2,1-0.7,1.2-1.4
                                                                c0-0.3,0.1-0.5,0.2-0.8c0.2-0.7,0.7-1.2,1.4-1.1c0.7,0,1.3,0.5,1.5,1.2c0.1,0.4,0.1,0.8,0.2,1.1c0.1,0.5,0.4,0.9,0.9,1.1
                                                                c1.4,0.4,1.2,2.2,0.3,2.9c-0.3,0.2-0.7,0.3-1,0.4c-0.2,0-0.5,0-0.7,0c-0.4-0.1-0.8-0.3-1.2-0.4c0,0-0.1,0-0.1,0
                                                                c-0.4,0.1-0.8,0.2-1.2,0.4C28,29.7,27.7,29.7,27.5,29.7z"/>
                                                    </g>
                                                    <g>
                                                        <path class="st6" d="M31.2,18.1c1.4,0.5,2.1,1.9,1.7,3.2c-0.1,0.2-0.2,0.4-0.3,0.6c-0.2,0.4-0.6,0.7-1.1,0.6
                                                                c-0.5-0.1-0.9-0.3-1-0.8c-0.1-0.3-0.2-0.6-0.2-0.9c0-0.6,0.1-1.3,0.1-1.9c0-0.3,0.2-0.6,0.5-0.8C31,18.1,31.1,18.1,31.2,18.1z"
                                                        />
                                                    </g>
                                                    <g>
                                                        <path class="st6" d="M28.8,18c0.4,0.3,0.5,0.7,0.5,1.1c0,0.7,0,1.4-0.1,2.1c-0.1,0.9-0.8,1.4-1.6,1.2c-0.2-0.1-0.5-0.3-0.6-0.5
                                                                c-0.7-1-0.4-2.8,0.6-3.5c0.3-0.2,0.6-0.3,0.9-0.4C28.6,18,28.7,18,28.8,18z"/>
                                                    </g>
                                                    <g>
                                                        <path class="st6" d="M25,22.6c0.1-0.2,0.3-0.2,0.5-0.1c0.5,0.3,1,0.7,1.3,1.2c0.2,0.4,0.3,0.9,0.1,1.3c-0.2,0.4-0.5,0.7-1,0.7
                                                                c-0.4,0-0.7-0.3-0.9-0.7c-0.1-0.2-0.1-0.4-0.2-0.7c-0.1-0.5-0.1-1,0.1-1.5C25,22.7,25,22.6,25,22.6z"/>
                                                    </g>
                                                    <g>
                                                        <path class="st6" d="M34.4,23c0.2,0.5,0.2,1.1,0,1.6c0,0.2-0.1,0.3-0.2,0.5c-0.2,0.5-0.6,0.7-1.1,0.7c-0.4,0-0.8-0.4-0.9-0.9
                                                                c-0.1-0.4,0-0.9,0.3-1.2c0.4-0.4,0.8-0.7,1.2-1c0.3-0.2,0.5-0.2,0.6,0.2C34.4,23,34.4,23,34.4,23z"/>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    </g>
                                        </svg>
                                <div class="work-popup-overlay">
                                    <div class="work-popup-content">
                                        <a href="javascript:void(0);" class="work-popup-close">×</a>
                                        <div class="row">

                                            <div class="col-sm-2">
                                                <img src="img/img02_1.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-3">
                                                <img src="img/img02_2.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="margin-t-10 sm-margin-t-0">
                                                    <h2>добролап</h2>
                                                    <p>Гостей, неравнодушных к пушистым четверолапым ждут на выставке-пристройстве, чтобы поддержать питомцев, или обрести друга</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img02_3.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-3">
                                                <img src="img/img02_4.jpg" alt="" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="work work-popup-trigger">
                                <svg version="1.1" id="icon3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" xml:space="preserve">

                                        <g>
                                            <g>
                                                <g class="st0">
                                                    <g>
                                                        <polygon class="st1" points="28.9,57.9 24.3,59 8.9,43.2 13.4,42.2               "/>
                                                    </g>
                                                    <g class="st0">
                                                        <defs>
                                                            <path id="icon3_1_" class="st0" d="M18.6,2.6l4.5-1.1C13,3.9,5.4,13.3,5.6,24.6c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                c0.6,0.7,1.2,1.3,1.8,1.8l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,14.4,8.4,5,18.6,2.6z"/>
                                                        </defs>
                                                        <clipPath id="icon2_2_">
                                                            <use xlink:href="#icon3_1_"  style="overflow:visible;"/>
                                                        </clipPath>
                                                        <g class="st2">
                                                            <path class="st3" d="M13.4,42.2l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,15.5,7.1,6.8,15.7,3.5l4.5-1.1c-8.7,3.3-14.8,12-14.7,22.1c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                C12.2,41,12.8,41.6,13.4,42.2"/>
                                                            <path class="st4" d="M20.3,2.5l-4.5,1.1c0.9-0.4,1.9-0.7,2.9-0.9l4.5-1.1C22.2,1.8,21.2,2.1,20.3,2.5"/>
                                                        </g>
                                                    </g>
                                                    <g>
                                                        <path class="st5" d="M28.2,1c12.6,0,23,10.6,23.2,23.6c0.1,4.7-1.2,9-3.4,12.7c-0.7,1.1-1.4,2.1-2.2,3.1L44,42.1L28.9,57.9
                                                            L13.4,42.2c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7C5.4,11.6,15.5,1,28.2,1z"/>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    <g>
                                        <g>
                                            <path class="st6" d="M15.1,17.9c0.7-0.2,1.3-0.7,2-0.6c1.9,0.2,3.7,0.6,5.8,0.9c0.4-1.1,0.8-2.5,1.2-3.8c0.6-1.8,0.8-1.9,2.7-1.6
                                                    c1.5,0.2,3.1,0.2,4.7,0.3c0.7,0,1.3,0.2,1.2,1c0,0.8-0.6,0.9-1.3,0.9c-1.2-0.1-2.4-0.2-3.5-0.2c-0.3,0-0.8,0.3-1,0.6
                                                    C26.5,16.2,26.3,17,26,18c0.1,0.1,0.2,0.1,0.3,0.2c0.6,0.3,1.6,0.5,1.8,1c0.5,1.2,0.7,2.5,1,3.8c0.1,0.6-0.7,0.9-0.3,1.7
                                                    c1.3,2.6,2.3,5.4,4.7,7.2c1.1,0.9,2.5,1.6,3.9,1.9c1.8,0.4,3.6,0.3,5.4,0.5c0.5,0.1,1,0.3,1.6,0.5c0,0.3,0,0.6,0,0.9
                                                    c-0.5,0.2-1,0.4-1.6,0.4c-0.8,0.1-1.5,0-2.3,0c-4.5-0.1-8.3-1.5-10.8-5.5c-0.9-1.4-1.5-2.9-2.3-4.3c-0.6-1.1-1.2-2.3-1.9-3.3
                                                    c-0.5-0.7-1.3-1.3-2.2-2.1c0,3.4,0,6.3,0,9.3c0,1.6,0.1,3.2,0,4.8c0,0.4-0.5,1-0.9,1.1c-0.7,0.2-1-0.4-1-1c0-0.4-0.1-0.7-0.1-1.1
                                                    c-1.4,0-2.7,0-4.1,0c-0.3,2.4-0.7,2.7-2.1,1.7C15.1,29.8,15.1,23.8,15.1,17.9z M21.2,23.9c-1.4,0-2.7,0-4,0c0,1,0,2,0,3.1
                                                    c0.6,0,1.1,0,1.6,0c2.8,0,2.8,0,2.5-2.9C21.3,24.1,21.3,24,21.2,23.9z M21.3,29c-1.4,0-2.7,0-4.1,0c0,1,0,2,0,3c1.4,0,2.7,0,4.1,0
                                                    C21.3,30.9,21.3,30,21.3,29z M17.2,19.2c0,1.1,0,1.9,0,2.7c1.4,0,2.7,0,4,0c0.3-1.9,0.2-2.1-1.5-2.4C19,19.3,18.1,19.3,17.2,19.2z
                                                    "/>
                                        </g>
                                        <g>
                                            <path class="st6" d="M29.3,10.5c0,1-0.8,1.9-1.8,1.9c-1,0-1.8-0.7-1.9-1.7c-0.1-1.1,0.7-1.9,1.7-2C28.4,8.7,29.3,9.5,29.3,10.5z"
                                            />
                                        </g>
                                    </g>
                                        </svg>
                                <div class="work-popup-overlay">
                                    <div class="work-popup-content">
                                        <a href="javascript:void(0);" class="work-popup-close">×</a>
                                        <div class="row">

                                            <div class="col-sm-3">
                                                <img src="img/img03_1.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img03_2.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="margin-t-10 sm-margin-t-0">
                                                    <h2>РАЗВЛЕЧЕНИЯ ДЛЯ ДЕТЕЙ</h2>
                                                    <p>Наших маленьких участников встречают веселые аниматоры с занимательными играми, аквагримом, мастер-классами, танцами.</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <img src="img/img03_3.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img03_4.jpg" alt="" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="work work-popup-trigger">
                                <svg version="1.1" id="icon4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" xml:space="preserve">

                                        <g>
                                            <g>
                                                <g class="st0">
                                                    <g>
                                                        <polygon class="st1" points="28.9,57.9 24.3,59 8.9,43.2 13.4,42.2               "/>
                                                    </g>
                                                    <g class="st0">
                                                        <defs>
                                                            <path id="icon4_1_" class="st0" d="M18.6,2.6l4.5-1.1C13,3.9,5.4,13.3,5.6,24.6c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                c0.6,0.7,1.2,1.3,1.8,1.8l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,14.4,8.4,5,18.6,2.6z"/>
                                                        </defs>
                                                        <clipPath id="icon4_2_">
                                                            <use xlink:href="#icon4_1_"  style="overflow:visible;"/>
                                                        </clipPath>
                                                        <g class="st2">
                                                            <path class="st3" d="M13.4,42.2l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,15.5,7.1,6.8,15.7,3.5l4.5-1.1c-8.7,3.3-14.8,12-14.7,22.1c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                C12.2,41,12.8,41.6,13.4,42.2"/>
                                                            <path class="st4" d="M20.3,2.5l-4.5,1.1c0.9-0.4,1.9-0.7,2.9-0.9l4.5-1.1C22.2,1.8,21.2,2.1,20.3,2.5"/>
                                                        </g>
                                                    </g>
                                                    <g>
                                                        <path class="st5" d="M28.2,1c12.6,0,23,10.6,23.2,23.6c0.1,4.7-1.2,9-3.4,12.7c-0.7,1.1-1.4,2.1-2.2,3.1L44,42.1L28.9,57.9
                                                            L13.4,42.2c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7C5.4,11.6,15.5,1,28.2,1z"/>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    <g>
                                        <g>
                                            <path class="st6" d="M28.6,9.7v1.9c0.4,0,0.7,0.1,1.1,0.3c0.6,0.3,1.3,0.8,1.4,2.4c0,0.7,0,1.4,0,2.2l-0.1,0v1.9c0,0.5,0,1,0,1.6
                                                    c0,1.1,0,2.2,0,3.3c-0.1,1-1.1,1.9-2.3,1.9l0,0c-1.2,0-2.2-0.9-2.3-1.9c-0.1-1.6-0.1-3.3-0.1-5c0-0.5,0-1,0-1.5
                                                    c0-1.1,0-2.1,0.1-3.1c0.1-1.1,1.1-2,2.3-2L28.6,9.7 M28.6,9.7c-2.1,0-4,1.7-4.2,3.7c-0.1,1.1-0.1,2.2-0.1,3.3c0,2.2,0,4.4,0.1,6.6
                                                    c0.1,2.1,2,3.6,4.2,3.7c0,0,0,0,0,0c2.1,0,4-1.6,4.2-3.7c0.1-1.6,0-3.3,0-5c0,0,0.1,0,0.1,0c0-1.4,0-2.7,0-4.1
                                                    c0-1.8-0.7-3.2-2.4-4.1C29.8,9.9,29.2,9.7,28.6,9.7L28.6,9.7z"/>
                                        </g>
                                        <g>
                                            <path class="st6" d="M23.1,35.6C23.1,35.5,23,35.5,23.1,35.6C22.9,34,22.9,34,24.3,34c1,0,1.9,0,2.9,0c0.4,0,0.6-0.1,0.5-0.5
                                                    c0-0.8,0-1.6,0-2.4c0-0.3-0.1-0.4-0.4-0.5c-3.7-0.6-6.5-3.6-6.7-7.4c-0.1-0.7-0.1-1.5-0.1-2.2c0.6,0,1.1,0,1.7,0
                                                    c0,0.4,0,0.9,0,1.3c0,2.7,1.1,4.8,3.6,6.1c2,1.1,4.8,0.8,6.6-0.7c1.2-1,2-2.2,2.3-3.6c0.1-0.7,0.2-1.4,0.2-2c0-0.4,0-0.7,0-1.1
                                                    c0.1,0,0.2,0,0.2,0c1.3,0,1.3,0,1.4,1.3c0.1,3.8-2.1,6.8-5.6,8.1c-0.4,0.1-0.8,0.2-1.2,0.3c-0.1,0-0.3,0.2-0.3,0.3c0,1,0,2,0,3
                                                    c1.6,0,3.1,0,4.7,0c0,0.6,0,1.1,0,1.6C30.5,35.6,26.8,35.6,23.1,35.6z"/>
                                        </g>
                                    </g>
                                        </svg>
                                <div class="work-popup-overlay">
                                    <div class="work-popup-content">
                                        <a href="javascript:void(0);" class="work-popup-close">×</a>
                                        <div class="row">

                                            <div class="col-sm-3">
                                                <img src="img/img01_1.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img01_2.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="margin-t-10 sm-margin-t-0">
                                                    <h2>КОНЦЕРТ</h2>
                                                    <p>На главной сцене Фестиваля весь день зажигательные мастер-классы, танцы, конкурсы и сеты от модных DJ Москвы. В 18.00 ARTIK@ASTI взорвут танцпол.</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img01_3.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-3">
                                                <img src="img/img01_4.jpg" alt="" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="work work-popup-trigger">
                                <svg version="1.1" id="icon5" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" xml:space="preserve">

                                        <g>
                                            <g>
                                                <g class="st0">
                                                    <g>
                                                        <polygon class="st1" points="28.9,57.9 24.3,59 8.9,43.2 13.4,42.2               "/>
                                                    </g>
                                                    <g class="st0">
                                                        <defs>
                                                            <path id="icon5_1_" class="st0" d="M18.6,2.6l4.5-1.1C13,3.9,5.4,13.3,5.6,24.6c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                c0.6,0.7,1.2,1.3,1.8,1.8l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,14.4,8.4,5,18.6,2.6z"/>
                                                        </defs>
                                                        <clipPath id="icon5_2_">
                                                            <use xlink:href="#icon5_1_"  style="overflow:visible;"/>
                                                        </clipPath>
                                                        <g class="st2">
                                                            <path class="st3" d="M13.4,42.2l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,15.5,7.1,6.8,15.7,3.5l4.5-1.1c-8.7,3.3-14.8,12-14.7,22.1c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                C12.2,41,12.8,41.6,13.4,42.2"/>
                                                            <path class="st4" d="M20.3,2.5l-4.5,1.1c0.9-0.4,1.9-0.7,2.9-0.9l4.5-1.1C22.2,1.8,21.2,2.1,20.3,2.5"/>
                                                        </g>
                                                    </g>
                                                    <g>
                                                        <path class="st5" d="M28.2,1c12.6,0,23,10.6,23.2,23.6c0.1,4.7-1.2,9-3.4,12.7c-0.7,1.1-1.4,2.1-2.2,3.1L44,42.1L28.9,57.9
                                                            L13.4,42.2c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7C5.4,11.6,15.5,1,28.2,1z"/>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    <g>
                                        <g>
                                            <g>
                                                <path class="st6" d="M38.1,36.4L38.1,36.4z M22.9,36.4c0.1,0,0.2-0.1,0.3-0.2c0,0,0.1-0.2,0.1-0.2c0,0,0-0.1,0-0.1
                                                        c0,0.2,0.1,0.3,0.3,0.5L22.9,36.4z M33.4,36.4c0.2-0.1,0.3-0.3,0.3-0.5c0.1,0.3,0.2,0.4,0.4,0.5L33.4,36.4z M36.8,36.4
                                                        c0.1,0,0.2-0.1,0.2-0.2c0.1-0.1,0.1-0.3,0-0.4c0.1,0.1,0.2,0.3,0.3,0.4c0.1,0.1,0.3,0.2,0.4,0.3L36.8,36.4z M19.3,36.4
                                                        c0.3-0.1,0.4-0.3,0.5-0.4c0.1-0.1,0.1-0.1,0.2-0.2c0,0.1,0,0.3,0,0.4c0.1,0.1,0.2,0.2,0.3,0.3C20,36.4,19.6,36.4,19.3,36.4z
                                                         M33.1,29.7c-0.2-0.5-0.7-0.5-0.8-0.5h0c0.1,0,0.2-0.1,0.2-0.2c0.1-0.1,0.1-0.3,0.1-0.4c0,0,0,0.1,0.1,0.1
                                                        c0.1,0.2,0.4,0.4,0.6,0.5c0,0.2-0.1,0.3-0.1,0.5C33.1,29.7,33.1,29.7,33.1,29.7z M23.9,29.7c0-0.1,0-0.3-0.1-0.5
                                                        c0.3-0.1,0.5-0.2,0.7-0.6c0,0.1,0,0.2,0.1,0.3c0.1,0.1,0.1,0.1,0.2,0.2C24.4,29.2,24.1,29.4,23.9,29.7z M30.2,29.2
                                                        c0.1,0,0.2-0.1,0.3-0.2c0.1-0.1,0.1-0.2,0.1-0.4c0,0,0,0,0,0.1c0.1,0.2,0.3,0.4,0.5,0.5L30.2,29.2z M26,29.2
                                                        c0.2-0.1,0.4-0.2,0.5-0.5c0,0,0,0,0,0c0,0.1,0,0.2,0.1,0.3c0.1,0.1,0.2,0.2,0.3,0.2C26.6,29.2,26.3,29.2,26,29.2z M29.7,26.7
                                                        c-0.1-0.1-0.3-0.2-0.4-0.3c0,0,0.1,0,0.1,0C29.5,26.4,29.5,26.4,29.7,26.7C29.6,26.6,29.6,26.6,29.7,26.7z M27.5,26.5
                                                        c0.1-0.1,0.1-0.1,0.2-0.1c0,0,0.1,0,0.1,0C27.7,26.4,27.5,26.5,27.5,26.5C27.4,26.6,27.5,26.5,27.5,26.5z M26.1,24.9
                                                        c0-0.1,0-0.1,0-0.2c-0.1-0.1-0.2-0.2-0.3-0.3c0,0,0,0,0,0c0,0,0.1,0,0.1,0l0.2,0c0.2,0,0.4-0.2,0.4-0.4l0,0c0,0.1,0,0.1,0.1,0.2
                                                        c0.1,0.1,0.1,0.1,0.2,0.2C26.3,24.5,26.2,24.8,26.1,24.9z M31,24.9c-0.1-0.3-0.5-0.5-0.7-0.5c0,0,0,0,0,0c0.1,0,0.2-0.1,0.2-0.2
                                                        c0.1-0.1,0.1-0.3,0.1-0.4l-0.2-0.9c0.1,0.3,0.2,0.7,0.3,1c0.1,0.5,0.5,0.6,0.7,0.6c0,0,0,0-0.1,0C31.1,24.6,31,24.7,31,24.9
                                                        C31,24.9,31,24.9,31,24.9z M29,17.6c-0.1-0.2-0.3-0.4-0.5-0.4c0,0,0,0,0,0c-0.2,0-0.3,0.1-0.4,0.2c0.1-0.9,0.3-1.9,0.3-2.8
                                                        c0-0.5,0.1-1,0.1-1.4c0-0.2-0.1-0.4-0.2-0.5c-0.1-0.1-0.1-0.1-0.2-0.2c0,0-0.1-0.1-0.1-0.1c0-0.4,0-0.8,0-1.2c0,0,0-0.1,0.2-0.1
                                                        c0.2-0.1,0.3-0.2,0.3-0.4c0.1,0.2,0.2,0.4,0.4,0.5c0.1,0.5,0,0.9,0,1.4c-0.4,0.3-0.4,0.6-0.4,0.8c0.1,1.7,0.3,3.3,0.5,4.9
                                                        L29,17.6z"/>
                                                <path class="st7" d="M29.1,9.3L29.1,9.3L29.1,9.3 M28.5,8.7c0,0-0.1,0-0.1,0c-0.3,0.1-0.4,0.3-0.4,0.5c0,0.4,0,0.9,0,1.3
                                                        c-0.4,0.2-0.5,0.2-0.5,0.5c0,0.5,0,0.9,0,1.4c0,0.2,0.2,0.3,0.3,0.4c0,0.1,0.1,0.1,0.1,0.2c0,0.5,0,0.9-0.1,1.4
                                                        c-0.2,2.2-0.5,4.3-1.1,6.4c-0.3,1-0.6,2-0.8,2.9c-0.4,0-0.7,0-0.8,0.4c-0.1,0.3,0.1,0.5,0.4,0.6c0,0.1-0.1,0.2-0.1,0.3
                                                        c-0.5,1.1-1,2.2-1.4,3.2c-0.1,0.2-0.2,0.3-0.4,0.3c-0.3,0-0.5,0.4-0.3,0.7c0.1,0.2,0.1,0.3,0,0.4c-1.1,2-2.4,4-3.9,5.8
                                                        c-0.1,0.2-0.3,0.3-0.6,0.3c-0.1,0-0.3,0.2-0.4,0.3c0,0.1,0,0.3,0.1,0.5c0.1,0.1,0.3,0.1,0.4,0.1c0.9,0,1.7,0,2.6,0
                                                        c0.7,0,1.3,0,2,0c0.1,0,0.2,0,0.3-0.1c0.3-0.1,0.4-0.6,0.1-0.8c-0.3-0.2-0.2-0.3-0.2-0.6c0.3-1.3,1-2.4,2.1-3.1
                                                        c0.8-0.5,1.7-0.8,2.6-0.8c2.2,0,4.4,1.6,4.7,4.1c0,0.1,0,0.2-0.1,0.2c-0.2,0.1-0.3,0.3-0.2,0.6c0.1,0.2,0.3,0.3,0.5,0.3
                                                        c0.6,0,1.3,0,1.9,0c0.9,0,1.8,0,2.7,0c0.3,0,0.6-0.2,0.6-0.5c0-0.3-0.2-0.5-0.6-0.5c-0.1,0-0.2-0.1-0.3-0.1
                                                        c-1.6-1.9-2.9-3.9-4.1-6c0-0.1,0-0.2,0-0.3c0.2-0.4,0-0.7-0.4-0.7c-0.1,0-0.2-0.1-0.3-0.2c-0.2-0.4-0.4-0.8-0.6-1.3
                                                        c-0.3-0.8-0.7-1.5-1-2.3c0.4-0.2,0.5-0.3,0.4-0.6c0-0.2-0.2-0.4-0.6-0.4c-0.2,0-0.2-0.1-0.3-0.2c-0.3-0.9-0.6-1.9-0.8-2.8
                                                        c-0.7-2.5-1-5.1-1.2-7.7c0-0.1,0-0.2,0.1-0.3c0.1-0.1,0.3-0.3,0.3-0.4c0-0.5,0-0.9,0-1.4c0-0.2-0.1-0.4-0.3-0.4
                                                        C29,10.6,29,10.5,29,10.3c0-0.4,0-0.8,0-1.2C29,8.9,28.8,8.7,28.5,8.7L28.5,8.7z M27,24c0.7-2.1,1.2-4.1,1.5-6.2
                                                        c0.5,2,1,4.1,1.5,6.2C29,24,28,24,27,24L27,24z M25,28.7c0.2-0.4,0.4-0.8,0.6-1.2c0.3-0.8,0.7-1.6,1-2.4c0.1-0.2,0.1-0.2,0.3-0.2
                                                        c0,0,0,0,0,0c1.1,0,2.3,0,3.4,0c0.1,0,0.2,0.1,0.3,0.2c0.5,1.1,1,2.3,1.5,3.5c0,0,0,0.1,0.1,0.2c-0.2,0-0.3,0-0.4,0
                                                        c-0.1,0-0.2,0-0.3,0c-0.1,0-0.2-0.1-0.2-0.2c-0.3-0.7-0.7-1.4-1-2.2c-0.1-0.3-0.4-0.5-0.7-0.5c0,0,0,0,0,0c-0.3,0-0.5,0-0.8,0
                                                        c-0.3,0-0.6,0-0.9,0c0,0,0,0,0,0c-0.3,0-0.5,0.1-0.6,0.4c-0.3,0.7-0.7,1.5-1,2.2c-0.1,0.2-0.2,0.2-0.3,0.2c0,0,0,0-0.1,0
                                                        c-0.1,0-0.1,0-0.2,0C25.3,28.7,25.1,28.7,25,28.7L25,28.7z M27,28.7c0.3-0.6,0.5-1.2,0.8-1.8c0-0.1,0.1-0.1,0.2-0.1
                                                        c0.2,0,0.4,0,0.6,0c0.2,0,0.3,0,0.5,0c0.1,0,0.2,0.1,0.2,0.2c0.3,0.6,0.5,1.1,0.8,1.7C29.1,28.7,28,28.7,27,28.7L27,28.7z
                                                         M20.5,35.9c0.2-0.3,0.4-0.5,0.6-0.8c1.2-1.6,2.3-3.3,3.3-5.2c0.1-0.2,0.2-0.3,0.5-0.3c0,0,0,0,0,0c1.3,0,2.6,0,3.9,0
                                                        c1.2,0,2.3,0,3.5,0c0,0,0,0,0,0c0.2,0,0.3,0,0.4,0.2c1.1,2,2.3,4,3.8,5.8c0,0.1,0.1,0.1,0.2,0.2c-0.4,0-0.8,0-1.2,0
                                                        c-0.3,0-0.7,0-1,0c0,0,0,0,0,0c-0.2,0-0.2-0.1-0.2-0.2c-0.2-1.7-1.1-3.1-2.5-4.1c-0.9-0.6-2.1-1-3.2-1c-1.3,0-2.6,0.4-3.6,1.3
                                                        c-1.2,1-1.9,2.2-2.1,3.7c0,0.1,0,0.1,0,0.2c0,0,0,0,0,0.1C22,35.9,21.3,35.9,20.5,35.9L20.5,35.9z"/>
                                            </g>
                                        </g>
                                        <g>
                                            <g>
                                                <path class="st8" d="M13.6,26.1h5.7V27h-5.7V26.1z"/>
                                                <polygon class="st7" points="19.3,26.1 13.6,26.1 13.6,27 19.3,27 19.3,26.1          "/>
                                            </g>
                                        </g>
                                        <g>
                                            <g>
                                                <path class="st8" d="M17.4,17.7l4.6,3.4l-0.5,0.7l-4.6-3.4L17.4,17.7z"/>
                                                <polygon class="st7" points="17.4,17.7 16.9,18.4 21.5,21.8 22,21.1 17.4,17.7            "/>
                                            </g>
                                        </g>
                                        <g>
                                            <g>
                                                <path class="st8" d="M38.4,26.1h5.7V27h-5.7V26.1z"/>
                                                <polygon class="st7" points="44.1,26.1 38.4,26.1 38.4,27 44.1,27 44.1,26.1          "/>
                                            </g>
                                        </g>
                                        <g>
                                            <g>
                                                <path class="st8" d="M40.3,17.7l-4.6,3.4l0.5,0.7l4.6-3.4L40.3,17.7z"/>
                                                <polygon class="st7" points="40.3,17.7 35.7,21.1 36.2,21.8 40.8,18.3 40.3,17.7          "/>
                                            </g>
                                        </g>
                                    </g>
                                        </svg>
                                <div class="work-popup-overlay">
                                    <div class="work-popup-content">
                                        <a href="javascript:void(0);" class="work-popup-close">×</a>
                                        <div class="row">

                                            <div class="col-sm-2">
                                                <img src="img/img04_1.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-3">
                                                <img src="img/img04_2.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="margin-t-10 sm-margin-t-0">
                                                    <h2>КВЕСТ «ХОЧУ В ПАРИЖ»</h2>
                                                    <p>Для самых активных: супер-квест, конкурсы и розыгрыш главного приза – путешествия в Париж.</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img04_3.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-3">
                                                <img src="img/img04_4.jpg" alt="" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="work work-popup-trigger">
                                <svg version="1.1" id="icon6" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" xml:space="preserve">
                                        <g>
                                            <g>
                                                <g class="st0">
                                                    <g>
                                                        <polygon class="st1" points="28.9,57.9 24.3,59 8.9,43.2 13.4,42.2               "/>
                                                    </g>
                                                    <g class="st0">
                                                        <defs>
                                                            <path id="icon6_1_" class="st0" d="M18.6,2.6l4.5-1.1C13,3.9,5.4,13.3,5.6,24.6c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                c0.6,0.7,1.2,1.3,1.8,1.8l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,14.4,8.4,5,18.6,2.6z"/>
                                                        </defs>
                                                        <clipPath id="icon6_2_">
                                                            <use xlink:href="#SVGID_1_"  style="overflow:visible;"/>
                                                        </clipPath>
                                                        <g class="st2">
                                                            <path class="st3" d="M13.4,42.2l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,15.5,7.1,6.8,15.7,3.5l4.5-1.1c-8.7,3.3-14.8,12-14.7,22.1c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                C12.2,41,12.8,41.6,13.4,42.2"/>
                                                            <path class="st4" d="M20.3,2.5l-4.5,1.1c0.9-0.4,1.9-0.7,2.9-0.9l4.5-1.1C22.2,1.8,21.2,2.1,20.3,2.5"/>
                                                        </g>
                                                    </g>
                                                    <g>
                                                        <path class="st5" d="M28.2,1c12.6,0,23,10.6,23.2,23.6c0.1,4.7-1.2,9-3.4,12.7c-0.7,1.1-1.4,2.1-2.2,3.1L44,42.1L28.9,57.9
                                                            L13.4,42.2c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7C5.4,11.6,15.5,1,28.2,1z"/>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    <g>
                                        <g>
                                            <g>
                                                <g>
                                                    <path class="st6" d="M29.1,35.7c-3.5,0-6.8-1.2-9.5-3.3C17,30.2,15,27.2,14,23.9c-0.2-0.6-0.1-1.2,0.3-1.8
                                                            c0.4-0.5,0.9-0.8,1.5-0.8h13.4v1.5H15.8c-0.2,0-0.3,0.1-0.4,0.2c-0.1,0.1-0.1,0.3-0.1,0.5c1.9,6.4,7.4,10.8,13.8,10.8
                                                            c6.4,0,12-4.3,13.8-10.8c0-0.2,0-0.3-0.1-0.5c-0.1-0.1-0.2-0.2-0.4-0.2h-2.7v-1.5h2.7c0.6,0,1.1,0.3,1.5,0.8
                                                            c0.4,0.5,0.5,1.1,0.3,1.8c-1,3.3-3,6.3-5.6,8.5C35.9,34.5,32.6,35.7,29.1,35.7z"/>
                                                </g>
                                                <g>
                                                    <path class="st6" d="M40,19.9c-1.5-5-5.8-8.4-10.8-8.4c-5,0-9.4,3.4-10.8,8.4L17,19.4c0.8-2.7,2.4-5.1,4.5-6.8
                                                            c2.2-1.8,4.9-2.7,7.6-2.7c2.8,0,5.4,0.9,7.6,2.7c2.1,1.7,3.8,4.1,4.5,6.8L40,19.9z"/>
                                                </g>
                                                <g>
                                                    <path class="st6" d="M25.7,20.6l-1.3-0.4c0.8-2.6,3-4.4,5.7-4.4c0.7,0,1.3,0.1,1.9,0.3l-0.5,1.4c-0.5-0.2-1-0.3-1.5-0.3
                                                            C28.1,17.2,26.3,18.6,25.7,20.6z"/>
                                                </g>
                                                <g>
                                                    <path class="st6" d="M22.3,20L21,19.6c0.5-1.8,1.5-3.4,2.9-4.6c3-2.6,7.2-2.8,10.4-0.5l-0.8,1.2c-2.7-1.9-6.2-1.7-8.8,0.4
                                                            C23.5,17.1,22.7,18.5,22.3,20z"/>
                                                </g>
                                                <g>
                                                    <path class="st6" d="M37.1,26.5l-1.4-0.3c0,0,0,0,0,0c0,0,0-0.4,0-2.2c0-1.2-0.8-1.4-1-1.4l-4.5,0.1l0-1.5l4.6-0.1l0,0
                                                            c0.8,0.1,2.2,0.8,2.3,2.8C37.2,25.3,37.2,26.2,37.1,26.5z"/>
                                                </g>
                                                <g>
                                                    <path class="st6" d="M39.2,27.3L37.9,27l0.7,0.1L37.9,27c0,0,0.1-0.7,0-3.7c-0.1-2.5-1.8-2.8-2.1-2.9l-7.7,0.1l0-1.5l7.8-0.1
                                                            l0,0c1.1,0.1,3.3,1.2,3.4,4.3C39.4,26.5,39.3,27.2,39.2,27.3z"/>
                                                </g>
                                            </g>
                                        </g>
                                    </g>
                                        </svg>
                                <div class="work-popup-overlay">
                                    <div class="work-popup-content">
                                        <a href="javascript:void(0);" class="work-popup-close">×</a>
                                        <div class="row">

                                            <div class="col-sm-3">
                                                <img src="img/img06_1.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img06_2.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="margin-t-10 sm-margin-t-0">
                                                    <h2>гастромаркет</h2>
                                                    <p>Утолить разгулявшийся аппетит можно в многочисленных маркетах вкусной еды.</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <img src="img/img06_3.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img06_4.jpg" alt="" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="work work-popup-trigger">
                                <svg version="1.1" id="icon7" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" xml:space="preserve">
                                        <g>
                                            <g>
                                                <g class="st0">
                                                    <g>
                                                        <polygon class="st1" points="28.9,57.9 24.3,59 8.9,43.2 13.4,42.2               "/>
                                                    </g>
                                                    <g class="st0">
                                                        <defs>
                                                            <path id="icon7_1_" class="st0" d="M18.6,2.6l4.5-1.1C13,3.9,5.4,13.3,5.6,24.6c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                c0.6,0.7,1.2,1.3,1.8,1.8l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,14.4,8.4,5,18.6,2.6z"/>
                                                        </defs>
                                                        <clipPath id="icon7_2_">
                                                            <use xlink:href="#icon7_1_"  style="overflow:visible;"/>
                                                        </clipPath>
                                                        <g class="st2">
                                                            <path class="st3" d="M13.4,42.2l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,15.5,7.1,6.8,15.7,3.5l4.5-1.1c-8.7,3.3-14.8,12-14.7,22.1c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                C12.2,41,12.8,41.6,13.4,42.2"/>
                                                            <path class="st4" d="M20.3,2.5l-4.5,1.1c0.9-0.4,1.9-0.7,2.9-0.9l4.5-1.1C22.2,1.8,21.2,2.1,20.3,2.5"/>
                                                        </g>
                                                    </g>
                                                    <g>
                                                        <path class="st5" d="M28.2,1c12.6,0,23,10.6,23.2,23.6c0.1,4.7-1.2,9-3.4,12.7c-0.7,1.1-1.4,2.1-2.2,3.1L44,42.1L28.9,57.9
                                                            L13.4,42.2c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7C5.4,11.6,15.5,1,28.2,1z"/>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    <g>
                                        <g>
                                            <path class="st6" d="M43.3,19.4c-1.7,0.6-3.4,1.1-5,1.7c-0.1,0.1-0.3,0.4-0.3,0.6c0,2.1,0,4.3,0,6.4c0,0.4-0.1,0.7-0.5,0.9
                                                    c-5.6,3-11.2,3-16.7,0c-0.4-0.2-0.5-0.5-0.5-0.9c0-2.1,0-4.2,0-6.4c0-0.5-0.1-0.7-0.6-0.8c-1-0.3-1.9-0.7-3-1c0,3.8,0,7.5,0,11.4
                                                    c-0.6,0-1.2,0-1.8,0c0-4.6,0-9.2,0-13.9c0.1,0,0.1-0.1,0.2-0.1c4.5-1.8,9-3.6,13.6-5.4c0.3-0.1,0.7-0.1,0.9,0.1
                                                    c2.6,1,5.1,2,7.7,3.1c2,0.8,3.9,1.6,5.9,2.4C43.3,18.2,43.3,18.8,43.3,19.4z M17.5,18.4c0.2,0.1,0.3,0.1,0.4,0.2
                                                    c3.6,1.3,7.2,2.5,10.8,3.8c0.3,0.1,0.7,0.1,1,0c3.1-1.1,6.2-2.2,9.3-3.2c0.6-0.2,1.1-0.4,1.8-0.7c-3.9-1.6-7.6-3-11.4-4.5
                                                    c-0.2-0.1-0.4,0-0.6,0c-2,0.8-4,1.6-5.9,2.4C21.1,16.9,19.3,17.6,17.5,18.4z M36.2,21.9C36,22,35.8,22,35.6,22.1
                                                    c-2,0.7-4,1.4-6,2.1c-0.3,0.1-0.7,0.1-0.9,0c-2-0.7-3.9-1.4-5.9-2C22.6,22,22.3,22,22,21.9c0,1.9,0,3.7,0,5.5
                                                    c0,0.2,0.1,0.4,0.3,0.5c4.5,2.3,9.1,2.3,13.6,0c0.1-0.1,0.3-0.3,0.3-0.4C36.2,25.6,36.2,23.8,36.2,21.9z"/>
                                        </g>
                                        <g>
                                            <path class="st6" d="M15,33.8c0.1-0.1,0.2-0.3,0.3-0.4c0.4-0.3,0.8-0.3,1.2,0.1c0.3,0.4,0.3,0.8,0,1.1c-0.3,0.4-0.8,0.4-1.2,0.1
                                                    c-0.1-0.1-0.2-0.3-0.4-0.4C15,34.1,15,34,15,33.8z"/>
                                        </g>
                                    </g>
                                        </svg>
                                <div class="work-popup-overlay">
                                    <div class="work-popup-content">
                                        <a href="javascript:void(0);" class="work-popup-close">×</a>
                                        <div class="row">

                                            <div class="col-sm-3">
                                                <img src="img/img07_1.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img07_2.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="margin-t-10 sm-margin-t-0">
                                                    <h2>лекторий</h2>
                                                    <p>Прокачай себя в модном лектории – узнаешь много интересного о питомцах и познакомишься с ведущими зоо-экспертами. </p>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <img src="img/img07_3.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img07_4.jpg" alt="" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="work work-popup-trigger">
                                <svg version="1.1" id="icon8" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" xml:space="preserve">
                                        <g>
                                            <g>
                                                <g class="st0">
                                                    <g>
                                                        <polygon class="st1" points="28.9,57.9 24.3,59 8.9,43.2 13.4,42.2 "/>
                                                    </g>
                                                    <g class="st0">
                                                        <defs>
                                                            <path id="icon8_1_" class="st0" d="M18.6,2.6l4.5-1.1C13,3.9,5.4,13.3,5.6,24.6c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                c0.6,0.7,1.2,1.3,1.8,1.8l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,14.4,8.4,5,18.6,2.6z"/>
                                                        </defs>
                                                        <clipPath id="icon8_2_">
                                                            <use xlink:href="#icon8_1_"  style="overflow:visible;"/>
                                                        </clipPath>
                                                        <g class="st2">
                                                            <path class="st3" d="M13.4,42.2l-4.5,1.1c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7
                                                                C0.9,15.5,7.1,6.8,15.7,3.5l4.5-1.1c-8.7,3.3-14.8,12-14.7,22.1c0.1,4.7,1.4,9,3.7,12.7c0.7,1.1,1.4,2.1,2.3,3
                                                                C12.2,41,12.8,41.6,13.4,42.2"/>
                                                            <path class="st4" d="M20.3,2.5l-4.5,1.1c0.9-0.4,1.9-0.7,2.9-0.9l4.5-1.1C22.2,1.8,21.2,2.1,20.3,2.5"/>
                                                        </g>
                                                    </g>
                                                    <g>
                                                        <path class="st5" d="M28.2,1c12.6,0,23,10.6,23.2,23.6c0.1,4.7-1.2,9-3.4,12.7c-0.7,1.1-1.4,2.1-2.2,3.1L44,42.1L28.9,57.9
                                                            L13.4,42.2c-0.6-0.6-1.2-1.2-1.8-1.8c-0.8-0.9-1.6-2-2.3-3c-2.3-3.7-3.7-8-3.7-12.7C5.4,11.6,15.5,1,28.2,1z"/>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    <g>
                                        <g>
                                            <path class="st6" d="M39.2,17.2c1,0,1.9,0.8,1.9,1.9v9.9c0,1-0.8,1.9-1.9,1.9h-2.7l-0.8,2.8c0,0.1-0.1,0.1-0.2,0.1
                                                    c-0.1,0-0.1,0-0.2-0.1l-1.5-2.8H17c-1,0-1.9-0.8-1.9-1.9v-9.9c0-1,0.8-1.9,1.9-1.9H39.2 M39.2,15.5H17c-1.9,0-3.5,1.6-3.5,3.5v9.9
                                                    c0,1.9,1.6,3.5,3.5,3.5h15.9l1,1.9c0.3,0.6,0.9,1,1.6,1c0.8,0,1.5-0.5,1.8-1.3l0.5-1.6h1.4c1.9,0,3.5-1.6,3.5-3.5v-9.9
                                                    C42.7,17.1,41.2,15.5,39.2,15.5L39.2,15.5z"/>
                                        </g>
                                        <g>
                                            <path class="st6" d="M18.8,20.8c0.3-0.1,1-0.1,1.6-0.1c0.8,0,1.2,0.1,1.6,0.3c0.4,0.2,0.6,0.6,0.6,1.1c0,0.5-0.3,0.9-0.9,1.2v0
                                                    c0.6,0.2,1.1,0.6,1.1,1.3c0,0.5-0.2,0.9-0.6,1.2c-0.4,0.3-1.1,0.5-2.1,0.5c-0.6,0-1.1,0-1.3-0.1V20.8z M20,22.9h0.4
                                                    c0.7,0,1-0.3,1-0.7c0-0.4-0.3-0.6-0.9-0.6c-0.3,0-0.4,0-0.5,0V22.9z M20,25.3c0.1,0,0.3,0,0.5,0c0.6,0,1.1-0.2,1.1-0.8
                                                    c0-0.5-0.5-0.8-1.1-0.8H20V25.3z"/>
                                            <path class="st6" d="M23.7,20.7h1.2v4.4h2.2v1h-3.4V20.7z"/>
                                            <path class="st6" d="M32.5,23.4c0,1.8-1.1,2.9-2.7,2.9c-1.6,0-2.6-1.2-2.6-2.8c0-1.6,1.1-2.9,2.7-2.9
                                                    C31.6,20.6,32.5,21.9,32.5,23.4z M28.6,23.5c0,1.1,0.5,1.8,1.3,1.8c0.8,0,1.3-0.8,1.3-1.9c0-1-0.5-1.8-1.3-1.8
                                                    C29,21.6,28.6,22.4,28.6,23.5z"/>
                                            <path class="st6" d="M37.9,26c-0.4,0.1-1.1,0.3-1.8,0.3c-1,0-1.7-0.3-2.2-0.7c-0.5-0.5-0.8-1.2-0.8-2c0-1.8,1.3-2.9,3.1-2.9
                                                    c0.7,0,1.2,0.1,1.5,0.3l-0.3,1c-0.3-0.1-0.7-0.2-1.3-0.2c-1,0-1.8,0.6-1.8,1.8c0,1.1,0.7,1.8,1.7,1.8c0.3,0,0.5,0,0.6-0.1V24h-0.8
                                                    v-1h2V26z"/>
                                        </g>
                                    </g>
                                        </svg>
                                <div class="work-popup-overlay">
                                    <div class="work-popup-content">
                                        <a href="javascript:void(0);" class="work-popup-close">×</a>
                                        <div class="row">

                                            <div class="col-sm-2">
                                                <img src="img/img08_1.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-3">
                                                <img src="img/img08_2.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="margin-t-10 sm-margin-t-0">
                                                    <h2>БЛОГЕР-ШОУ</h2>
                                                    <p>Самая горячая ток-площадка в разгар праздника! Собери друзей и не пропусти шоу с ведущими интернет-звездами.</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <img src="img/img08_3.jpg" alt="" />
                                            </div>
                                            <div class="col-sm-2">
                                                <img src="img/img08_4.jpg" alt="" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="join_btn js-open-popup" data-popup-id="form-festival">я пойду!</button>
                        </div>
                    </div>
                </div>
                <!--// end row -->


                <!--<div class="container-full-width">
                    <div class="row row-space-2">
                        <div class="col-sm-6 sm-margin-b-4">
                            <img class="img-responsive" src="img/970x647/01.jpg" alt="Image">
                        </div>
                        <div class="col-sm-6">
                            <img class="img-responsive" src="img/970x647/03.jpg" alt="Image">
                        </div>
                    </div>
                </div>-->
            </div>
        </div>

        <!-- GUEST -->
        <div id="guest">
            <div class="container content-lg">
                <div class="row text-center margin-b-40">
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="bar"><h5>блогеры</h5></div>
                        <h2>Блогер-Шоу</h2>
                        <hr class="header_line" />
                        <p>В&nbsp;самый разгар праздника, на&nbsp;блогер-шоу, выступят популярные интернет-звезды, эксперты, актеры и&nbsp;ТВ ведущие. За&nbsp;их&nbsp;блогами следит вся страна, их&nbsp;питомцев знает каждый. Они расскажут яркие истории, поделятся секретами и&nbsp;полезными советами.<br/> Они будут рядом и&nbsp;вместе&nbsp;&mdash; не&nbsp;пропусти встречу!</p>
                    </div>
                    <!-- Latest Products -->
                </div>
            </div>
            <div class="festival-guest">
                <div class="block_wrapper">
                    <div class="colour_descr violet">
                        <p>ANNY MAGIC - Звездный блогер каналов о жизни с животными MAGIC FAMILY и MAGIC PETS. Мама 5 собачек и кошки Рикки.</p>
                        <div class="triangle violet-triangle-right"></div>
                    </div>
                    <div class="portrait">
                        <div class="portrait_title">
                            <h3 class="violet">Anny</h3>
                            <h3 class="violet_text">Magic</h3>
                        </div>
                        <img src="img/anny_magic.jpg" alt="" />
                    </div>
                </div>
                <div class="block_wrapper">
                    <div class="colour_descr blue">
                        <p>самый юный видео-блогер, ведет канал на Youtube. Ждет в гости РЕБЯТ, которые любят снимать видео со своими питомцами</p>
                        <div class="triangle blue-triangle-right"></div>
                    </div>
                    <div class="portrait">
                        <div class="portrait_title">
                            <h3 class="blue">Viki</h3>
                            <h3 class="blue_text">Show</h3>
                        </div>
                        <img src="img/vicky_show.jpg" alt="" />
                    </div>
                </div>
                <div class="block_wrapper">
                    <div class="colour_descr gray">
                        <p>инстаграм-блогер, актриса, певица и&nbsp;самая весёлая мама двойняшек</p>
                        <div class="triangle gray-triangle-left"></div>
                    </div>
                    <div class="portrait">
                        <div class="portrait_title">
                            <h3 class="gray">Helen</h3>
                            <h3 class="gray_text">Yes</h3>
                        </div>
                        <img src="img/sazhina.jpg" alt="" /></div>
                </div>
                <div class="block_wrapper">
                    <div class="colour_descr pink">
                        <p>друг и&nbsp;хозяин четырех хаски Юбэк, Бугатти, Фрэя и&nbsp;Даная. Автор историй с&nbsp;хаски в&nbsp;&laquo;Добром блоге Леши&raquo;</p>
                        <div class="triangle pink-triangle-left"></div>
                    </div>
                    <div class="portrait">
                        <div class="portrait_title">
                            <h3 class="pink">Alexey</h3>
                            <h3 class="pink_text">Husky</h3>
                        </div>
                        <img src="img/sorokin.jpg" alt="" />
                    </div>
                </div>
                <div class="block_wrapper">
                    <div class="colour_descr green">
                        <p>актриса, спортсменка, ТЕЛЕВЕДУЩАЯ. мама ДВУХ терьерОВ, и&nbsp;щеночка Оливии</p>
                        <div class="triangle green-triangle-right"></div>
                    </div>
                    <div class="portrait">
                        <div class="portrait_title">
                            <h3 class="green">Юлия</h3>
                            <h3 class="green_text">Костюшкина</h3>
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
                            <h3 class="blue_text">Левадная</h3>
                        </div>
                        <img src="img/levadnaya.jpg" alt="" />
                    </div>
                </div>
                <div class="block_wrapper">
                    <div class="colour_descr violet">
                        <p>ветеринар-эксперт, любящий папа для всех пушистых питомцев. зНАЕТ ВСЕ ОБ&nbsp;УХОДЕ ЗА&nbsp;ДОМАШНИМИ ЖИВОТНЫМИ</p>
                        <div class="triangle violet-triangle-left"></div>
                    </div>
                    <div class="portrait">
                        <div class="portrait_title">
                            <h3 class="violet">Кирилл</h3>
                            <h3 class="violet_text">Скачков</h3>
                        </div>
                        <img src="img/skachkov.jpg" alt="" />
                    </div>
                </div>
                <div class="block_wrapper">
                    <div class="colour_descr pink">
                        <p>ведущий программы &laquo;Кто в&nbsp;доме хозяин&raquo; и&nbsp;организатор Школы Орлова по&nbsp;воспитанию ПИТОМЦЕВ</p>
                        <div class="triangle pink-triangle-left"></div>
                    </div>
                    <div class="portrait">
                        <div class="portrait_title">
                            <h3 class="pink">Виталий</h3>
                            <h3 class="pink_text">Орлов</h3>
                        </div>
                        <img src="img/orlov.jpg" alt="" />
                    </div>
                </div>
            </div>
            <button class="join_btn js-open-popup" data-popup-id="form-festival">я пойду!</button>
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

                        <button class="join_btn js-open-popup" data-popup-id="form-festival">я пойду!</button>
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
            <button class="join_btn js-open-popup" data-popup-id="form-festival">я пойду!</button>
        </div>
        <!-- End acitvity -->

        <!-- Work -->
        <div id="gala">
            <div class="content-md container">
                <div class="row text-center margin-b-40">
                    <div class="col-sm-8 col-sm-offset-2">
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
                    <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-8 margin-b-4">
                        <!-- Work -->
                        <div class="work work-popup-trigger">
                            <div class="full_width">
                                <iframe width="908" height="450" src="https://www.youtube.com/embed/St9BslKyfgE" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>

                            <div class="mobile">
                                <iframe width="345" height="169" src="https://www.youtube.com/embed/St9BslKyfgE" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
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
                <button class="join_btn js-open-popup" data-popup-id="form-festival">я пойду!</button>
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
                <button class="join_btn js-open-popup" data-popup-id="form-festival">я пойду!</button>
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
                            <div class="bar"><h5>квест «Хочу в париж»</h5></div>
                            <h2>выиграй путешествие в париж</h2>
                            <hr class="header_line" />
                            <p class="promo-banner-text">всего четыре шага и ты у цели</p>
                        </div>
                    </div>
                    <div class="konkurs_scheme">
                        <div class="vertical_line">
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
            </div>
            <button class="join_btn js-open-popup" data-popup-id="form-festival">я пойду!</button>
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
                <!-- Masonry Grid -->
                <div class="masonry-grid row row-space-2">
                    <div class="partners_wrap">
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners01.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners02.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners03.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners04.jpg" alt="">
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                <!-- End Masonry Grid -->
            </div>
            <div class="content-md container">
                <div class="row text-center">
                    <div class="col-sm-8 col-sm-offset-2">
                        <h2>партнеры</h2>
                        <hr class="header_line" />
                        <!-- Latest Products -->
                    </div>
                </div>
                <!-- Masonry Grid -->
                <div class="masonry-grid row row-space-2">
                    <div class="partners_wrap">
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners05.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners06.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners07.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners08.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners09.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners10.jpg" alt="">
                        </div>

                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners11.jpg" alt="">
                        </div>
                    </div>
                </div>
                <!-- End Masonry Grid -->
            </div>
            <div class="content-md container">
                <div class="row text-center">
                    <div class="col-sm-8 col-sm-offset-2">
                        <h2>специальные партнеры</h2>
                        <hr class="header_line" />
                        <!-- Latest Products -->
                    </div>
                </div>
                <!-- Masonry Grid -->
                <div class="masonry-grid row row-space-2">
                    <div class="partners_wrap">
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners12.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners13.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners14.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners15.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners16.jpg" alt="">
                        </div>
                        <div class="pertner_item">
                            <img class="partner_logo" src="img/partners17.jpg" alt="">
                        </div>
                    </div>
                </div>
                <!-- End Masonry Grid -->
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
                <!-- Masonry Grid -->
                <div class="masonry-grid row row-space-2">
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
                <!-- End Masonry Grid -->
            </div>
        </div>
        <!-- End contacts -->
        <!--========== END PAGE LAYOUT ==========-->