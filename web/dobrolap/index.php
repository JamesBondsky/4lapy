<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Добролап");
?>
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar ftco-navbar-light site-navbar-target" id="ftco-navbar">
        <div class="container">
            <button class="navbar-toggler js-fh5co-nav-toggle fh5co-nav-toggle" type="button" data-toggle="collapse"
                    data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="oi oi-menu"></span>
            </button>

            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav nav ml-auto">
                    <li class="nav-item"><a href="#needs" class="nav-link"><span>Помогаем вместе</span></a></li>
                    <li class="nav-item"><a href="#shelter" class="nav-link"><span>Приюты-участники</span></a></li>
                    <li class="nav-item"><a href="#how_get" class="nav-link"><span>Принять участие</span></a></li>
                    <li class="nav-item"><a href="#thanks" class="nav-link"><span>Добрые сюрпризы</span></a></li>
                    <li class="nav-item"><a href="#little" class="nav-link"><span>Маленькие друзья</span></a></li>
                    <li class="nav-item"><a href="#challenge" class="nav-link"><span>Челлендж</span></a></li>
                    <li class="nav-item"><a href="#photos" class="nav-link"><span>Фотоотчеты</span></a></li>
                    <li class="nav-item"><a href="#raise" class="nav-link"><span>Едем помогать</span></a></li>
                </ul>
            </div>
        </div>
    </nav>


    <section class="ftco-about img ftco-section ftco-no-pb" id="about-section">
        <div class="container">
            <div class="row d-flex">
                <div class="col-md-6 col-lg-5 d-flex">
                    <div class="img-about img d-flex align-items-stretch">
                        <div class="overlay"></div>
                        <div class="img d-flex align-self-stretch align-items-center"
                             style="background-image:url(dobrolap/images/key_visual.png); background-size: contain; background-position: center bottom;">
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-7 pb-5">
                    <div class="row justify-content-start pb-3">
                        <div class="col-md-12 heading-section ftco-animate">
                            <span class="subheading">V ЕЖЕГОДНАЯ БЛАГОТВОРИТЕЛЬНАЯ АКЦИЯ «ДОБРОЛАП»</span>
                            <h1 class="mb-4 mt-3">ТВОРИМ ДОБРО ВМЕСТЕ</span></h1>
                            <p class="but_scroll">
                                <a href="#how_get" class="btn btn-primary py-3 px-4" target="_blank">КАК ПОМОЧЬ</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<? $APPLICATION->IncludeComponent('articul:dobrolap.form', '', []); ?>

    <section class="ftco-section" id="needs">
        <div class="container">
            <div class="row justify-content-center pb-5">
                <div class="col-md-12 heading-section text-center ftco-animate">
                    <h2 class="">ПОМОГАЕМ ВМЕСТЕ</h2>
                    <h5 class="mb-4">Компания &laquo;Четыре Лапы&raquo; помогает временно бездомным животным.</h5>
                    <hr/>
                    <div class="harvest_icon read_more_btn">
                        <a href="javascript:void(0);" class="btn btn-primary-filled py-3 px-4">УЗНАТЬ ПРО ДОБРОЛАП</a>
                    </div>
                    <div class="needs_note">
                        <h5 class="mb-4">1 августа стартовала V&nbsp;ежегодная благотворительная акция &laquo;Добролап&raquo;
                            &laquo;ТВОРИМ ДОБРО ВМЕСТЕ!&raquo;&nbsp;&mdash; ПОД ТАКИМ ЛОЗУНГОМ
                            в&nbsp;зоомагазинах &laquo;Четыре&nbsp;Лапы&raquo; проводится акция помощи приютам для
                            бездомных животных &laquo;Добролап&raquo;.<br/>
                            Благотворительная инициатива &laquo;Добролап&raquo; уже в&nbsp;пятый раз проходит в&nbsp;сети
                            &laquo;Четыре&nbsp;Лапы&raquo;. Совместно с&nbsp;44 благотворительными
                            организациями в&nbsp;этом году &laquo;Четыре&nbsp;лапы&raquo; объединяет всех, кто не&nbsp;равнодушен
                            к&nbsp;питомцам без семьи, с&nbsp;целью помочь животным
                            найти родителей и&nbsp;поддержать временно бездомных друзей.</h5>
                        <h5 class="mb-4">ПРИНЯТЬ УЧАСТИЕ МОЖНО НА&nbsp;САЙТЕ &laquo;ЧЕТЫРЕ&nbsp;ЛАПЫ&raquo;, НАЖАВ
                            КНОПКУ &laquo;ХОЧУ ПОМОЧЬ&raquo; ИЛИ В&nbsp;ЛЮБОМ ЗООМАГАЗИНЕ &laquo;ЧЕТЫРЕ&nbsp;ЛАПЫ&raquo;.<br/>
                            ПРИСОЕДИНЯЙТЕСЬ К&nbsp;КОМАНДЕ &laquo;ДОБРОЛАП&raquo;, УЗНАВАЙТЕ ПОДРОБНОСТИ НА&nbsp;САЙТЕ,
                            ПРИНИМАЙТЕ УЧАСТИЕ В&nbsp;ЧЕЛЛЕНДЖЕ #КОМАНДАДОБРОЛАП И&nbsp;СЛЕДИТЕ
                            ЗА&nbsp;НОВОСТЯМИ В&nbsp;СОЦИАЛЬНЫХ СЕТЯХ.</h5>
                    </div>
                </div>
            </div>
            <div class="row">
                <? $APPLICATION->IncludeComponent('articul:dobrolap.necessary', '', []); ?>
                <div class="harvest_icon">
                    <a href="#how_get" class="btn btn-primary py-3 px-4" target="_blank">КАК ПОМОЧЬ</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section ftco-counter img" id="helps">
        <div class="container">

            <div class="col-md-12 heading-section text-center ftco-animate">
                <h2 class="">Мы помогаем</h2>
                <hr/>
            </div>
            <div class="row d-md-flex align-items-center">
                <div class="col-md d-flex justify-content-center counter-wrap ftco-animate">
                    <div class="block-18">
                        <div class="text">
                            <strong class="number" data-number="18538">0</strong>
                            <span>Питомцам</span>
                        </div>
                    </div>
                </div>
                <div class="col-md d-flex justify-content-center counter-wrap ftco-animate">
                    <div class="block-18">
                        <div class="text">
                            <span class="free_place">из</span>
                            <strong class="number" data-number="44">0</strong>
                            <span>приютов</span>
                        </div>
                    </div>
                </div>
                <div class="col-md d-flex justify-content-center counter-wrap ftco-animate">
                    <div class="block-18">
                        <div class="text">
                            <span class="free_place">в</span>
                            <strong class="number" data-number="20">0</strong>
                            <span>городах</span>
                        </div>
                    </div>
                </div>
                <div class="cat_dog">
                    <img src="/dobrolap/images/help_bg_2.png" alt=""/>
                </div>
                <div class="harvest_icon">
                    <a href="#how_get" class="btn btn-primary py-3 px-4" target="_blank">КАК ПОМОЧЬ</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section" id="shelter">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12 heading-section text-center ftco-animate">
                    <h2 class="">Приюты участники</h2>
                    <hr/>
                </div>
            </div>
            <div class="row">
                <? $APPLICATION->IncludeComponent('articul:dobrolap.shelters', '', []); ?>
            </div>
        </div>
        <div class="read_more">
            <div class="btn btn-primary py-3 px-4 see_more" data-read-more-shelter="true">Показать больше ▼</div>
        </div>
    </section>

    <section class="ftco-section" id="how_get">
        <div class="container">
            <div class="row justify-content-center pb-5">
                <div class="col-md-12 heading-section text-center ftco-animate">
                    <h2 class="">Принять участие легко</h2>
                    <hr/>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h4 class="subheader">в магазине «Четыре лапы»</h4>

                    <div class="how-get__shop">
                        <div class="col-md-12 animate-box">
                            <div class="rule_wrap">

                                <div class="rule_number">
                                    <img src="/dobrolap/images/01.png" alt="01"/>
                                </div>
                                <div class="rule_icon">
                                    <img src="/dobrolap/images/icon_1.png" alt="купи подарок"/>
                                </div>
                                <div class="rule_note">
                                    <span><strong>купи подарок</strong><br/>для питомцев из приюта</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 animate-box">
                            <div class="rule_wrap">

                                <div class="rule_number">
                                    <img src="/dobrolap/images/02.png" alt="02"/>
                                </div>
                                <div class="rule_icon">
                                    <img src="/dobrolap/images/icon_2.png" alt="положи в корзину"/>
                                </div>
                                <div class="rule_note">
                                    <span><strong>положи его</strong><br/>в корзину #добролап</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 animate-box">
                            <div class="rule_wrap">

                                <div class="rule_number">
                                    <img src="/dobrolap/images/03.png" alt="03"/>
                                </div>
                                <div class="rule_icon">
                                    <img src="/dobrolap/images/icon_3.png" alt="получи сюрприз"/>
                                </div>
                                <div class="rule_note">
                                    <span><strong>ПОЛУЧИ СЮРПРИЗ</strong><br/>И МАГНИТ #ДОБРОЛАП НА КАССЕ</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 animate-box">
                            <div class="rule_wrap">

                                <div class="rule_number">
                                    <img src="/dobrolap/images/04.png" alt="04"/>
                                </div>
                                <div class="rule_icon">
                                    <img src="/dobrolap/images/icon_4.png" alt="следи за итогами"/>
                                </div>
                                <div class="rule_note">
                                    <span><strong>СЛЕДИ</strong><br/>ЗА ИТОГАМИ И ОТЧЕТАМИ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 white-col">
                    <h4 class="subheader">на сайте&nbsp;&nbsp;<a href="https://4lapy.ru/" target="_blank"><img
                                    src="/dobrolap/images/4lapy.png" alt=""/></a></h4>

                    <div class="how-get__site">
                        <div class="col-md-12 animate-box">
                            <div class="rule_wrap">

                                <div class="rule_number">
                                    <img src="/dobrolap/images/01.png" alt="01"/>
                                </div>
                                <div class="rule_icon">
                                    <img src="/dobrolap/images/icon_5.png" alt="выбери товары"/>
                                </div>
                                <div class="rule_note">
                                    <span><strong>ВЫБЕРИ ТОВАРЫ</strong><br/>И ПОЛОЖИ В КОРЗИНУ</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 animate-box">
                            <div class="rule_wrap">

                                <div class="rule_number">
                                    <img src="/dobrolap/images/02.png" alt="02"/>
                                </div>
                                <div class="rule_icon">
                                    <img src="/dobrolap/images/icon_6.png" alt="ВЫБЕРИ ПРИЮТ"/>
                                </div>
                                <div class="rule_note">
                                    <span><strong>ВЫБЕРИ ПРИЮТ</strong><br/>ПРИ ОФОРМЛЕНИИ ЗАКАЗА</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 animate-box">
                            <div class="rule_wrap">

                                <div class="rule_number">
                                    <img src="/dobrolap/images/03.png" alt="03"/>
                                </div>
                                <div class="rule_icon">
                                    <img src="/dobrolap/images/icon_3.png" alt="получи сюрприз"/>
                                </div>
                                <div class="rule_note">
                                    <span>ОПЛАТИ ЗАКАЗ,<br/><strong>ПОЛУЧИ СЮРПРИЗ</strong><br/>И МАГНИТ #ДОБРОЛАП</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 animate-box">
                            <div class="rule_wrap">

                                <div class="rule_number">
                                    <img src="/dobrolap/images/04.png" alt="04"/>
                                </div>
                                <div class="rule_icon">
                                    <img src="/dobrolap/images/icon_4.png" alt="следи за итогами"/>
                                </div>
                                <div class="rule_note">
                                    <span><strong>СЛЕДИ</strong><br/>ЗА ИТОГАМИ И ОТЧЕТАМИ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="harvest_icon">
                <a href="https://4lapy.ru/shares/blagotvoritelnaya-aktsiya-dobrolap-dlya-zhivotnykh-ikh-priyutov2.html"
                   class="btn btn-primary-filled py-3 px-4" target="blank">ХОЧУ ПОМОЧЬ</a>
            </div>
        </div>
    </section>

    <section class="ftco-section" id="thanks">
        <div class="container">
            <div class="row">

                <div class="col-md-6 col-md-6-mobile">
                    <div class="row justify-content-center">
                        <div class="col-md-12 heading-section text-center ftco-animate">
                            <h2 class="">ДОБРЫЕ<br/>СЮРПРИЗЫ</h2>
                            <hr/>
                            <h5 class="mb-4">Каждому человеку под силу совершить доброе дело и сделать счастливым
                                маленького пушистого друга. Тем более, что для этого надо совсем немного.</h5>
                            <h5 class="mb-4">На память о добром поступке каждый участник «Добролап» получит памятный
                                магнит и ОДИН ИЗ ДОБРЫХ СЮРПРИЗОВ: СКИДКУ НА АКСЕССУАРЫ ИЛИ ЛАКОМСТВА, БОНУСЫ НА ПОКУПКУ
                                ПРАВИЛЬНОГО КОРМА ИЛИ ОДИН ИЗ 2000 ФАН-БОНУСОВ ДЛЯ УЧАСТИЯ В РОЗЫГРЫШЕ ПРИЗОВ. Весь
                                август в магазинах «Четыре лапы» и на сайте «4lapy.ru».</h5>
                            <div class="thanks__btns">
                                <a href="javascript:void(0);"
                                   class="btn btn-primary-filled py-3 px-4 <?= ($USER->IsAuthorized()) ? 'js-show-fan-form' : 'js-open-popup' ?>"
                                   data-popup-id="authorization">ЗАРЕГИСТРИРОВАТЬ ФАН</a>
                                <a href="javascript:void(0);" class="btn btn-primary py-3 px-4 js-open-popup"
                                   data-popup-id="dobrolap_more_info_popup">ПОДРОБНЕЕ</a>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-md-3">
                    <div class="col-md-12 animate-box">
                        <div class="rule_wrap">
                            <div class="rule_icon">
                                <img src="/dobrolap/images/icon_7.png" alt=""/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 animate-box">
                        <div class="rule_wrap">
                            <div class="rule_icon">
                                <img src="/dobrolap/images/icon_9.png" alt=""/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="col-md-12 animate-box">
                        <div class="rule_wrap">
                            <div class="rule_icon">
                                <img src="/dobrolap/images/icon_8.png" alt=""/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 animate-box">
                        <div class="rule_wrap">
                            <div class="rule_icon">
                                <img src="/dobrolap/images/icon_10.png" alt=""/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-md-6-desktop">
                    <div class="row justify-content-center">
                        <div class="col-md-12 heading-section text-center ftco-animate">
                            <h2 class="">ДОБРЫЕ<br/>СЮРПРИЗЫ</h2>
                            <hr/>
                            <h5 class="mb-4">Каждому человеку под силу совершить доброе дело и сделать счастливым
                                маленького пушистого друга. Тем более, что для этого надо совсем немного.</h5>
                            <h5 class="mb-4">На память о добром поступке каждый участник «Добролап» получит памятный
                                магнит и ОДИН ИЗ ДОБРЫХ СЮРПРИЗОВ: СКИДКУ НА АКСЕССУАРЫ ИЛИ ЛАКОМСТВА, БОНУСЫ НА ПОКУПКУ
                                ПРАВИЛЬНОГО КОРМА ИЛИ ОДИН ИЗ 2000 ФАН-БОНУСОВ ДЛЯ УЧАСТИЯ В РОЗЫГРЫШЕ ПРИЗОВ. Весь
                                август в магазинах «Четыре лапы» и на сайте «4lapy.ru».</h5>
                            <div class="thanks__btns">
                                <a href="javascript:void(0);"
                                   class="btn btn-primary-filled py-3 px-4 <?= ($USER->IsAuthorized()) ? 'js-show-fan-form' : 'js-open-popup' ?>"
                                   data-popup-id="authorization">ЗАРЕГИСТРИРОВАТЬ ФАН-БОНУС</a>
                                <a href="javascript:void(0);" class="btn btn-primary py-3 px-4 js-open-popup"
                                   data-popup-id="dobrolap_more_info_popup">ПОДРОБНЕЕ</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section ftco-no-pb ftco-no-pt" id="little">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="row justify-content-center little__description">
                        <div class="col-md-12 heading-section text-center ftco-animate">
                            <h2 class="">большая помощь<br/>для маленького друга</h2>
                            <hr/>
                            <h5 class="mb-4">Для самых маленьких «Четыре лапы» подготовили удобные подарочные коробочки,
                                в которые малыши могут положить подарок для питомца из приюта прямо в магазине и
                                подписать адресата, чтобы потом увидеть на сайте счастливые мордочки питомцев в рубрике
                                «Фотоотчет».</h5>
                            <h5 class="mb-4">Обязательно присоединяйтесь вместе с детьми: помощь маленького друга - это
                                большое доброе сердце и счастье научиться делать чудеса своими руками</h5>
                        </div>
                        <div class="harvest_icon">
                            <a href="#how_get" class="btn btn-primary py-3 px-4" target="_blank">КАК ПОМОЧЬ</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="col-md-12 animate-box">
                        <div class="rule_wrap">
                            <div class="rule_icon">
                                <img src="/dobrolap/images/little_boy.jpg" alt=""/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section" id="challenge">
        <div class="col-md-12">
            <div class="row justify-content-center">
                <div class="col-md-12 heading-section text-center ftco-animate">
                    <h2 class="">Челлендж #командадобролап</h2>
                    <hr/>
                    <h5 class="mb-4">Стань частью команды – включайся в челлендж : расскажи своим подписчикам о том, как
                        помочь питомцам, у которых пока нет дома. Запиши видео или прикрепи фотографию. Обязательно
                        поставь хештег #командадобролап. Делись и собирай «лайки»: авторы 10 самых популярных историй
                        смогут превратить свои «лайки» в бонусные баллы!</h5>
                    <h5 class="mb-4">Присоединяйтесь к команде и следите за новостями в социальных сетях.</h5>
                </div>
            </div>
        </div>
        <!--<div class="home-slider  owl-carousel">
          <div class="slider-item ">
              <div class="overlay"></div>
            <div class="container">
              <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end" data-scrollax-parent="true">
                      <video controls poster="/dobrolap/images/29184619-preview.jpg">
                      <source src="/dobrolap/video/29184619-preview.mp4" type="video/mp4">
                    </video>
                </div>
            </div>
          </div>
          <div class="slider-item ">
              <div class="overlay"></div>
            <div class="container">
              <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end" data-scrollax-parent="true">
                      <video controls poster="/dobrolap/images/1014142868-preview.jpg">
                      <source src="/dobrolap/video/1014142868-preview.mp4" type="video/mp4">
                    </video>
                </div>
            </div>
          </div>
          <div class="slider-item ">
              <div class="overlay"></div>
            <div class="container">
              <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end" data-scrollax-parent="true">
                      <video controls poster="/dobrolap/images/1012398863-preview.jpg">
                      <source src="/dobrolap/video/1012398863-preview.mp4" type="video/mp4">
                    </video>

                    <span class='ion-ios-arrow-left'></span>
                </div>
            </div>
          </div>
        </div>-->

        <img src="/dobrolap/images/story.png?v=1" style="margin: 20px auto; display: block;" alt=""/>
    </section>

    <section class="ftco-section" id="photos">
        <div class="col-md-12">
            <div class="row justify-content-center">
                <div class="col-md-12 heading-section text-center ftco-animate">
                    <h2 class="">фотоотчеты</h2>
                    <hr/>
                </div>
            </div>
        </div>
        <div class="b-container">
            <section class="b-common-section">
                <div class="b-common-section__title-box b-common-section__title-box--sale">
                    <h2 class="b-title b-title--sale">&nbsp;</h2>
                </div>
                <div class="b-common-section__content b-common-section__content--sale b-common-section__content--main-sale js-popular-product">
                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/2608/DSCN4338.JPG" data-lightbox="photos"
                           class="photos__link"
                           data-title="Корзинка «Добролап» никогда не бывает пустой">
                            <img src="/dobrolap/images/report/2608/DSCN4338.JPG" class="photos__img" />
                        </a>
                        <div class="carousel-note">
                            <p class="mb-4">
                                Корзинка «Добролап» никогда не бывает пустой
                            </p>
                        </div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/2608/IMG_7423-13-08-19-10-34.jpeg" data-lightbox="photos"
                           class="photos__link"
                           data-title="Каждый день питомцы из приютов получают долгожданные подарки">
                            <img src="/dobrolap/images/report/2608/IMG_7423-13-08-19-10-34.jpeg" class="photos__img" />
                        </a>
                        <div class="carousel-note">
                            <p class="mb-4">
                                Каждый день питомцы из приютов получают долгожданные подарки
                            </p>
                        </div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/2608/lyEQwzEt0aY.jpg" data-lightbox="photos"
                           class="photos__link"
                           data-title="Вкусные лакомства теперь в обязательном рационе питомцев">
                            <img src="/dobrolap/images/report/2608/lyEQwzEt0aY.jpg" class="photos__img" />
                        </a>
                        <div class="carousel-note">
                            <p class="mb-4">
                                Вкусные лакомства теперь в обязательном рационе питомцев
                            </p>
                        </div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/2608/IMG_9399.JPG" data-lightbox="photos"
                           class="photos__link"
                           data-title="Команда волонтеров растет с каждым днем">
                            <img src="/dobrolap/images/report/2608/IMG_9399.JPG" class="photos__img" />
                        </a>
                        <div class="carousel-note">
                            <p class="mb-4">
                                Команда волонтеров растет с каждым днем
                            </p>
                        </div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/2608/Skobelevskaja3.jpg" data-lightbox="photos"
                           class="photos__link"
                           data-title="Больше всего маленьким питомцам нужны ветеринарные препараты и пеленки. Теперь все будет в порядке!">
                            <img src="/dobrolap/images/report/2608/Skobelevskaja3.jpg" class="photos__img" />
                        </a>
                        <div class="carousel-note">
                            <p class="mb-4">
                                Больше всего маленьким питомцам нужны ветеринарные препараты и пеленки. Теперь все будет в порядке!
                            </p>
                        </div>
                    </div>

                    <?/*<div class="b-common-item">
                        <a href="/dobrolap/images/report/2608/" data-lightbox="photos"
                           class="photos__link"
                           data-title="Целый набор самого необходимого собрали маленькие помощники всего за один день">
                            <img src="/dobrolap/images/report/2608/" class="photos__img" />
                        </a>
                        <div class="carousel-note">
                            <p class="mb-4">
                                Целый набор самого необходимого собрали маленькие помощники всего за один день
                            </p>
                        </div>
                    </div>*/?>

                    <?/*<div class="b-common-item">
                        <a href="/dobrolap/images/report/2608/" data-lightbox="photos"
                           class="photos__link"
                           data-title="Во время визита в  учебно- кинологический центр «Собаки-помощники инвалидов» волонтеры «Четыре Лапы» привезли с собой подарки от каждого">
                            <img src="/dobrolap/images/report/2608/" class="photos__img" />
                        </a>
                        <div class="carousel-note">
                            <p class="mb-4">
                                Во время визита в  учебно- кинологический центр «Собаки-помощники инвалидов» волонтеры «Четыре Лапы» привезли с собой подарки от каждого
                            </p>
                        </div>
                    </div>*/?>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/2608/IMG_9332.JPG" data-lightbox="photos"
                           class="photos__link"
                           data-title="В Центре «Собаки-поводыри» обучаются лабрадоры и ретриверы. Эти породы наиболее приспособлены для ответственной работы собаки-поводыря.">
                            <img src="/dobrolap/images/report/2608/IMG_9332.JPG" class="photos__img" />
                        </a>
                        <div class="carousel-note">
                            <p class="mb-4">
                                В Центре «Собаки-поводыри» обучаются лабрадоры и ретриверы. Эти породы наиболее приспособлены для ответственной работы собаки-поводыря.
                            </p>
                        </div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/02.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/02.jpg')"
                           data-title="Команда «Добролап» помогла найти Лайме и еще более 100 питомцам новую семью">
                        </a>
                        <div class="carousel-note"><p class="mb-4">Команда «Добролап» помогла<br/>найти Лайме и еще
                                более 100 питомцам<br/>новую семью</p></div>
                    </div>
                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/03.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/03.jpg')"
                           data-title="Самые вкусные подарки привозят друзья «Добролап»">
                        </a>
                        <div class="carousel-note"><p class="mb-4">Самые вкусные подарки<br/>привозят друзья<br/>«Добролап»
                            </p></div>
                    </div>
                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/04.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/04.jpg')"
                           data-title="Большая дружба начинается с малого: более 500 ребят стали участниками акции в 2018 году">
                        </a>
                        <div class="carousel-note"><p class="mb-4">Большая дружба начинается<br/>с малого: более 500
                                ребят стали<br/>участниками акции в 2018 году</p></div>
                    </div>
                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/05.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/05.jpg')"
                           data-title="Большая радость самому приехать к питомцам, которые очень тебя ждут">
                        </a>
                        <div class="carousel-note"><p class="mb-4">Большая радость самому<br/>приехать к питомцам,
                                которые<br/>очень тебя ждут</p></div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/06.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/06.jpg')"
                           data-title="Каждый день маленьких помощников ждут добрые дела">
                        </a>
                        <div class="carousel-note"><p class="mb-4">Каждый день маленьких помощников ждут добрые дела</p></div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/07.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/07.jpg')"
                           data-title="Вместе с мамой ребята собрали большую посылку с лакомствами для друзей">
                        </a>
                        <div class="carousel-note"><p class="mb-4">Вместе с мамой ребята собрали большую посылку с лакомствами для друзей</p></div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/08.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/08.jpg')"
                           data-title="Катя и Даша узнали про Добролап в Инстаграмм и привели родителей">
                        </a>
                        <div class="carousel-note"><p class="mb-4">Катя и Даша узнали про Добролап в Инстаграмм и привели родителей</p></div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/09.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/09.jpg')"
                           data-title="Много корма не бывает! ">
                        </a>
                        <div class="carousel-note"><p class="mb-4">Много корма не бывает!</p></div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/10.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/10.jpg')"
                           data-title="Маша с мамой выбирают по списку, чтобы не упустить ничего важного">
                        </a>
                        <div class="carousel-note"><p class="mb-4">Маша с мамой выбирают по списку, чтобы не упустить ничего важного</p></div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/11.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/11.jpg')"
                           data-title="В приют отправились три коробочки с вкуснятиной, запас корма на месяц и уютные лежаки">
                        </a>
                        <div class="carousel-note"><p class="mb-4">В приют отправились три коробочки с вкуснятиной, запас корма на месяц и уютные лежаки</p></div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/12.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/12.jpg')"
                           data-title="У Маргариты это уже третий магнитик Добролап!">
                        </a>
                        <div class="carousel-note"><p class="mb-4">У Маргариты это уже третий магнитик Добролап!</p></div>
                    </div>

                    <div class="b-common-item">
                        <a href="/dobrolap/images/report/13.jpg" data-lightbox="photos"
                           class="photos__link"
                           style="background-image: url('/dobrolap/images/report/13.jpg')"
                           data-title="Долгожданная помощь отправляется в приюты каждую неделю">
                        </a>
                        <div class="carousel-note"><p class="mb-4">Долгожданная помощь отправляется в приюты каждую неделю</p></div>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <section class="ftco-section" id="together-more">
        <div class="col-md-12">
            <div class="row justify-content-center">
                <div class="col-md-12 heading-section text-center ftco-animate">
                    <h2 class="">Вместе мы сможем больше</h2>
                    <hr/>
                </div>
            </div>
        </div>
        <div class="home-slider home-slider_images owl-carousel">
            <div class="slider-item ">
                <div class="overlay"></div>
                <div class="container">
                    <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end"
                         data-scrollax-parent="true">
                        <div class="home-slider__img">
                            <img src="/dobrolap/images/together-more/banner-together-more1.jpg?v=1" alt=""/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="slider-item ">
                <div class="overlay"></div>
                <div class="container">
                    <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end"
                         data-scrollax-parent="true">
                        <div class="home-slider__img">
                            <img src="/dobrolap/images/together-more/banner-together-more2.jpg?v=1" alt=""/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="slider-item ">
                <div class="overlay"></div>
                <div class="container">
                    <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end"
                         data-scrollax-parent="true">
                        <div class="home-slider__img">
                            <img src="/dobrolap/images/together-more/banner-together-more3.jpg?v=1" alt=""/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="slider-item ">
                <div class="overlay"></div>
                <div class="container">
                    <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end"
                         data-scrollax-parent="true">
                        <div class="home-slider__img">
                            <img src="/dobrolap/images/together-more/banner-together-more4.jpg?v=1" alt=""/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="slider-item ">
                <div class="overlay"></div>
                <div class="container">
                    <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end"
                         data-scrollax-parent="true">
                        <div class="home-slider__img">
                            <img src="/dobrolap/images/together-more/banner-together-more5.jpg?v=1" alt=""/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="slider-item ">
                <div class="overlay"></div>
                <div class="container">
                    <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end"
                         data-scrollax-parent="true">
                        <div class="home-slider__img">
                            <img src="/dobrolap/images/together-more/banner-together-more6.jpg?v=1" alt=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section" id="raise">
        <div class="col-md-12">
            <div class="row justify-content-center">
                <div class="col-md-12 heading-section text-center ftco-animate">
                    <h2 class="">едем помогать</h2>
                    <hr/>
                    <h5 class="mb-4">КАЖДУЮ НЕДЕЛЮ МЫ ОТПРАВЛЯЕМСЯ В ГОСТИ К НАШИМ ЧЕТВЕРОЛАПЫМ ДРУЗЬЯМ, ЧТОБЫ отвезти
                        нужные и долгожданные подарки четверолапым друзьям из приютов.</h5>
                    <h5 class="mb-4">Примите участие в челлендже, расскажите друзьям и присоединяйтесь к нам. Вместе мы
                        сможем больше!</h5>
                </div>
            </div>
        </div>
        <div class="home-slider  owl-carousel">
            <div class="slider-item ">
                <div class="container">
                    <div class="row d-md-flex no-gutters slider-text align-items-end justify-content-end"
                         data-scrollax-parent="true">
                        <video controls poster="/dobrolap/images/raise-preview2.jpg">
                            <source src="/upload/dobrolap/4lapi_priut_14_08_2.mp4">
                            <source src="/upload/dobrolap/4lapi_priut_14_08_2.ogv" type="video/webm">
                            <source src="/upload/dobrolap/4lapi_priut_14_08_2.webm" type="video/ogg">
                        </video>
                    </div>
                </div>
            </div>
        </div>
    </section>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
