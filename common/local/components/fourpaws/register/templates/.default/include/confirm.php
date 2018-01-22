<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;

/** @var string $name */ ?>
<div class="b-registration__content">
    <div class="b-registration__text-block">
        <p class="b-registration__text"><?= $name ?>, спасибо за регистрацию!</p>
        <p class="b-registration__text">Теперь вы можете:</p>
    </div>
    <ul class="b-registration__add-more-list">
        <li class="b-registration__add-more-item">
            <a class="b-registration__add-more-link"
               href="/personal/pets/"
               title="">
                <span class="b-icon b-icon--registration">
                    <?= new SvgDecorator('icon-info-pet', 64, 64) ?>
                </span>
                <dl class="b-registration__add-more-dl">
                    <dt class="b-registration__add-more-dt">Добавить инфомацию о питомце</dt>
                    <dd class="b-registration__add-more-dd">Для персональных рекомендаций</dd>
                </dl>
            </a>
        </li>
        <li class="b-registration__add-more-item">
            <a class="b-registration__add-more-link"
               href="/personal/address/"
               title="">
                <span class="b-icon b-icon--registration">
                    <?= new SvgDecorator('icon-pin-pet', 64, 64) ?>
                </span>
                <dl class="b-registration__add-more-dl">
                    <dt class="b-registration__add-more-dt">Добавить адреса доставки</dt>
                    <dd class="b-registration__add-more-dd">Для быстрого оформления</dd>
                </dl>
            </a>
        </li>
    </ul>
</div>
