<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\External\Manzana\Model\Client;

$request = Application::getInstance()->getContext()->getRequest();
$backUrl = $arResult['BACK_URL'] ?? $request->get('backurl');

/**
 * @var Client $manzanaItem
 * @var string $phone
 * @var string $formSubmit
 */ ?>
<div class="b-registration__content b-registration__content--moiety b-registration__content--step b-registration__content--back">
    <div class="b-step-form">Шаг <span>2</span> из <span>2</span></div>
    <form class="b-registration__form b-registration__form--margin js-form-validation js-registration-form"
          id="reg-step5-form"
          onsubmit="<?= $formSubmit ?>"
          data-url="/ajax/user/auth/register-r/"
          method="post">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="PERSONAL_PHONE" value="<?= $phone ?>">
        <input type="hidden" name="backurl" value="<?=$backUrl?>">
        <div class="b-input-line b-input-line--user-data js-hidden-valid-fields js-small-input-two">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-surname">Фамилия</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       id="registration-surname"
                       placeholder=""
                       name="LAST_NAME"
                       data-text="0"
                       type="text"
                       value="<?= $manzanaItem instanceof Client ? $manzanaItem->lastName : '' ?>"/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--user-data js-small-input-two">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-name">Имя</label>
                <span class="b-input-line__require">(обязательно)</span>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="text"
                       id="registration-name"
                       name="NAME"
                       data-text="1"
                       placeholder=""
                       value="<?= $manzanaItem instanceof Client ? $manzanaItem->firstName : '' ?>"/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--user-data js-no-valid">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-patronymic">Отчество</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="text"
                       id="registration-patronymic"
                       name="SECOND_NAME"
                       data-text="2"
                       placeholder=""
                       value="<?= $manzanaItem instanceof Client ? $manzanaItem->secondName : '' ?>"/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--user-data js-date-valid js-hidden-valid-fields">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-date-birth">Дата рождения</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="text"
                       id="registration-date-birth"
                       name="PERSONAL_BIRTHDAY"
                       data-text="3"
                       placeholder=""
                       value="<?= $manzanaItem instanceof Client ? $manzanaItem->getBirthDateFormated() : '' ?>"/>
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--user-data">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-email">Эл. почта</label>
                <span class="b-input-line__require">(обязательно)</span>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="email"
                       id="registration-email"
                       name="EMAIL"
                       placeholder=""
                       value="<?= $manzanaItem instanceof Client ? $manzanaItem->email : '' ?>"/>
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
                       placeholder=""/>
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
                       data-radio="0"
                       <?=$manzanaItem instanceof Client && (int)$manzanaItem->genderCode === 1 ? 'checked="checked"' : ''?>/>
                <label class="b-radio__label" for="registration-male"><span class="b-radio__text-label">мужской</span>
                </label>
            </div>
            <div class="b-radio">
                <input class="b-radio__input"
                       type="radio"
                       name="PERSONAL_GENDER"
                       id="registration-female"
                       value="F"
                       data-radio="1"
                    <?=$manzanaItem instanceof Client && (int)$manzanaItem->genderCode === 2 ? 'checked="checked"' : ''?>
                />
                <label class="b-radio__label" for="registration-female"><span class="b-radio__text-label">женский</span>
                </label>
            </div>
        </div>
        <div class="b-checkbox b-checkbox--agree">
            <input class="b-checkbox__input" type="checkbox" name="UF_CONFIRMATION" id="registration-agree" required/>
            <label class="b-checkbox__name b-checkbox__name--agree" for="registration-agree">
                <span class="b-checkbox__text-agree">Я ознакомлен(а) и соглашаюсь с условиями
                    <a class="b-checkbox__link-agree"
                       href="/user-agreement/"
                       title="пользовательского соглашения"
                       target="_blank">пользовательского соглашения.</a>
                </span>
                <span class="b-checkbox__text-agree">Я даю согласие на
                    <a class="b-checkbox__link-agree"
                       href="/privacy-policy/"
                       title="обработку персональных данных"
                       target="_blank">обработку персональных данных.</a>
                </span>
            </label>
        </div>
        <button class="b-button b-button--social b-button--full-width">Зарегистрироваться</button>
    </form>
    <a class="b-registration__back js-reg3-back" href="javascript:void(0);" title="Назад"
       data-url="/ajax/user/auth/register-r/"
       data-action="get"
       data-step="step1"
       data-phone="<?= $phone ?>">
        <span class="b-icon b-icon--back-long">
            <?= new SvgDecorator('icon-back-form', 13, 11) ?>
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
