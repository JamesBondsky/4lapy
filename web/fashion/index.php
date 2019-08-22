<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Новая коллекция одежды для собак');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle("Новая коллекция одежды для собак");

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Decorators\SvgDecorator;

?>
<div class="fashion-page">
    <section class="fashion-main-banner">
        <div class="fashion-main-banner__img"></div>
    </section>

    <section class="fashion-info">
        <div class="b-container">
            <div class="fashion-info__list">
                <div class="fashion-item-info fashion-item-info_small">
                    <div class="fashion-item-info__title">2&nbsp;000 товаров</div>
                    <div class="fashion-item-info__descr">из&nbsp;новой коллекции<br/> одежды и&nbsp;обуви</div>
                </div>
                <div class="fashion-item-info">
                    <div class="fashion-item-info__title">скидки до&nbsp;15%</div>
                    <div class="fashion-item-info__descr">при покупке <nobr>2-х</nobr> вещей&nbsp;&mdash; 7%,<br/> <nobr>3-х</nobr> вещей&nbsp;&mdash; 10%, <nobr>4-х</nobr>&nbsp;&mdash; 15%</div>
                </div>
                <div class="fashion-item-info fashion-item-info_full hide-xs">
                    <div class="fashion-item-info__title">бесплатная доставка и&nbsp;примерка</div>
                    <div class="fashion-item-info__descr">
                        <span>закажи несколько размеров<br/> домой или в&nbsp;магазин</span>
                        <span class="fashion-item-info__arr"></span>
                        <span>примерь и&nbsp;купи<br/> то, что подошло</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="fashion-total-look-section">
        <div class="b-container">
            <div class="fashion-total-look-section__group active" style="display: block;" data-group-total-look-fashion="true">
                <div class="fashion-total-look" data-item-total-look-fashion="true">
                    <div class="fashion-total-look__slider" data-total-look-slider-fashion="true">
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look1.jpg" alt="">
                        </div>
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look2.jpg" alt="">
                        </div>
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look1.jpg" alt="">
                        </div>
                    </div>
                    <div class="fashion-total-look__list">
                        <div data-list-fashion-total-look="true" data-url="/ajax/catalog/product-info/">
                            <?php
                            include __DIR__ . '/items-total-look.php';
                            ?>
                        </div>
                    </div>
                </div>
                <div class="fashion-total-look" data-item-total-look-fashion="true">
                    <div class="fashion-total-look__slider" data-total-look-slider-fashion="true">
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look2.jpg" alt="">
                        </div>
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look1.jpg" alt="">
                        </div>
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look2.jpg" alt="">
                        </div>
                    </div>
                    <div class="fashion-total-look__list">
                        <div data-list-fashion-total-look="true" data-url="/ajax/catalog/product-info/">
                            <?php
                            include __DIR__ . '/items-total-look.php';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fashion-total-look-section__group" data-group-total-look-fashion="true">
                <div class="fashion-total-look" data-item-total-look-fashion="true">
                    <div class="fashion-total-look__slider" data-total-look-slider-fashion="true">
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look1.jpg" alt="">
                        </div>
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look2.jpg" alt="">
                        </div>
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look1.jpg" alt="">
                        </div>
                    </div>
                    <div class="fashion-total-look__list">
                        <div data-list-fashion-total-look="true" data-url="/ajax/catalog/product-info/">
                            <?php
                            include __DIR__ . '/items-total-look.php';
                            ?>
                        </div>
                    </div>
                </div>
                <div class="fashion-total-look" data-item-total-look-fashion="true">
                    <div class="fashion-total-look__slider" data-total-look-slider-fashion="true">
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look2.jpg" alt="">
                        </div>
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look1.jpg" alt="">
                        </div>
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look2.jpg" alt="">
                        </div>
                    </div>
                    <div class="fashion-total-look__list">
                        <div data-list-fashion-total-look="true" data-url="/ajax/catalog/product-info/">
                            <?php
                            include __DIR__ . '/items-total-look.php';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fashion-total-look-section__group" data-group-total-look-fashion="true">
                <div class="fashion-total-look" data-item-total-look-fashion="true">
                    <div class="fashion-total-look__slider" data-total-look-slider-fashion="true">
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look2.jpg" alt="">
                        </div>
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look1.jpg" alt="">
                        </div>
                        <div class="item-total-look-slider">
                            <img src="/fashion/img/total-look/total-look2.jpg" alt="">
                        </div>
                    </div>
                    <div class="fashion-total-look__list">
                        <div data-list-fashion-total-look="true" data-url="/ajax/catalog/product-info/">
                            <?php
                            include __DIR__ . '/items-total-look.php';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fashion-total-look-section__btn-wrap">
                <div class="fashion-total-look-section__btn" data-btn-next-look-fashion="true">Показать ещё</div>
            </div>
        </div>
    </section>

    <section class="fashion-info hide show-xs">
        <div class="b-container">
            <div class="fashion-info__list">
                <div class="fashion-item-info fashion-item-info_full">
                    <div class="fashion-item-info__title">бесплатная доставка и&nbsp;примерка</div>
                    <div class="fashion-item-info__descr">
                        <span>закажи несколько размеров<br/> домой или в&nbsp;магазин</span>
                        <span class="fashion-item-info__arr"></span>
                        <span>примерь и&nbsp;купи<br/> то, что подошло</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="fashion-category">
        <div class="b-container">
            <div class="fashion-category-filter">
                <div class="fashion-category-filter__item active" data-type-filter-category-fashion="0">
                    <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                        <div class="fashion-category-filter__img" style="background-image: url('/fashion/img/category/category-fashion_overalls.png')"></div>
                    </div>
                    <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true">Комбинезоны</div>
                </div>
                <div class="fashion-category-filter__item active" data-type-filter-category-fashion="1">
                    <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                        <div class="fashion-category-filter__img" style="background-image: url('/fashion/img/category/category-fashion_sweaters.png')"></div>
                    </div>
                    <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true">Свитера и толстовки</div>
                </div>
                <div class="fashion-category-filter__item active" data-type-filter-category-fashion="2">
                    <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                        <div class="fashion-category-filter__img" style="background-image: url('/fashion/img/category/category-fashion_footwear.png')"></div>
                    </div>
                    <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true">Обувь</div>
                </div>
                <div class="fashion-category-filter__item" data-type-filter-category-fashion="3">
                    <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                        <div class="fashion-category-filter__img" style="background-image: url('/fashion/img/category/category-fashion_jackets.png')"></div>
                    </div>
                    <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true">Куртки и жилетки</div>
                </div>
                <div class="fashion-category-filter__item" data-type-filter-category-fashion="4">
                    <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                        <div class="fashion-category-filter__img" style="background-image: url('/fashion/img/category/category-fashion_blankets.png')"></div>
                    </div>
                    <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true">Попоны</div>
                </div>
                <div class="fashion-category-filter__item" data-type-filter-category-fashion="5">
                    <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                        <div class="fashion-category-filter__img" style="background-image: url('/fashion/img/category/category-fashion_raincoats.png')"></div>
                    </div>
                    <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true">Дождевики</div>
                </div>
                <div class="fashion-category-filter__item" data-type-filter-category-fashion="6">
                    <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                        <div class="fashion-category-filter__img" style="background-image: url('/fashion/img/category/category-fashion_socks.png')"></div>
                    </div>
                    <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true">Носки</div>
                </div>
                <div class="fashion-category-filter__item" data-type-filter-category-fashion="7">
                    <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                        <div class="fashion-category-filter__img" style="background-image: url('/fashion/img/category/category-fashion_costumes.png')"></div>
                    </div>
                    <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true">Костюмы</div>
                </div>
                <div class="fashion-category-filter__item" data-type-filter-category-fashion="8">
                    <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                        <div class="fashion-category-filter__img" style="background-image: url('/fashion/img/category/category-fashion_t-shirts.png')"></div>
                    </div>
                    <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true">Футболки и майки</div>
                </div>
                <div class="fashion-category-filter__item" data-type-filter-category-fashion="9">
                    <div class="fashion-category-filter__img-wrap" data-img-type-filter-category-fashion="true">
                        <div class="fashion-category-filter__img" style="background-image: url('/fashion/img/category/category-fashion_hats.png')"></div>
                    </div>
                    <div class="fashion-category-filter__title" data-title-type-filter-category-fashion="true">Шапки</div>
                </div>
            </div>
        </div>
        <div class="fashion-category-list">
                <div class="item-category-fashion active" data-item-filter-category-fashion="0" data-url="/ajax/catalog/product-info/">
                    <div class="b-container">
                        <div class="item-category-fashion__title">Комбинезоны</div>
                        <div class="item-category-fashion__content">
                            <div class="item-category-fashion__img" style="background-image: url('/fashion/img/category/category-img_1.jpg')"></div>
                            <div class="item-category-fashion__slider" data-slider-category-fashion="true">
                                <?php
                                    include __DIR__ . '/products-list-category1.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-category-fashion active" data-item-filter-category-fashion="1" data-url="/ajax/catalog/product-info/">
                    <div class="b-container">
                        <div class="item-category-fashion__title">Свитера и толстовки</div>
                        <div class="item-category-fashion__content">
                            <div class="item-category-fashion__img" style="background-image: url('/fashion/img/category/category-img_2.jpg')"></div>
                            <div class="item-category-fashion__slider" data-slider-category-fashion="true">
                                <?php
                                include __DIR__ . '/products-list-category2.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-category-fashion active" data-item-filter-category-fashion="2" data-url="/ajax/catalog/product-info/">
                    <div class="b-container">
                        <div class="item-category-fashion__title">Обувь</div>
                        <div class="item-category-fashion__content">
                            <div class="item-category-fashion__img" style="background-image: url('/fashion/img/category/category-img_3.jpg')"></div>
                            <div class="item-category-fashion__slider" data-slider-category-fashion="true">
                                <?php
                                include __DIR__ . '/products-list-category3.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-category-fashion" data-item-filter-category-fashion="3" data-url="/ajax/catalog/product-info/">
                    <div class="b-container">
                        <div class="item-category-fashion__title">Куртки и жилетки</div>
                        <div class="item-category-fashion__content">
                            <div class="item-category-fashion__img" style="background-image: url('/fashion/img/category/category-img_1.jpg')"></div>
                            <div class="item-category-fashion__slider" data-slider-category-fashion="true">
                                <?php
                                include __DIR__ . '/products-list-category1.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-category-fashion" data-item-filter-category-fashion="4" data-url="/ajax/catalog/product-info/">
                    <div class="b-container">
                        <div class="item-category-fashion__title">Попоны</div>
                        <div class="item-category-fashion__content">
                            <div class="item-category-fashion__img" style="background-image: url('/fashion/img/category/category-img_2.jpg')"></div>
                            <div class="item-category-fashion__slider" data-slider-category-fashion="true">
                                <?php
                                include __DIR__ . '/products-list-category2.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-category-fashion" data-item-filter-category-fashion="5" data-url="/ajax/catalog/product-info/">
                    <div class="b-container">
                        <div class="item-category-fashion__title">Дождевики</div>
                        <div class="item-category-fashion__content">
                            <div class="item-category-fashion__img" style="background-image: url('/fashion/img/category/category-img_3.jpg')"></div>
                            <div class="item-category-fashion__slider" data-slider-category-fashion="true">
                                <?php
                                include __DIR__ . '/products-list-category3.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-category-fashion" data-item-filter-category-fashion="6" data-url="/ajax/catalog/product-info/">
                    <div class="b-container">
                        <div class="item-category-fashion__title">Носки</div>
                        <div class="item-category-fashion__content">
                            <div class="item-category-fashion__img" style="background-image: url('/fashion/img/category/category-img_1.jpg')"></div>
                            <div class="item-category-fashion__slider" data-slider-category-fashion="true">
                                <?php
                                include __DIR__ . '/products-list-category1.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-category-fashion" data-item-filter-category-fashion="7" data-url="/ajax/catalog/product-info/">
                    <div class="b-container">
                        <div class="item-category-fashion__title">Костюмы</div>
                        <div class="item-category-fashion__content">
                            <div class="item-category-fashion__img" style="background-image: url('/fashion/img/category/category-img_2.jpg')"></div>
                            <div class="item-category-fashion__slider" data-slider-category-fashion="true">
                                <?php
                                include __DIR__ . '/products-list-category2.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-category-fashion" data-item-filter-category-fashion="8" data-url="/ajax/catalog/product-info/">
                    <div class="b-container">
                        <div class="item-category-fashion__title">Футболки и майки</div>
                        <div class="item-category-fashion__content">
                            <div class="item-category-fashion__img" style="background-image: url('/fashion/img/category/category-img_3.jpg')"></div>
                            <div class="item-category-fashion__slider" data-slider-category-fashion="true">
                                <?php
                                include __DIR__ . '/products-list-category3.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-category-fashion" data-item-filter-category-fashion="9" data-url="/ajax/catalog/product-info/">
                    <div class="b-container">
                        <div class="item-category-fashion__title">Шапки</div>
                        <div class="item-category-fashion__content">
                            <div class="item-category-fashion__img" style="background-image: url('/fashion/img/category/category-img_1.jpg')"></div>
                            <div class="item-category-fashion__slider" data-slider-category-fashion="true">
                                <?php
                                include __DIR__ . '/products-list-category1.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>

    <section class="fashion-info-banner">
        <div class="fashion-info-banner__img">
            <picture>
                <source media="(max-width: 767px)" srcset="/fashion/img/fashion-info-banner_mobile.jpg">
                <img src="/fashion/img/fashion-info-banner.jpg" alt="Новая коллекция одежды для собак" />
            </picture>
        </div>
    </section>

    <section>
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
    </section>

    <section class="fashion-free-shipping">
        <div class="b-container">
            <div class="fashion-free-shipping__content">
                <div class="fashion-free-shipping__img"></div>
                <div class="fashion-free-shipping__info">
                    <div class="fashion-free-shipping__title">Закажи бесплатную доставку и&nbsp;примерку</div>
                    <ul class="fashion-free-shipping__steps">
                        <li>примерь несколько размеров</li>
                        <li>купи только то, что подошло</li>
                    </ul>
                    <div class="item-free-shipping">
                        <div class="item-free-shipping__title">домой</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_time"><b>время примерки</b> 15&nbsp;минут</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_delivery">
                            <p><b>доставка</b> от&nbsp;197Р<br/> бесплатно&nbsp;- при заказе от&nbsp;2000р</p>
                            <p>курьер привезёт ваш заказ в&nbsp;удобное место и&nbsp;время</p>
                        </div>
                    </div>
                    <div class="item-free-shipping">
                        <div class="item-free-shipping__title">в магазин</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_time"><b>время примерки</b> не ограничено</div>
                        <div class="item-free-shipping__descr item-free-shipping__descr_delivery">
                            <p><b>доставка</b> бесплатно</p>
                            <p>продавец поможет измерить питомца и&nbsp;быстро найти модель</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="fashion-interesting-clothes">
        <div class="b-container">
            <h2 class="fashion-title txt-center">Интересное про одежду</h2>
            <? $APPLICATION->IncludeComponent('fourpaws:items.list',
                'fashion',
                [
                    'ACTIVE_DATE_FORMAT'     => 'j F Y',
                    'AJAX_MODE'              => 'N',
                    'AJAX_OPTION_ADDITIONAL' => '',
                    'AJAX_OPTION_HISTORY'    => 'N',
                    'AJAX_OPTION_JUMP'       => 'N',
                    'AJAX_OPTION_STYLE'      => 'Y',
                    'CACHE_FILTER'           => 'Y',
                    'CACHE_GROUPS'           => 'N',
                    'CACHE_TIME'             => '36000000',
                    'CACHE_TYPE'             => 'A',
                    'CHECK_DATES'            => 'Y',
                    'FIELD_CODE'             => [
                        '',
                    ],
                    'FILTER_NAME'            => '',
                    'IBLOCK_ID'              => [
                        IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS),
                        IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES),
                    ],
                    'IBLOCK_TYPE'            => IblockType::PUBLICATION,
                    'NEWS_COUNT'             => '7',
                    'PREVIEW_TRUNCATE_LEN'   => '',
                    'PROPERTY_CODE'          => [
                        'PUBLICATION_TYPE',
                        'VIDEO',
                    ],
                    'SET_LAST_MODIFIED'      => 'N',
                    'SORT_BY1'               => 'ACTIVE_FROM',
                    'SORT_BY2'               => 'SORT',
                    'SORT_ORDER1'            => 'DESC',
                    'SORT_ORDER2'            => 'ASC',
                ],
                false,
                ['HIDE_ICONS' => 'Y']);
            ?>
        </div>
    </section>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>