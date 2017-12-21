<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-registration__content b-registration__content--moiety b-registration__content--step">
    <div class="b-step-form">Шаг <span>1</span> из <span>2</span>
    </div>
    <form class="b-registration__form" data-url="/ajax/user/auth/login/" method="post">
        <input type="hidden" name="action" value="login">
        <div class="b-input-line b-input-line--phone-two">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="mobile-number-2">Мобильный телефон</label>
                <span class="b-input-line__require">(обязательно)</span>
            </div>
            <input class="b-input b-input--registration-form"
                   type="tel"
                   id="mobile-number-2"
                   placeholder=""
                   name="login"
                   value="<?= $phone ?>" />
            <div class="b-input-line__warning">
                <p class="b-input-line__text-warning">Пользователь с этим номером телефона уже зарегистрирован в нашем
                                                      магазине.</p>
                <p class="b-input-line__text-warning">Попробуйте ввести пароль для входа в систему.</p>
            </div>
        </div>
        <div class="b-input-line b-input-line--phone-two">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="password-2">Пароль</label>
            </div>
            <input class="b-input b-input--registration-form"
                   type="password"
                   name="password"
                   id="password-2"
                   placeholder="" />
            <a class="b-link-gray" href="/personal/forgot-password/" title="Забыли пароль?">Забыли пароль?</a>
        </div>
        <button class="b-button b-button--social b-button--full-width" type="submit">Далее</button>
    </form>
</div>
<section class="b-registration__additional-info b-registration__additional-info--step">
    <h3 class="b-registration__title-advantage">Зачем это нужно?</h3>
    <ul class="b-social-advantage">
        <li class="b-social-advantage__item">Для оперативной связи по поводу доставки</li>
        <li class="b-social-advantage__item">Для привязки бонусной карты</li>
        <li class="b-social-advantage__item">Телефон можно использовать как логин при входе</li>
    </ul>
</section>
