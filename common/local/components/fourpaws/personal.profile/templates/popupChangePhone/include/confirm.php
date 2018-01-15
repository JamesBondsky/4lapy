<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $phone */ ?>
<div class="b-registration__step b-registration__step--two js-phone-change-two js-hidden-valid-fields">
    <input type="hidden" name="action" value="confirmPhone">
    <div class="b-registration__text b-registration__text--phone" id="js-resend"
         data-url="/ajax/personal/profile/changePhone/"
         data-phone="+7 (920) 161-24-27"
         data-action="resendSms">Ваш номер <?= $phone ?>
    </div>
    <a class="b-registration__text b-registration__text--phone-edit js-open-popup"
       href="javascript:void(0);"
       title="Сменить номер"
       data-popup-id="edit-phone"
       data-url="/ajax/personal/profile/changePhone/" data-action="get" data-step="phone">Сменить номер</a>
    <div class="b-input-line b-input-line--popup-authorization b-input-line--sms">
        <div class="b-input-line__label-wrapper">
            <label class="b-input-line__label" for="sms-phone">SMS-код
            </label>
        </div>
        <div class="b-input b-input--registration-form">
            <input class="b-input__input-field b-input__input-field--registration-form ok"
                   id="sms-phone"
                   placeholder=""
                   name="confirmCode"
                   type="text">
            <div class="b-error b-error--ok"><span class="js-message">Поле верно заполнено</span>
            </div>
        </div>
        <a class="b-link-gray"
           href="javascript:void(0);"
           title="Отправить снова"
           data-url="/ajax/personal/profile/changePhone/"
           data-phone="<?= $phone ?>"
           data-action="resendSms">Отправить снова</a>
    </div>
</div>
