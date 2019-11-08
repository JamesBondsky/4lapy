<?php

use FourPaws\Decorators\SvgDecorator;

?>

<section class="banner-flagship-store">
    <div class="b-container">
        <div class="banner-flagship-store__title">Сервисы<br/> флагманского магазина</div>
        <div class="banner-flagship-store__subtitle">
                <span class="b-icon">
                    <?=new SvgDecorator('icon-delivery-header', 24, 24)?>
                </span>
            <span>ул. Вавилова, 3, ТРК Гагаринский</span>
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
            <?php if ($arParams['SHOW_LECTION'] == 'Y') : ?>
                <div class="nav-flagship-store__item" data-nav-flagship-store="lectures">
                    <div class="nav-flagship-store__icon">
                        <?=new SvgDecorator('icon-flagship-lectures', 61, 61)?>
                    </div>
                    <div class="nav-flagship-store__title">Лекции</div>
                </div>
            <?php endif; ?>
            <?php if ($arParams['SHOW_TRAINING'] == 'Y') : ?>
                <div class="nav-flagship-store__item" data-nav-flagship-store="walking">
                    <div class="nav-flagship-store__icon">
                        <?=new SvgDecorator('icon-flagship-walking', 61, 61)?>
                    </div>
                    <div class="nav-flagship-store__title">Выгул-тренировка</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>