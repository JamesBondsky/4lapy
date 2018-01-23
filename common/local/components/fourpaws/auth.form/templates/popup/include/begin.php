<?php

use Bitrix\Main\Application;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var Cmain $APPLICATION */
?>
<?php if ($component->getMode() === FourPawsAuthFormComponent::MODE_FORM) {
    ?>
    <div class="b-registration b-registration--popup-authorization js-auth-block js-ajax-replace-block">
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration">Авторизация</h1>
        </header>
        <form class="b-registration__form js-form-validation js-auth-2way"
              data-url="/ajax/user/auth/login/"
              method="post">
            <input type="hidden" name="action" value="login" class="js-no-valid">
            <div class="b-input-line b-input-line--popup-authorization">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="tel-email-authorization">Телефон или
                                                                                     эл.почта</label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="text"
                           id="tel-email-authorization"
                           name="login"
                           data-type="telEmail" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="password-authorization">Пароль</label>
                    <a class="b-link-gray b-link-gray--label"
                       href="/personal/forgot-password/?backurl=<?= Application::getInstance()->getContext()->getRequest()->getRequestUri()?>"
                       title="Забыли пароль?">Забыли пароль?</a>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="password"
                           id="password-authorization"
                           name="password" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <div><span class="b-registration__auth-error"></span></div>
            <button class="b-button b-button--social b-button--full-width b-button--popup-authorization">
                Войти
            </button>
            <span class="b-registration__else b-registration__else--authorization">или</span>
            <?php $APPLICATION->IncludeComponent(
                'bitrix:socserv.auth.form',
                'socserv_auth',
                [
                    'AUTH_SERVICES' => $arResult['AUTH_SERVICES'],
                    'AUTH_URL'      => $arResult['AUTH_URL'],
                    'POST'          => $arResult['POST'],
                ],
                $component,
                ['HIDE_ICONS' => 'Y']
            ); ?>
            <div class="b-registration__new-user">Я новый покупатель.
                <a class="b-link b-link--authorization b-link--authorization"
                   href="/personal/register/"
                   title="Зарегистрироваться"><span class="b-link__text b-link__text--authorization">Зарегистрироваться</span></a>
            </div>
        </form>
    </div>
    <?php
} ?>