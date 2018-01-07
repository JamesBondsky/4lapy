<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $phone */ ?>
<div class="b-registration__content b-registration__content--moiety b-registration__content--step">
    <div class="b-step-form b-step-form--add-number">Шаг <span>1</span> из <span>2</span>
    </div>
    <div class="b-registration__your-number">Ваш номер <span id="js-resend"
                                                             data-url="/ajax/user/auth/login/"
                                                             data-phone="<?= $phone ?>"
                                                             data-action="resendSms"><?= $phone ?></span>
    </div>
    <a class="b-link-gray b-link-gray--add-number js-else-phone"
       href="javascript:void(0);"
       title="Сменить номер"
       data-url="/ajax/user/auth/login/"
       data-action="get"
       data-step="addPhone"
       data-phone="<?= $phone ?>">Сменить номер</a>
    <form class="b-registration__form js-form-validation js-registration-form"
          id="reg-step3-form"
          data-url="/ajax/user/auth/login/"
          method="post">
        <input type="hidden" name="action" value="savePhone">
        <input type="hidden" name="phone" value="<?= $phone ?>">
        <div class="b-input-line b-input-line--add-number js-phone3-resend js-resend">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="sms-code-3">SMS-код</label>
            </div>
            <div class="b-input b-input--registration-form b-kek">
                <input class="b-input__input-field b-input__input-field--registration-form b-kek"
                       type="text"
                       id="sms-code-3"
                       placeholder=""
                       name="confirmCode" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
            <a class="b-link-gray"
               href="javascript:void(0);"
               data-url="/ajax/user/auth/login/"
               data-phone="<?= $phone ?>"
               data-action="resendSms"
               title="Отправить снова">Отправить снова</a>
        </div>
        <div class="b-registration__captcha" data-sitekey=""></div>
        <button class="b-button b-button--social b-button--full-width" type="submit">Подтвердить</button>
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