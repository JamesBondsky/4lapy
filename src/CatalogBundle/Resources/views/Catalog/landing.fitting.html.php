<?php

use FourPaws\Decorators\SvgDecorator;
use Symfony\Component\Templating\PhpEngine;

global $APPLICATION;

$APPLICATION->IncludeComponent('fourpaws:catalog.clothing_size_selection', '', [], ['HIDE_ICONS' => 'Y']);

/**
 * @var PhpEngine $view
 */ ?>
<div class="measure_dog__wrapper js-measure-dog">
    <div class="measure_dog">
        <div class="content_dropdown js-content-dropdown-trigger mobile_mq">
            <div class="content_dropdown__title">
                Узнать размер собаки
                <div class="content_dropdown__arrow">
                    <?= new SvgDecorator('icon-up-arrow') ?>
                </div>
            </div>
        </div>
        <div class="content_dropdown__content js-content-dropdown-content">
            <div class="measure_dog__title tablet_up_mq">Узнайте размер вашей собаки</div>
            <div class="measure_dog__paragraph">Измерьте свою собаку и укажите параметры в сантиметрах, как
                показано на рисунке
            </div>
            <div class="measure_dog__img"></div>
            <form class="measure_dog__size js-measure-dog-form">
                <label class="measure_dog__label">1. Обхват груди
                    <input class="measure_dog__input" pattern="[0-9]" id="chest_size" type="number" min="1" required>
                </label>
                <label class="measure_dog__label">2. Длина спинки
                    <input class="measure_dog__input" pattern="[0-9]" id="back_size" type="number" min="1" required>
                </label>
                <label class="measure_dog__label">3. Обхват шеи
                    <input class="measure_dog__input" pattern="[0-9]" id="neck_size" type="number" min="1" required>
                </label>
                <input class="measure_dog__button" type="submit" value="Узнать размер">
            </form>
        </div>
    </div>
    <div class="measure_dog--custom_size">
        <div class="content_dropdown mobile_mq js-content-dropdown-trigger">
            <div class="content_dropdown__title">Узнать размер собаки
                <div class="content_dropdown__arrow">
                    <?= new SvgDecorator('icon-up-arrow') ?>
                </div>
            </div>
        </div>
        <div class="content_dropdown__content js-content-dropdown-content">
            <div class="measure_dog__content_wrapper">
                <div class="measure_dog__img"></div>
                <div class="measure_dog__title">У вашей собаки нестандартный размер</div>
                <div class="measure_dog__paragraph">
                    <div>Указанные размеры не совпадают со стандартными.</div>
                    <div>
                        Пожалуйста, приезжайте в магазин для примерки, или обратитесь к нашим специалистам
                        за помощью в подборе размера +7 (800) 770-00-22
                    </div>
                </div>
                <div class="measure_dog__button_set">
                    <a class="measure_dog__button--secondary js-measure-dog-recalculate" href="#">
                        Рассчитать еще раз</a>
                    <a class="measure_dog__button js-scroll-to-catalog" href="#">Перейти в каталог</a>
                </div>
            </div>
        </div>
    </div>
    <div class="measure_dog--default_size">
        <div class="content_dropdown mobile_mq js-content-dropdown-trigger">
            <div class="content_dropdown__title">Узнать размер собаки
                <div class="content_dropdown__arrow">
                    <?= new SvgDecorator('icon-up-arrow') ?>
                </div>
            </div>
        </div>
        <div class="content_dropdown__content js-content-dropdown-content">
            <div class="measure_dog__content_wrapper">
                <div class="measure_dog__img"></div>
                <div class="measure_dog__title">Скорее всего у вашей собаки размер <span></span></div>
                <div class="measure_dog__paragraph">так же мы рекомендуем померить размер <span></span>
                </div>
                <div class="measure_dog__button_set">
                    <a class="measure_dog__button--secondary js-measure-dog-recalculate" href="#">
                        Рассчитать еще раз
                    </a>
                    <a class="measure_dog__button js-scroll-to-catalog" href="#">Перейти в каталог</a></div>
            </div>
        </div>
    </div>
</div>
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
