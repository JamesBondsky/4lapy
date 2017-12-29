<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $phone */ ?>
<input type="hidden" name="action" value="confirmPhone">
<div class="b-registration__step b-registration__step--two js-phone-change-two js-hidden-valid-fields">
    <div class="b-registration__text b-registration__text--phone">Ваш номер <?= $phone ?></div>
    <a class="b-registration__text b-registration__text--phone-edit js-open-popup"
       href="javascript:void(0);"
       title="Сменить номер"
       data-popup-id="edit-phone">Сменить номер</a>
    <div class="b-input-line b-input-line--popup-authorization b-input-line--sms">
        <div class="b-input-line__label-wrapper">
            <label class="b-input-line__label" for="sms-phone">SMS-код</label>
        </div>
        <div class="b-input b-input--registration-form">
            <input class="b-input__input-field b-input__input-field--registration-form"
                   type="text"
                   id="sms-phone"
                   placeholder=""
                   name="confirmCode" />
            <div class="b-error"><span class="js-message"></span>
            </div>
        </div>
        <a class="b-link-gray"
           href="javascript:void(0);"
           title="Отправить снова"
           data-url="/ajax/user/auth/changePhone/"
           data-action="resendSms">Отправить снова</a>
    </div>
</div>
<a class="b-link b-link--subscribe-delivery js-open-popup js-open-popup--subscribe-delivery js-open-popup"
   href="javascript:void(0)"
   title="Изменить"
   data-popup-id="edit-phone-step"
   data-url="/ajax/user/auth/changePhone/"
   data-action="get"
   data-step="phone">
    <span class="b-link__text b-link__text--subscribe-delivery js-open-popup">Изменить</span>
</a>
