<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application as App;
use FourPaws\ReCaptcha\ReCaptchaService;

/** @var string $phone */ ?>
<div class="b-registration__content b-registration__content--moiety b-registration__content--step"
     style="padding: 0!important; border: none !important; width: 100% !important;">
    <div class="b-registration__your-number" id="js-resend"
         data-url="/ajax/user/auth/login/"
         data-method="post"
         data-phone="<?= $phone ?>"
         data-action="resendSms">Ваш номер <?= $phone ?>
    </div>
    <form class="b-registration__form js-form-validation js-registration-form js-ajax-form"
          id="reg-step3-form"
          data-url="/ajax/user/auth/login/"
          method="post">
        <input type="hidden" name="action" value="savePhone">
        <input type="hidden" name="phone" value="<?= $phone ?>">
        <div class="b-input-line b-input-line--add-number js-phone3-resend js-resend">
            <div class="b-input-line__label-wrapper">
                <label class="b-input-line__label" for="sms-code-3">SMS-код</label>
            </div>
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="text"
                       id="sms-code-3"
                       placeholder=""
                       name="confirmCode" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
            <a class="b-link-gray"
               href="javascript:void(0);"
               data-url="/ajax/user/auth/register/"
               data-method="post"
               data-phone="<?= $phone ?>"
               data-action="resendSms"
               title="Отправить снова">Отправить снова</a>
        </div>
        <?php /** @var ReCaptchaService $recaptchaService */
        /** @noinspection PhpUnhandledExceptionInspection */
        $recaptchaService = App::getInstance()->getContainer()->get('recaptcha.service');
        echo $recaptchaService->getCaptcha(' b-registration__captcha') ?>
        <div><span class="b-registration__auth-error"></span></div>
        <button class="b-button b-button--social b-button--full-width">Подтвердить</button>
    </form>
</div>