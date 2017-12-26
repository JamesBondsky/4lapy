<?php

use FourPaws\External\Manzana\Model\Client;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var \FourPaws\External\Manzana\Model\Client $manzanaItem */ ?>
<div class="b-registration__content b-registration__content--moiety b-registration__content--step b-registration__content--back">
    <div class="b-step-form">Шаг <span>2</span> из <span>2</span>
    </div>
    <form class="b-registration__form b-registration__form--margin js-form-validation"
          data-url="/ajax/user/auth/register/"
          method="post">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="PERSONAL_PHONE" value="<?= $phone ?>">
        <div class="b-input-line b-input-line--user-data">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-surname">Фамилия</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="text"
                       id="registration-surname"
                       name="LAST_NAME"
                       placeholder=""
                       value="<?= $manzanaItem instanceof Client ? $manzanaItem->lastName : '' ?>" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--user-data">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-name">Имя</label>
                <span class="b-input-line__require">(обязательно)</span>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="text"
                       id="registration-name"
                       name="NAME"
                       placeholder=""
                       value="<?= $manzanaItem instanceof Client ? $manzanaItem->firstName : '' ?>" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--user-data">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-patronymic">Отчество</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="text"
                       id="registration-patronymic"
                       name="SECOND_NAME"
                       placeholder=""
                       value="<?= $manzanaItem instanceof Client ? $manzanaItem->secondName : '' ?>" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--user-data">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-date-birth">Дата рождения</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="text"
                       id="registration-date-birth"
                       name="PERSONAL_BIRTHDAY"
                       placeholder=""
                       value="<?= $manzanaItem instanceof Client ? $manzanaItem->birthDate : '' ?>" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--user-data">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-email">Эл. почта</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="email"
                       id="registration-email"
                       name="EMAIL"
                       placeholder=""
                       value="<?= $manzanaItem instanceof Client ? $manzanaItem->email : '' ?>" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--user-data">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-password-5">Пароль</label>
                <span class="b-input-line__require">(обязательно)</span>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="password"
                       id="registration-password-5"
                       name="PASSWORD"
                       placeholder="" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
            <span class="b-link-gray">Минимум 6 символов</span>
        </div>
        <div class="b-input-line b-input-line--user-data">
            <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Пол</span>
            </div>
            <div class="b-radio">
                <input class="b-radio__input"
                       type="radio"
                       name="PERSONAL_GENDER"
                       id="registration-male"
                       value="M"
                       checked="checked" />
                <label class="b-radio__label" for="registration-male"><span class="b-radio__text-label">мужской</span>
                </label>
            </div>
            <div class="b-radio">
                <input class="b-radio__input" type="radio" name="PERSONAL_GENDER" id="registration-female" value="F" />
                <label class="b-radio__label" for="registration-female"><span class="b-radio__text-label">женский</span>
                </label>
            </div>
        </div>
        <div class="b-checkbox b-checkbox--agree">
            <input class="b-checkbox__input" type="checkbox" name="UF_CONFIRMATION" id="registration-agree" required />
            <label class="b-checkbox__name b-checkbox__name--agree" for="registration-agree">
                <span class="b-checkbox__text-agree">Я ознакомлен(а) и соглашаюсь с условиями
                    <a class="b-checkbox__link-agree"
                       href="/company/user-agreement/"
                       title="пользовательского соглашения">пользовательского соглашения.</a>
                </span>
                <span class="b-checkbox__text-agree">Я даю согласие на
                    <a class="b-checkbox__link-agree"
                       href="/company/privacy-policy/"
                       title="обработку персональных данных">обработку персональных данных.</a>
                </span>
            </label>
        </div>
        <button class="b-button b-button--social b-button--full-width" type="submit">Зарегистрироваться</button>
    </form>
    <a class="b-registration__back" href="javascript:void(0);" title="Назад" data-action="get"
       data-step="step1"
       data-phone="<?= $phone ?>">
        <span class="b-icon b-icon--back-long">
            <?= new \FourPaws\Decorators\SvgDecorator('icon-back-form', 13, 21) ?>
        </span>Назад
    </a>
</div>
<section class="b-registration__additional-info b-registration__additional-info--step b-registration__additional-info--back">
    <h3 class="b-registration__title-advantage">Почему так много?</h3>
    <ul class="b-social-advantage">
        <li class="b-social-advantage__item">Чтобы помогать вам быстрее и правильнее</li>
        <li class="b-social-advantage__item">Нам приятно работать с людьми, а не роботами</li>
    </ul>
</section>
