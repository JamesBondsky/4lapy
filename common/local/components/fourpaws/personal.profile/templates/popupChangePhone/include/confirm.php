<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $phone
 * @var string $oldPhone
 * @var int $userId
 */ ?>
<form class="b-registration__form js-form-validation js-phone-change-two"
      method="post"
      data-url="/ajax/personal/profile/changePhone/">
    <input class="js-data-id js-no-valid" name="ID" value="<?= $userId ?>" type="hidden">
    <input type="hidden" name="action" value="confirmPhone">
    <input type="hidden" name="oldPhone" value="<?= $oldPhone ?>">
    <input type="hidden" name="phone" value="<?= $phone ?>">
    <div class="b-registration__step js-two">
        <div class="b-registration__text b-registration__text--phone" id="js-resend"
             data-url="/ajax/personal/profile/changePhone/"
             data-phone="<?= $phone ?>"
             data-action="resendSms" data-method="post">Ваш номер <span class="js-phone"><?= $phone ?></span>
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
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
            <a class="b-link-gray"
               href="javascript:void(0);"
               title="Отправить снова"
               data-url="/ajax/personal/profile/changePhone/"
               data-phone="<?= $phone ?>"
               data-action="resendSms"
               data-method="post">Отправить снова</a>
        </div>
    </div>
    <a class="b-link b-link--subscribe-delivery js-open-popup js-open-popup--subscribe-delivery js-open-popup"
       href="javascript:void(0)"
       title="Изменить"
       data-popup-id="edit-phone-step"><span class="b-link__text b-link__text--subscribe-delivery js-open-popup">Изменить</span></a>
    <button class="b-button b-button--subscribe-delivery js-sms-step" data-url="/ajax/personal/profile/changePhone/">
        Подтвердить
    </button>
</form>
