<?php

use Bitrix\Main\Application;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use FourPaws\Helpers\ProtectorHelper;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaInterface;
use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;
/** @var Cmain $APPLICATION */

if ((isset($isAjax) && $isAjax) || $component->getMode() === FourPawsAuthFormComponent::MODE_FORM) {
    $requestUri = Application::getInstance()
        ->getContext()
        ->getRequest()
        ->getRequestUri();
    if (strpos($requestUri, 'sale/order/complete') !== false) {
        $backUrl = '/personal';
    } else {
        $backUrl = !empty($backUrl) ? $backUrl : $requestUri;
    }
    if ($arParams['BACK_URL_HASH'])
    {
        //$backUrl .= '#' . $arParams['BACK_URL_HASH']; // backurl срабатывает, но форма не закрывается и
        // страница не перезагружается (хотя JsonSuccessResponse в www/deploy/release/common/local/components/fourpaws/auth.form/class.php:413 формируется нормально

        // Временное решение вместо # FIXME заменить на #
        if (strpos($backUrl, '?') === false) {
            $backUrl .= '?anchor=' . $arParams['BACK_URL_HASH'];
        }
        else
        {
            $backUrl = preg_replace('/anchor=([^&]*[&]?)/', '', $backUrl) . '&anchor=' . $arParams['BACK_URL_HASH']; //FIXME Сделано для быстроты реализации, нужно сделать более корректный метод установки параметра
        }
    }
    if ($arResult['KIOSK']){
        $backUrl = $arResult['BACK_URL'];
    }
    ?>
    <div class="b-registration b-registration--popup-authorization js-auth-block js-ajax-replace-block" data-registration-popup-authorization="true">
        <header class="b-registration__header">
            <div class="b-title b-title--h1 b-title--registration">Авторизация</div>
            <div class="b-title b-title--h1 b-title--registration-subscribe">Авторизуйтесь на&nbsp;сайте, чтобы оформить подписку</div>
        </header>
        <form class="b-registration__form js-form-validation js-auth-2way"
              data-url="/ajax/user/auth/login-r/"
              method="post">
            <?= bitrix_sessid_post() ?>
            <input type="hidden" name="action" value="login" class="js-no-valid">
            <?php if (!CatalogLandingService::isLandingPage()) { ?>
                <input type="hidden" name="backurl" value="<?= $backUrl ?>" class="js-no-valid">
            <?php } ?>
            <div class="b-input-line b-input-line--popup-authorization">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="tel-email-authorization">
                        Телефон или эл.почта
                    </label>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="text"
                           id="tel-email-authorization"
                           name="login"
                           value="<?= $arResult['LOGIN'] ?>"
                           data-type="telEmail"/>
                    <div class="b-error"><span class="js-message"></span></div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="password-authorization">Пароль</label>
                    <?php if(!$arResult['KIOSK']) { ?>
                        <a class="b-link-gray b-link-gray--label"
                           href="/personal/forgot-password/?backurl=<?= $backUrl ?>"
                           title="Забыли пароль?">Забыли пароль?</a>
                    <?php } ?>
                </div>
                <div class="b-input b-input--registration-form">
                    <input class="b-input__input-field b-input__input-field--registration-form"
                           type="password"
                           id="password-authorization"
                           name="password"/>
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <?php
            if ($arResult['IS_SHOW_CAPTCHA'] && ((int)$_SESSION['COUNT_AUTH_AUTHORIZE'] >= $arResult['LIMIT_AUTH_ATTEMPT'])) {
                try {
                    $recaptchaService = App::getInstance()
                        ->getContainer()
                        ->get(ReCaptchaInterface::class);
                    echo "<script>try {grecaptcha.getResponse()} catch(err) {grecaptcha.render($('.g-recaptcha')[0], {sitekey : $('.g-recaptcha').data('sitekey')})}</script>";
                    echo $recaptchaService->getCaptcha('', true);
                } catch (ApplicationCreateException $e) {
                }
            } ?>
            <div>
                <span class="b-registration__auth-error">
                    <?= ((int)$_SESSION['COUNT_AUTH_AUTHORIZE'] >= $arResult['LIMIT_AUTH_ATTEMPT']) ? 'Неверный логин или пароль' : '' ?>
                </span>
            </div>
            <button class="b-button b-button--social b-button--full-width b-button--popup-authorization">
                Войти
            </button>
            <span class="b-registration__else b-registration__else--authorization">или</span>
            <?php if(!$arResult['KIOSK']) { ?>
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
                       href="/personal/register/?backurl=<?= $backUrl ?>"
                       title="Зарегистрироваться"><span
                                class="b-link__text b-link__text--authorization">Зарегистрироваться</span></a>
                </div>
            <?php } else { ?>
                <div class="b-authorize-by-card">
                    <div class="b-authorize-by-card__text">Поднесите карту к сканеру штрих-кодом</div>
                    <span class="b-icon b-icon--barcode-kiosk">
                        <?= new SvgDecorator('icon-barcode', 51, 37) ?>
                    </span>
                    <span class="b-icon b-icon--arr-barcode-kiosk">
                        <?= new SvgDecorator('icon-arr-barcode', 15, 9) ?>
                    </span>
                </div>
            <?php } ?>

            <?php $token = $arResult['token'] ?? $component->getTokenProvider()->getToken(ProtectorHelper::TYPE_AUTH)->getValue() ?>
            <input type="hidden" name="_csrf" value="<?= $token ?>">
        </form>
    </div>
<?php } ?>
