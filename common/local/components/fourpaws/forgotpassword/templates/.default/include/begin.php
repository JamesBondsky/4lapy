<?php

use Bitrix\Main\Application;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaInterface;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $phone */ ?>
<div class="b-registration__content b-registration__content--create-password">
    <div class="b-registration__text-instruction b-registration__text-instruction--create-password">Для восстановления
                                                                                                    пароля, введите
                                                                                                    телефон или почту,
                                                                                                    которую вы указывали
                                                                                                    при регистрации
    </div>
    <form class="b-registration__form js-form-validation js-recovery-form"
          data-url="/ajax/user/auth/forgotPassword/"
          method="post">
        <input type="hidden" name="action" value="get">
        <input type="hidden" name="backurl" value="<?= Application::getInstance()->getContext()->getRequest()->get('backurl')?>">
        <div class="b-choice-recovery">
            <input class="b-choice-recovery__input js-recovery-telephone js-no-valid"
                   id="registration-recovery-telephone"
                   name="recovery"
                   checked="checked"
                   data-url="/ajax/user/auth/forgotPassword/"
                   type="radio"
                   value="phone" />
            <label class="b-choice-recovery__label b-choice-recovery__label--left"
                   for="registration-recovery-telephone">Телефон</label>
            <input class="b-choice-recovery__input js-recovery-email js-no-valid"
                   id="registration-recovery-email"
                   data-url="/ajax/user/auth/forgotPassword/"
                   type="radio"
                   value="email"
                   name="recovery" />
            <label class="b-choice-recovery__label b-choice-recovery__label--right" for="registration-recovery-email">Почта</label>
        </div>
        <div class="b-input-line b-input-line--create-password b-input-line--recovery js-telephone-recovery">
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form"
                       type="tel"
                       id="registration-tel-recovery"
                       name="phone"
                       value="<?= $phone ?>"
                       placeholder="" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <div class="b-input-line b-input-line--create-password b-input-line--recovery js-email-recovery">
            <div class="b-input b-input--registration-form">
                <input class="b-input__input-field b-input__input-field--registration-form js-no-valid"
                       type="email"
                       id="registration-email-recovery"
                       placeholder=""
                       name="email" />
                <div class="b-error"><span class="js-message"></span>
                </div>
            </div>
        </div>
        <?
        try {
            $recaptchaService = App::getInstance()->getContainer()->get(ReCaptchaInterface::class);
            echo $recaptchaService->getCaptcha('', true);
        } catch (ApplicationCreateException $e) {
        }
        ?>
        <br>
        <button class="b-button b-button--social b-button--full-width">Далее</button>
    </form>
</div>
