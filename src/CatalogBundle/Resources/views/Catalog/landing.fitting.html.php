<?php

use FourPaws\Decorators\SvgDecorator;
use Symfony\Component\Templating\PhpEngine;

global $APPLICATION;

$APPLICATION->IncludeComponent('fourpaws:catalog.clothing_size_selection', '', [], ['HIDE_ICONS' => 'Y']);

global $USER;

/**
 * @var PhpEngine $view
 */ ?>
<div class="measure_dog__wrapper js-measure-dog <?=($USER->IsAuthorized() ? 'js-measure-dog--with-lk-modal' : '')?>">
    <div class="measure_dog measure_dog--start_size">
        <div class="content_dropdown js-content-dropdown-trigger mobile_mq">
            <div class="content_dropdown__title">
                Как узнать размер собаки
                <div class="content_dropdown__arrow">
                    <?= new SvgDecorator('icon-up-arrow') ?>
                </div>
            </div>
        </div>
        <div class="content_dropdown__content js-content-dropdown-content">
            <div class="measure_dog__title tablet_up_mq">Как узнать размер собаки</div>
            <div class="measure_dog__info">
                <style>
                  .measure_dog__video-iframe {
                    position: relative;
                    padding-bottom: 56.25%;
                    height: 0;
                  }

                  .measure_dog__video-iframe iframe {
                  	position: absolute;
                  	top: 0;
                  	left: 0;
                  	width: 100%;
                  	height: 100%;
                  }
                </style>

                <div class="measure_dog__video">
                    <div class="measure_dog__video-iframe">
                      <iframe src="https://www.youtube.com/embed/BxWcZ6uN7mQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    </div>

                    <?/*<video controls="" poster="/static/build/images/inhtml/preview-size-dog.jpg">
                        <source src="/upload/dobrolap/measure_size.mp4">
                        <source src="/upload/dobrolap/measure_size.ogv" type="video/webm">
                        <source src="/upload/dobrolap/measure_size.webm" type="video/ogg">
                    </video>*/?>
                </div>

                <div class="measure_dog__img"></div>
            </div>

            <form class="measure_dog__form-size js-measure-dog-form">
                <div class="measure_dog__fields">
                    <input class="measure_dog__input" pattern="[0-9]" id="back_size" type="number" min="1" placeholder="Длина спинки (см)" required>
                    <input class="measure_dog__input" pattern="[0-9]" id="chest_size" type="number" min="1" placeholder="Обхват груди (см)" required>
                    <input class="measure_dog__input" pattern="[0-9]" id="neck_size" type="number" min="1" placeholder=" Обхват шеи (см)" required>
                </div>

                <input class="measure_dog__button" type="submit" value="Узнать размер">
            </form>
        </div>
    </div>
    <div class="measure_dog--custom_size">
        <div class="content_dropdown mobile_mq js-content-dropdown-trigger">
            <div class="content_dropdown__title">Как узнать размер собаки
                <div class="content_dropdown__arrow">
                    <?= new SvgDecorator('icon-up-arrow') ?>
                </div>
            </div>
        </div>
        <div class="content_dropdown__content js-content-dropdown-content">
            <div class="measure_dog__content_wrapper">
                <div class="measure_dog__title">У&nbsp;твоей собаки нестандарный размер</div>
                <div class="measure_dog__container">
                    <div class="measure_dog__img"></div>
                    <div class="measure_dog__result">
                        <ul class="measure_dog__steps">
                            <li><span class="num">1</span>
                                закажи бесплатную доставку и&nbsp;примерку<br/>
                                <span class="light">домой или в <a href="/shops/">магазин</a></span>
                            </li>
                            <li><span class="num">2</span>примерь несколько размеров</li>
                            <li><span class="num">3</span>купи только то, что подошло</li>
                        </ul>
                        <div class="measure_dog__button_set">
                            <a class="measure_dog__button measure_dog__button--secondary js-measure-dog-recalculate" href="#">
                                Рассчитать еще раз
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="measure_dog--default_size">
        <div class="content_dropdown mobile_mq js-content-dropdown-trigger">
            <div class="content_dropdown__title">Как узнать размер собаки
                <div class="content_dropdown__arrow">
                    <?= new SvgDecorator('icon-up-arrow') ?>
                </div>
            </div>
        </div>
        <div class="content_dropdown__content js-content-dropdown-content">
            <div class="measure_dog__content_wrapper">
                <div class="measure_dog__title">Mы определили размер</div>
                <div class="measure_dog__container">
                    <div class="measure_dog__img"></div>
                    <div class="measure_dog__result">
                        <div class="measure_dog__size">
                            <div class="measure_dog__size-title">
                                скорее всего у&nbsp;вашей собаки размер
                            </div>
                            <div class="measure_dog__size-number" data-size-dog-measure="true"></div>
                        </div>
                        <div class="measure_dog__paragraph">
                            специально для вашего питомца Мы подобрали одежду по&nbsp;размеру
                        </div>
                        <div class="measure_dog__button_set">
                            <a class="measure_dog__button js-scroll-to-catalog" href="#">Посмотреть</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<? if ($USER->IsAuthorized()): ?>
    <div class="b-popup-wrapper js-popup-wrapper" data-measure-dog-lk-save-popup="wrapper">
        <section class="b-popup-pick-city b-popup-pick-city--authorization" data-popup="authorization" data-measure-dog-lk-save-popup="step1">
            <a class="b-popup-pick-city__close b-popup-pick-city__close--authorization" title="Закрыть" data-measure-dog-lk-save-popup="cancel"></a>

            <div class="b-registration b-registration--popup-authorization">
                <header class="b-registration__header">
                    <div class="b-title b-title--h1 b-title--registration">
                        Результат расчета
                    </div>
                </header>

                <div class="b-registration__content b-registration__content--moiety b-registration__content--step b-cart-combination">
                    <div class="b-registration__text-instruction">
                        Скорее всего, у вашей собаки размер — <span data-measure-dog-lk-save-popup="size-txt"></span>. <br /><br /> Обновить размер вашей собаки в личном кабинете?
                    </div>

                    <div class="b-registration__form js-auth-2way" method="post">
                        <button class="b-button b-button--social b-button--full-width b-cart-combination__btn--left" data-measure-dog-lk-save-popup="go-step2">
                            Да
                        </button>

                        <button class="b-button b-button--social b-button--full-width b-cart-combination__btn--right" data-measure-dog-lk-save-popup="cancel">
                            Нет
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="b-popup-pick-city b-popup-pick-city--authorization" data-popup="authorization" data-measure-dog-lk-save-popup="step2">
            <a class="b-popup-pick-city__close b-popup-pick-city__close--authorization" title="Закрыть" data-measure-dog-lk-save-popup="cancel"></a>

            <div class="b-registration b-registration--popup-authorization">
                <header class="b-registration__header">
                    <div class="b-title b-title--h1 b-title--registration">
                        Результат расчета
                    </div>
                </header>

                <div class="b-registration__content b-registration__content--moiety b-registration__content--step b-cart-combination">
                    <div class="b-registration__form">
                        <label class="b-registration__label b-registration__label--subscribe-delivery" for="type-pet">Выберите питомца</label>

                        <div class="b-select b-select--subscribe-delivery" style="margin-bottom: 30px;">
                            <select class="b-select__block b-select__block--subscribe-delivery" data-measure-dog-lk-save-popup="pet-select"></select>
                        </div>
                    </div>

                    <div class="b-registration__form js-auth-2way">
                        <button
                            class="b-button b-button--social b-button--full-width b-cart-combination__btn--left"
                            data-measure-dog-lk-save-popup="confirm"
                        >
                            Да
                        </button>

                        <button class="b-button b-button--social b-button--full-width b-cart-combination__btn--right" data-measure-dog-lk-save-popup="cancel">
                            Нет
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <form data-url="/ajax/personal/pets/updateSize/" method="post" data-measure-dog-lk-save-popup="form" class="js-ajax-form success-valid">
            <input type="hidden" name="pet_id" data-measure-dog-lk-save-popup="form-size-petid" />

            <input type="hidden" name="back" data-measure-dog-lk-save-popup="form-size-back" />
            <input type="hidden" name="chest" data-measure-dog-lk-save-popup="form-size-chest" />
            <input type="hidden" name="neck" data-measure-dog-lk-save-popup="form-size-neck" />

            <input type="hidden" name="size" data-measure-dog-lk-save-popup="form-size-main" />
        </form>
    </div>

    <script>
        // $('[data-measure-dog-lk-save-popup="wrapper"]').addClass('active');
        // $('[data-measure-dog-lk-save-popup="step1"]').fadeIn();
    </script>
    
