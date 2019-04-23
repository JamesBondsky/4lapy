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
                            <!--<img class="logo-img logo-img-main" src="img/logo.png" alt="Asentus Logo">-->
                            <a href="#body" style="position: relative; top: 10px;"><img class="logo-img logo-img-active" src="img/logo-dark.png" alt="Asentus Logo"></a>
                        </a>
                    </div>
                    <!-- End Logo -->
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse nav-collapse">
                    <div class="menu-container">
                        <ul class="nav navbar-nav navbar-nav-right">
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#body">Фестиваль</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#map">Программа</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#guest">Блогер-Шоу</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#gala">ARTIK&ASTI</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#konkurs">Конкурс</a></li>
                            <li class="js_nav-item nav-item"><a class="nav-item-child nav-item-hover" href="#interview">Я ПОЙДУ!</a></li>
                            <li class="js_nav-item nav-item join-item"><a class="nav-item-child nav-item-hover js-open-popup" href="#" data-popup-id="authorization"><span>РЕГИСТРАЦИЯ</span></a></li>
                        </ul>
                    </div>
                </div>
                <!-- End Navbar Collapse -->
            </div>
        </nav>
        <!-- Navbar -->
    </header>
    <!--========== END HEADER ==========-->

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
        <!-- Swiper Testimonials -->
        <div class="swiper-slider swiper-testimonials">
            <!-- Swiper Wrapper -->
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <img src="img/slide01.jpg" alt="" />
                </div>
                <div class="swiper-slide">
                    <img src="img/slide01.jpg" alt="" />
                </div>
                <div class="swiper-slide">
                    <img src="img/slide01.jpg" alt="" />
                </div>
            </div>
            <!-- End Swiper Wrapper -->

            <!-- Pagination -->
            <div class="swiper-testimonials-pagination"></div>
        </div>
        <!-- End Swiper Testimonials -->
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
                        <h2>О фестивале</h2>
                        <p>Фестиваль &laquo;Четыре лапы&raquo;&nbsp;&mdash; яркое событие этого лета и&nbsp;долгожданный семейный праздник для тех, кто не&nbsp;может представить себе жизнь без любимых питомцев.</p>
                        <p>Организатор Фестиваля&nbsp;&mdash; первая профессиональная сеть магазинов для домашних животных &laquo;Четыре лапы&raquo; объединяет владельцев питомцев, компании-партнеров и&nbsp;зоо-экспертов вместе, чтобы создать Pet friendly среду&nbsp;&mdash; территорию, где самое важное&nbsp;&mdash; это искренняя забота и&nbsp;ответственность за&nbsp;качество жизни, воспитание и&nbsp;взаимная любовь к&nbsp;четверолапым друзьям.</p>
                    </div>
                </div>
            </div>
            <!--// end row -->
        </div>
        <!-- Promo Banner -->
        <div class="promo-banner">
            <div class="container-sm content-lg">
                <p class="promo-banner-text">9&nbsp;июня на&nbsp;территории парка Сокольники с&nbsp;самого утра и&nbsp;до&nbsp;позднего вечера гостей ждут более 50&nbsp;развлекательных событий: соревнования, танцы с&nbsp;питомцами, аквагрим, веселые старты, мастер-классы, детские игры с&nbsp;аниматорами и&nbsp;фотозоны. Для самых активных: супер-квест, конкурсы и&nbsp;розыгрыш главного приза&nbsp;&mdash; Путешествия в&nbsp;Париж.</p>
            </div>
        </div>
        <!-- End Promo Banner -->
    </div>
    <div id="map">
        <div class="container content-lg">
            <div class="row text-center margin-b-40" >
                <div class="col-sm-6 col-sm-offset-3">
                    <h2>Программа</h2>
                    <div class="interactive_map">
                        <img src="img/u273.png" alt="" />
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
        <div class="content-md container">
            <div class="row">

                <h2>Блогер-Шоу</h2>
                <p>В&nbsp;самый разгар праздника, на&nbsp;Блогер-Шоу, выступят популярные интернет-звезды, эксперты, актеры и&nbsp;ТВ ведущие. За&nbsp;их&nbsp;блогами следит вся страна, их&nbsp;питомцев знает каждый. Они расскажут яркие истории, поделятся секретами и&nbsp;полезными советами. Они будут рядом и&nbsp;вместе&nbsp;&mdash; не&nbsp;пропусти встречу!</p>
                <!-- Latest Products -->
            </div>
        </div>
        <div class="festival-guest">
            <div class="block_wrapper">
                <div class="colour_descr violet">
                    <h2>Anny</h2>
                    <h2>Magick</h2>
                    <p>блогер самых популярных каналов о&nbsp;животных Magic Family и&nbsp;Magic Pets. Мама 5&nbsp;собачек Софи, Эйван, Миши, Юми, Алисы и&nbsp;кошки Рики поделится яркими историями и&nbsp;лайфхаками из&nbsp;жизни с&nbsp;питомцем в&nbsp;большом городе</p>
                    <div class="triangle violet-triangle-right"></div>
                </div>
                <div class="portrait"><img src="img/anny_magic.jpg" alt="" /></div>
            </div>
            <div class="block_wrapper">
                <div class="colour_descr blue">
                    <h2>Viki</h2>
                    <h2>Show</h2>
                    <p>самый юный видео-блогер, вместе с&nbsp;мамой ведет канал на&nbsp;Youtube &laquo;Viki Show&raquo;. Ждет в&nbsp;гости мальчишек и&nbsp;девчонок, которые любят пушистых друзей и&nbsp;расскажет, как стать крутым зооблогером.</p>
                    <div class="triangle blue-triangle-right"></div>
                </div>
                <div class="portrait"><img src="img/vicky_show.jpg" alt="" /></div>
            </div>
            <div class="block_wrapper">
                <div class="colour_descr gray">
                    <h2>Елена</h2>
                    <h2>Сажина</h2>
                    <p>инстаграм-блогер, актриса, певица и&nbsp;самая весёлая мама двойняшек. Городские приключения и&nbsp;полезные советы мамы, собаки и&nbsp;малышей.</p>
                    <div class="triangle gray-triangle-left"></div>
                </div>
                <div class="portrait"><img src="img/sazhina.jpg" alt="" /></div>
            </div>
            <div class="block_wrapper">
                <div class="colour_descr pink">
                    <h2>Алексей</h2>
                    <h2>Сорокин</h2>

                    <p>@ alexey.husky&nbsp;&mdash; популярный зоо блогер, друг и&nbsp;хозяин четырех хаски Юбэк, Бугатти, Фрэя и&nbsp;Даная. Автор историй с&nbsp;хаски в&nbsp;&laquo;Добром блоге Леши&raquo;. Правила хорошего отпуска и&nbsp;добрые истории о&nbsp;путешествиях на&nbsp;встрече с&nbsp;гостями Фестиваля.</p>
                    <div class="triangle pink-triangle-left"></div>
                </div>
                <div class="portrait"><img src="img/sorokin.jpg" alt="" /></div>
            </div>
            <div class="block_wrapper">
                <div class="colour_descr green">
                    <h2>Юлия</h2>
                    <h2>Костюшкина</h2>
                    <p>@karapylka&nbsp;&mdash; актриса, спортсменка, замужем за&nbsp;Стасом Костюшкиным, мама йоркширского терьера Жужи, джек-рассел-терьера и&nbsp;щеночка Оливии. Истории о&nbsp;спорте, моде и&nbsp;лафхаки для активных хозяек.</p>
                    <div class="triangle green-triangle-right"></div>
                </div>
                <div class="portrait"><img src="img/kostyushkina.jpg" alt="" /></div>
            </div>
            <div class="block_wrapper">
                <div class="colour_descr blue">
                    <h2>Анна</h2>
                    <h2>Левадная</h2>
                    <p>@doctor_annamama&nbsp;&mdash; мама двоих детей, педиатр, ведущая рубрики &laquo;Супермама&raquo; на&nbsp;канале &laquo;Пятница&raquo;, автор книг и&nbsp;любимый блогер всех мам. Анна откроет секреты воспитания малышей и&nbsp;пушистых питомцев и&nbsp;расскажет, что делать, если ребенок и&nbsp;питомец живут вместе.</p>
                    <div class="triangle blue-triangle-right"></div>
                </div>
                <div class="portrait"><img src="img/levadnaya.jpg" alt="" /></div>
            </div>
            <div class="block_wrapper">
                <div class="colour_descr violet">
                    <h2>Кирилл</h2>
                    <h2>Скачков</h2>
                    <p>@veterinar.ot.boga&nbsp;&mdash; Ветеринар-эксперт, любящий папа для всех пушистых питомцев. Откроет секреты правильного питания домашних животных и&nbsp;поделится историями правильного ухода во&nbsp;время дачного сезона.</p>
                    <div class="triangle violet-triangle-left"></div>
                </div>
                <div class="portrait"><img src="img/skachkov.jpg" alt="" /></div>
            </div>
            <div class="block_wrapper">
                <div class="colour_descr pink">
                    <h2>Виталий</h2>
                    <h2>Орлов</h2>
                    <p>@shkolaorlova&nbsp;&mdash; ведущий программы &laquo;Кто в&nbsp;доме хозяин&raquo; и&nbsp;организатор Школы Орлова по&nbsp;воспитанию домашних животных. О&nbsp;любви, правильном общении и&nbsp;играх с&nbsp;питомцами расскажет и&nbsp;ответит на&nbsp;вопросы вместе с&nbsp;четвероногими друзьями.</p>
                    <div class="triangle pink-triangle-left"></div>
                </div>
                <div class="portrait"><img src="img/orlov.jpg" alt="" /></div>
            </div>
        </div>
    </div>
    <!-- End Service -->



    <!-- Work -->
    <div id="gala">
        <div class="content-md container">
            <div class="row">

                <h2>ARTIK&ASTI</h2>
                <p>Звездные гости Фестиваля&nbsp;&mdash; группа ARTIK&amp;ASTI и&nbsp;ведущие московские диджеи. Любимые хиты и&nbsp;впервые на&nbsp;открытой площадке новый альбом &laquo;7&raquo;. Музыкальная сцена ждет!</p>
                <!-- Latest Products -->
            </div>
            <!-- Masonry Grid -->
            <div class="masonry-grid row row-space-2">
                <div class="masonry-grid-sizer col-xs-6 col-sm-6 col-md-1"></div>
                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-8 margin-b-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="full_width">
                            <iframe width="758" height="372" src="https://www.youtube.com/embed/St9BslKyfgE" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
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
                            <img class="full-width img-responsive" src="img/a&a04.jpg" alt="Portfolio Image">
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
                            <img class="full-width img-responsive" src="img/a&a01.jpg" alt="Portfolio Image">
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
                            <img class="full-width img-responsive" src="img/a&a02.jpg" alt="Portfolio Image">
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
                            <img class="full-width img-responsive" src="img/a&a03.jpg" alt="Portfolio Image">
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
        </div>
        <!-- Promo Banner -->
        <div class="promo-banner">
            <div class="container-sm content-lg">
                <p class="promo-banner-text">9&nbsp;июня на&nbsp;территории парка Сокольники с&nbsp;самого утра и&nbsp;до&nbsp;позднего вечера гостей ждут более 50&nbsp;развлекательных событий: соревнования, танцы с&nbsp;питомцами, аквагрим, веселые старты, мастер-классы, детские игры с&nbsp;аниматорами и&nbsp;фотозоны. Для самых активных: супер-квест, конкурсы и&nbsp;розыгрыш главного приза&nbsp;&mdash; Путешествия в&nbsp;Париж.</p>
            </div>
        </div>
        <!-- End Promo Banner -->
        <div class="content-md container">
            <div class="row">

                <h2>Добролап</h2>
                <p>Lorem Ipsum Dolor Si Amet...</p>
                <!-- Latest Products -->
            </div>
            <!-- Masonry Grid -->
            <div class="masonry-grid row row-space-2">
                <div class="masonry-grid-sizer col-xs-6 col-sm-6 col-md-1"></div>
                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-8 margin-b-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="full_width">
                            <iframe width="758" height="372" src="https://www.youtube.com/embed/2zi2NoLQ3eo" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>

                        <div class="mobile">
                            <iframe width="345" height="169" src="https://www.youtube.com/embed/2zi2NoLQ3eo" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>

                    </div>
                    <!-- End Work -->
                </div>
                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 margin-b-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="work-overlay">
                            <img class="full-width img-responsive" src="img/dblp01.jpg" alt="Portfolio Image">
                        </div>

                    </div>
                    <!-- End Work -->
                </div>
                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 md-margin-b-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="work-overlay">
                            <img class="full-width img-responsive" src="img/dblp02.jpg" alt="Portfolio Image">
                        </div>

                    </div>
                    <!-- End Work -->
                </div>
                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 md-margin-b-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="work-overlay">
                            <img class="full-width img-responsive" src="img/dblp03.jpg" alt="Portfolio Image">
                        </div>

                    </div>
                    <!-- End Work -->
                </div>
                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="work-overlay">
                            <img class="full-width img-responsive" src="img/dblp04.jpg" alt="Portfolio Image">
                        </div>

                    </div>
                    <!-- End Work -->
                </div>
            </div>
            <!-- End Masonry Grid -->
        </div>
        <!-- Promo Banner -->
        <div class="promo-banner">
            <div class="container-sm content-lg">
                <p class="promo-banner-text">9&nbsp;июня на&nbsp;территории парка Сокольники с&nbsp;самого утра и&nbsp;до&nbsp;позднего вечера гостей ждут более 50&nbsp;развлекательных событий: соревнования, танцы с&nbsp;питомцами, аквагрим, веселые старты, мастер-классы, детские игры с&nbsp;аниматорами и&nbsp;фотозоны. Для самых активных: супер-квест, конкурсы и&nbsp;розыгрыш главного приза&nbsp;&mdash; Путешествия в&nbsp;Париж.</p>
            </div>
        </div>
        <!-- End Promo Banner -->
    </div>
    <div id="interview">
        <div class="content-md container">
            <div class="row">

                <h2>Я пойду!</h2>
                <p>Lorem Ipsum Dolor Si Amet...</p>
                <!-- Latest Products -->
            </div>
            <!-- Masonry Grid -->
            <div class="masonry-grid row row-space-2">
                <div class="masonry-grid-sizer col-xs-6 col-sm-6 col-md-1"></div>

                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 margin-b-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="work-overlay">
                            <img class="full-width img-responsive" src="img/dblp01.jpg" alt="Portfolio Image">
                        </div>

                    </div>
                    <!-- End Work -->
                </div>
                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 margin-b-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="work-overlay">
                            <img class="full-width img-responsive" src="img/dblp01.jpg" alt="Portfolio Image">
                        </div>

                    </div>
                    <!-- End Work -->
                </div>
                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 margin-b-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="work-overlay">
                            <img class="full-width img-responsive" src="img/dblp01.jpg" alt="Portfolio Image">
                        </div>

                    </div>
                    <!-- End Work -->
                </div>
                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 md-margin-b-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="work-overlay">
                            <img class="full-width img-responsive" src="img/dblp02.jpg" alt="Portfolio Image">
                        </div>

                    </div>
                    <!-- End Work -->
                </div>
                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4 md-margin-b-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="work-overlay">
                            <img class="full-width img-responsive" src="img/dblp03.jpg" alt="Portfolio Image">
                        </div>

                    </div>
                    <!-- End Work -->
                </div>
                <div class="masonry-grid-item col-xs-12 col-sm-6 col-md-4">
                    <!-- Work -->
                    <div class="work work-popup-trigger">
                        <div class="work-overlay">
                            <img class="full-width img-responsive" src="img/dblp04.jpg" alt="Portfolio Image">
                        </div>

                    </div>
                    <!-- End Work -->
                </div>
            </div>
            <!-- End Masonry Grid -->
        </div>
    </div>
    <!-- End Work -->



    <!-- Contact -->
    <div id="contact">

    </div>
    <!-- End Contact -->
    <!--========== END PAGE LAYOUT ==========-->