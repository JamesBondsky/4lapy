<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $login
 * @var string $backUrl
 */ ?>
<div class="b-registration__content b-registration__content--create-password">
    <div class="b-registration__text-instruction b-registration__text-instruction--create-password">Введите и повторите
                                                                                                    новый пароль
    </div>
    <form class="b-registration__form js-form-validation js-registration-create-new-password js-recovery-form"
          data-url="/ajax/user/auth/forgotPassword/" method="post">
        <input type="hidden" name="action" value="savePassword">
        <input type="hidden" name="backurl" value="<?= $backUrl ?>">
        <input type="hidden" name="step" value="sendSmsCode">
        <input type="hidden" name="login" value="<?= $login ?? $arResult['EMAIL'] ?>">
        <div class="b-input-line b-input-line--create-password">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="registration-password-first">Пароль</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       id="registration-password-first"
                       placeholder=""
                       name="password"
                       type="password"
                       tabindex="1">
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
                       id="registration-password-second"
                       placeholder=""
                       name="confirmPassword"
                       data-type="password_two"
                       type="password"
                       tabindex="2">
                <div class="b-error"><span class="js-message"></span></div>
            </div>
            <a class="b-input-line__eye js-open-password" href="javascript:void(0);" title=""></a>
        </div>
        <button class="b-button b-button--social b-button--full-width b-button--create-password" tabindex="3">
            Сохранить
        </button>
    </form>
</div>