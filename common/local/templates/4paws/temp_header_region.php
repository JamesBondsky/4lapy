<?php

use FourPaws\Decorators\SvgDecorator;

?>

<div class="b-header__wrapper-for-popover">
    <a class="b-combobox b-combobox--header js-open-popover" href="javascript:void(0);" title="Нижний Новгород">
        <span class="b-icon b-icon--location">
            <?= new SvgDecorator('icon-delivery-header', 14, 16) ?>
        </span>
        Нижний Новгород
        <span class="b-icon b-icon--delivery-arrow">
            <?= new SvgDecorator('icon-arrow-down', 10, 13) ?>
        </span>
    </a>
    <div class="b-popover b-popover--blue-arrow js-your-city">
        <p class="b-popover__text">Ваш город&nbsp;&mdash; <span>Нижний Новгород</span>?</p>
        <a class="b-popover__link" href="javascript:void(0)" title="">Да</a>
        <a class="b-popover__link b-popover__link--last" href="javascript:void(0)" title="">Нет, выбрать другой</a>
    </div>
</div>
