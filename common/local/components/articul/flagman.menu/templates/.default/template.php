<?php

use FourPaws\Decorators\SvgDecorator;

?>

<section class="banner-flagship-store">
    <div class="b-container">
        <div class="banner-flagship-store__title">Запись на услуги</div>
        <div class="banner-flagship-store__subtitle">
                <span class="b-icon">
                    <?=new SvgDecorator('icon-delivery-header', 24, 24)?>
                </span>
            <span>Адрес: г.&nbsp;Москва, <nobr>пр-кт Вернадского, д.&nbsp;6,</nobr> ТЦ&nbsp;&laquo;Капитолий&raquo;, <nobr>-1 этаж</nobr></span>
        </div>
    </div>
</section>

<section class="nav-flagship-store">
    <div class="b-container">
        <div class="nav-flagship-store__list">
            <?php if ($arParams['SHOW_GROOMING'] == 'Y') : ?>
                <div class="nav-flagship-store__item" data-nav-flagship-store="grooming">
                    <div class="nav-flagship-store__icon">
                        <?=new SvgDecorator('icon-flagship-grooming', 61, 61)?>
                    </div>
                    <div class="nav-flagship-store__title">Груминг</div>
                </div>
            <?php endif; ?>
            <?php if ($arParams['SHOW_TRAINING'] == 'Y') : ?>
                <div class="nav-flagship-store__item" data-nav-flagship-store="training">
                    <div class="nav-flagship-store__icon">
                        <?=new SvgDecorator('icon-flagship-walking', 61, 61)?>
                    </div>
                    <div class="nav-flagship-store__title">Тренировочный клуб</div>
                </div>
            <?php endif; ?>
            <?php /*if ($arParams['SHOW_LECTION'] == 'Y') : ?>
                <div class="nav-flagship-store__item" data-nav-flagship-store="lectures">
                    <div class="nav-flagship-store__icon">
                        <?=new SvgDecorator('icon-flagship-lectures', 61, 61)?>
                    </div>
                    <div class="nav-flagship-store__title">Лекции</div>
                </div>
            <?php endif; */?>
        </div>
    </div>
</section>