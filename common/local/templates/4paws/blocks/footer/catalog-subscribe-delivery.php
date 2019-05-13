<?php

use FourPaws\Decorators\SvgDecorator;

?>

<section class="b-popup-subscribe-delivery js-popup-section js-popup-subscribe-delivery" data-popup="catalog-subscribe-delivery">
    <div class="b-popup-subscribe-delivery__content">
        <div class="b-popup-subscribe-delivery__top">
            <div class="b-popup-subscribe-delivery__header">
                <div class="b-container">
                    <div class="b-popup-subscribe-delivery__inner-header">
                        <div class="b-popup-subscribe-delivery__back" data-back-product-subscribe-delivery="true">
                            <span class="b-icon b-icon--back-subscribe">
                                <?= new SvgDecorator('icon-arrow-back-subscribe', 24, 24) ?>
                            </span>
                            <span class="hide-mobile">Назад</span>
                        </div>
                        <h1 class="b-popup-subscribe-delivery__title">
                            Добавление товаров в подписку
                        </h1>
                        <a class="b-popup-subscribe-delivery__close"
                           href="javascript:void(0);"
                           data-close-subscribe-delivery-popup="false"
                           title="Закрыть"></a>
                    </div>
                </div>
            </div>

            <div class="b-popup-subscribe-delivery__catalog js-catalog-subscribe-delivery"></div>
        </div>

        <div class="b-popup-subscribe-delivery__footer">
            <div class="b-container">
                <?php include __DIR__ . '/copyright.php' ?>
            </div>
        </div>
        <div class="b-shadow b-shadow--popup-subscribe js-shadow"></div>
        <div class="b-popup-subscribe-delivery__bg js-bg-popup-subscribe-delivery"></div>
    </div>
</section>