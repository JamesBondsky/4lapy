<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $phone */ ?>
<div class="b-registration__content b-registration__content--moiety b-registration__content--step" style="width:100% !important;">
    <div class="b-registration__text-instruction">Пожалуйста, введите номер телефона</div>
    <form class="b-registration__form js-form-validation js-ajax-phone-form" data-url="/ajax/user/auth/login/" method="post">
        <input type="hidden" name="action" value="get" class="js-no-valid">
        <input type="hidden" name="step" value="sendSmsCode" class="js-no-valid">
        <input type="hidden" name="backurl" value="<?=$backUrl?>" class="js-no-valid">
        <div class="b-input-line">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="mobile-number-by-auth">Мобильный телефон</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="tel"
                       name="phone"
                       value="<?= $phone ?>"
                       id="mobile-number-by-auth"
                       placeholder="" />
                <div class="b-error"><span class="js-message"></span></div>
            </div>
        </div>
        <div><span class="b-registration__auth-error"></span></div>
        <button class="b-button b-button--social b-button--full-width">Отправить код</button>
    </form>
</div>