<? endif ?>

<? if(!$hide_info) { ?>
<div class="free_fitting__wrapper">
    <div class="free_fitting">
        <div class="content_dropdown mobile_mq js-content-dropdown-trigger">
            <div class="content_dropdown__title">Бесплатная примерка
                <div class="content_dropdown__arrow">
                    <?= new SvgDecorator('icon-up-arrow') ?>
                </div>
            </div>
        </div>
        <div class="content_dropdown__content js-content-dropdown-content">
            <div class="free_fitting__title_wrapper">
                <div class="free_fitting__title tablet_up_mq">Бесплатная примерка</div>
                <div class="sub_navigation__wrapper">
                    <div class="sub_navigation js-sub-navigation">
                        <div class="sub_navigation__item--active js-sub-navigation-item" data-view="shop">В
                            магазине
                        </div>
                        <div class="sub_navigation__item js-sub-navigation-item" data-view="home">Дома</div>
                    </div>
                </div>
            </div>
            <div class="free_fitting_step__wrapper">
                <div class="free_fitting_step js-free-fitting-view-shop">
                    <div class="free_fitting_step__item">
                        <div class="free_fitting_step__icon">
                            <?= new SvgDecorator('free_fitting__location') ?>
                        </div>
                        <div class="free_fitting_step__title">1. Проверьте наличие<br>товара в магазинах
                        </div>
                        <div class="free_fitting_step__paragraph">Наличие в магазинах вы можете посмотреть в карточке
                            товара
                        </div>
                        <div class="free_fitting_step__paragraph">Так же вы можете посмотреть<br><a
                                    href="/shops/">список всех магазинов</a> в вашем городе
                        </div>
                    </div>
                    <div class="free_fitting_step__item">
                        <div class="free_fitting_step__icon">
                            <?= new SvgDecorator('free_fitting__shop') ?>
                        </div>
                        <div class="free_fitting_step__title">2. Оформите<br>самовывоз заказа</div>
                        <div class="free_fitting_step__paragraph">Оформите самовывоз в любой удобный для вас
                            магазин, в котором выбранный товар есть в наличии
                        </div>
                    </div>
                    <div class="free_fitting_step__item">
                        <div class="free_fitting_step__icon">
                            <?= new SvgDecorator('free_fitting__dog') ?>
                        </div>
                        <div class="free_fitting_step__title">3. Приезжайте с питомцем<br>и примерьте на
                            него одежду
                        </div>
                        <div class="free_fitting_step__paragraph">Вы можете приехать в выбранный магазин в
                            любое время его работы и примерить одежду на вашего питомца
                        </div>
                        <div class="free_fitting_step__paragraph">Услуга бесплатная</div>
                    </div>
                    <div class="free_fitting_step__item">
                        <div class="free_fitting_step__icon">
                            <?= new SvgDecorator('free_fitting__card') ?>
                        </div>
                        <div class="free_fitting_step__title">4. Купите только то,<br>что подошло</div>
                        <div class="free_fitting_step__paragraph">Товары, которые не подошли по размеру или
                            фасону, вы можете вернуть продавцу
                        </div>
                    </div>
                </div>
                <div class="free_fitting_step--home js-free-fitting-view-home">
                    <div class="free_fitting_step__item">
                        <div class="free_fitting_step__icon">
                            <?= new SvgDecorator('free_fitting__basket') ?>
                        </div>
                        <div class="free_fitting_step__title">1. Оформите заказ</div>
                        <div class="free_fitting_step__paragraph">Вы можете заказать несколько разных
                            моделей или одну модель в разных размерах
                        </div>
                    </div>
                    <div class="free_fitting_step__item">
                        <div class="free_fitting_step__icon">
                            <?= new SvgDecorator('free_fitting__delivery') ?>
                        </div>
                        <div class="free_fitting_step__title">2. Закажите<br>доставку курьером</div>
                        <div class="free_fitting_step__paragraph">Стоимость доставки — 200 ₽<br>Бесплатно
                            при заказе от 2 000 ₽
                        </div>
                    </div>
                    <div class="free_fitting_step__item">
                        <div class="free_fitting_step__icon">
                            <?= new SvgDecorator('free_fitting__dog') ?>
                        </div>
                        <div class="free_fitting_step__title">3. Примерьте одежду <br>на вашего питомца
                        </div>
                        <div class="free_fitting_step__paragraph">Время примерки — 15 минут<br>Услуга
                            бесплатная
                        </div>
                    </div>
                    <div class="free_fitting_step__item">
                        <div class="free_fitting_step__icon">
                            <?= new SvgDecorator('free_fitting__card') ?>
                        </div>
                        <div class="free_fitting_step__title">4. Купите только то,<br>что подошло</div>
                        <div class="free_fitting_step__paragraph">Товары, которые не подошли по размеру или
                            фасону, вы можете вернуть курьеру
                        </div>
                    </div>
                </div>
            </div>
            <input class="free_fitting__button js-scroll-to-catalog" type="submit" value="Перейти в каталог">
        </div>
    </div>
</div>
<? } ?>
