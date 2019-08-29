<?php

use FourPaws\Decorators\SvgDecorator;
use Symfony\Component\Templating\PhpEngine;

global $APPLICATION;

$APPLICATION->IncludeComponent('fourpaws:catalog.clothing_size_selection', '', [], ['HIDE_ICONS' => 'Y']);

/**
 * @var PhpEngine $view
 */ ?>
<div class="measure_dog__wrapper js-measure-dog">
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
                <div class="measure_dog__video">
                    <video controls="" poster="/static/build/images/inhtml/preview-size-dog.jpg">
                        <source src="/upload/dobrolap/measure_size.mp4">
                        <source src="/upload/dobrolap/measure_size.ogv" type="video/webm">
                        <source src="/upload/dobrolap/measure_size.webm" type="video/ogg">
                    </video>
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