<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-registration__content b-registration__content--create-password">
    <div class="b-registration__text-instruction b-registration__text-instruction--create-password">Введите и повторите
                                                                                                    новый пароль
    </div>
    <form class="b-registration__form js-form-validation" data-url="/ajax/user/auth/forgotPassword/">
        <input type="hidden" name="action" value="savePassword">
        <input type="hidden" name="step" value="sendSmsCode">
        <input type="hidden" name="login" value="<?= $login ?? $arResult['EMAIL'] ?>">
        <div class="b-input-line b-input-line--create-password">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-password-first">Пароль</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="password"
                       id="registration-password-first"
                       name="password"
                       placeholder="" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
            <a class="b-input-line__eye js-open-password"
               href="javascript:void(0);"
               title=""></a><span class="b-link-gray">Минимум 6 символов</span>
        </div>
        <div class="b-input-line b-input-line--create-password">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-password-second">Повторите пароль</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="password"
                       id="registration-password-second"
                       name="confirmPassword"
                       placeholder="" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
            <a class="b-input-line__eye js-open-password" href="javascript:void(0);" title=""></a>
        </div>
        <button class="b-button b-button--social b-button--full-width b-button--create-password">Сохранить</button>
    </form>
</div>
