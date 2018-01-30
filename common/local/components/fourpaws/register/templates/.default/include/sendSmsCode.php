<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application as App;
use FourPaws\ReCaptcha\ReCaptchaService;

/** @var string $phone
 * @var string $newAction
 */ ?>
<div class="b-registration__content b-registration__content--moiety b-registration__content--step">
    <div class="b-step-form b-step-form--add-number">Шаг <span>1</span> из <span>2</span>
    </div>
    <div class="b-registration__your-number" id="js-resend"
         data-url="/ajax/user/auth/register/"
         data-phone="<?= $phone ?>"
         data-action="resendSms">Ваш номер <?= $phone ?>
    </div>
    <a class="b-link-gray b-link-gray--add-number js-else-phone"
       href="javascript:void(0);"
       title="Сменить номер"
       data-url="/ajax/user/auth/register/"
       data-action="get"
       data-step="<?=!empty($newAction) ? 'addPhone' : 'step1'?>"
       data-phone="<?= $phone ?>">Сменить номер</a>
    <form class="b-registration__form js-form-validation js-registration-form"
          id="reg-step3-form"
          data-url="/ajax/user/auth/register/"
          method="post">
        <input type="hidden" name="action" value="<?= !empty($newAction) ? $newAction : 'get' ?>">
        <input type="hidden" name="step" value="step2">
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
               data-phone="<?= $phone ?>"
               data-action="resendSms"
               title="Отправить снова">Отправить снова</a>
        </div>
        <?php /** @var ReCaptchaService $recaptchaService */
        /** @noinspection PhpUnhandledExceptionInspection */
        $recaptchaService = App::getInstance()->getContainer()->get('recaptcha.service');
        echo $recaptchaService->getCaptcha(' b-registration__captcha') ?>
        <button class="b-button b-button--social b-button--full-width">Подтвердить</button>
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