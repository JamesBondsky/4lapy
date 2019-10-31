<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CBitrixComponent;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\ProtectorHelper;
use FourPawsAuthFormComponent;
use FourPawsRegisterComponent;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthController
 *
 * @package FourPaws\UserBundle\Controller
 * @Route("/auth")
 */
class AuthController extends Controller
{
    /** @var AjaxMess */
    private $ajaxMess;

    /**
     * AuthController constructor.
     *
     * @param AjaxMess $ajaxMess
     */
    public function __construct(AjaxMess $ajaxMess)
    {
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * Метод-заглушка для сбора IP ддосера
     *
     * @Route("/login-r/", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws SystemException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws ApplicationCreateException
     * @throws WrongPhoneNumberException
     */
    public function fakeLoginAction(Request $request): JsonResponse
    {
        $ipLogger = LoggerFactory::create('ddos', 'ddos_logs');
        $ipLogger->info(date('Y-m-d H:i:s') . '. IP: ' . $_SERVER['REMOTE_ADDR'] . '. Request: ' . print_r($_REQUEST, true));

        $tokenProvider = Application::getInstance()->getContainer()->get('security.csrf.token_manager');
        $newToken = $tokenProvider->refreshToken(ProtectorHelper::TYPE_AUTH)->getValue();
        $newTokenResponse = ['token' => $newToken];

        $html = '    <div class="b-registration b-registration--popup-authorization js-auth-block js-ajax-replace-block" data-registration-popup-authorization="true">
        <header class="b-registration__header">
            <div class="b-title b-title--h1 b-title--registration">Авторизация</div>
            <div class="b-title b-title--h1 b-title--registration-subscribe">Авторизуйтесь на&nbsp;сайте, чтобы оформить подписку</div>
        </header>
        <form class="b-registration__form js-form-validation js-auth-2way"
              data-url="/ajax/user/auth/login-o/"
              method="post">
            <input type="hidden" name="sessid" id="sessid" value="' . $_REQUEST['sessid'] . '" />            <input type="hidden" name="action" value="login" class="js-no-valid">
                            <input type="hidden" name="backurl" value="/" class="js-no-valid">
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
                           value="' . $_REQUEST['login'] . '"
                           data-type="telEmail"/>
                    <div class="b-error"><span class="js-message"></span></div>
                </div>
            </div>
            <div class="b-input-line b-input-line--popup-authorization">
                <div class="b-input-line__label-wrapper">
                    <label class="b-input-line__label" for="password-authorization">Пароль</label>
                                            <a class="b-link-gray b-link-gray--label"
                           href="/personal/forgot-password/?backurl=/"
                           title="Забыли пароль?">Забыли пароль?</a>
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
            <script>try {grecaptcha.getResponse()} catch(err) {grecaptcha.render($(\'.g-recaptcha\')[0], {sitekey : $(\'.g-recaptcha\').data(\'sitekey\')})}</script><script data-skip-moving=true async src="https://www.google.com/recaptcha/api.js?hl=ru"></script><div id="" class="g-recaptcha " data-sitekey="6LfS1IQUAAAAAGkc8KI9W1Ki44ek0NOx527Q6h-3" data-callback=""></div>            <div>
                <span class="b-registration__auth-error">
                    Неверный логин или пароль                </span>
            </div>
            <button class="b-button b-button--social b-button--full-width b-button--popup-authorization">
                Войти
            </button>
            <span class="b-registration__else b-registration__else--authorization">или</span>
                            
<ul class="b-registration__social-wrapper b-registration__social-wrapper--authorization">
    </ul>
                <div class="b-registration__new-user">Я новый покупатель.
                    <a class="b-link b-link--authorization b-link--authorization"
                       href="/personal/register/?backurl=/"
                       title="Зарегистрироваться"><span
                                class="b-link__text b-link__text--authorization">Зарегистрироваться</span></a>
                </div>
            
                        <input type="hidden" name="_csrf" value="' . $newToken . '">
        </form>
    </div>
';

        return $this->ajaxMess->getWrongPasswordError(array_merge(
            ['html' => $html],
            $newTokenResponse
        ));
    }

    /**
     * @Route("/login-o/", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws SystemException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws ApplicationCreateException
     * @throws WrongPhoneNumberException
     */
    public function loginAction(Request $request): JsonResponse
    {
        $action = $request->get('action', '');
        if ($action === 'login' && !check_bitrix_sessid()) {
            return JsonErrorResponse::createWithData('Ошибка проверки сессии');
        }
        CBitrixComponent::includeComponentClass('fourpaws:auth.form');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        try {
            $loginClass = new FourPawsAuthFormComponent();
        } catch (SystemException | ServiceNotFoundException | ServiceCircularReferenceException | RuntimeException | Exception $e) {
            return $this->ajaxMess->getSystemError();
        }

        switch ($action) {
            case 'login':
                $response = $loginClass->ajaxLogin(
                    $request->get('login', ''),
                    $request->get('password', ''),
                    $request->get('backurl', ''),
                    $request->get('_csrf', '')
                );

                //if ($response instanceof JsonErrorResponse) {
                    //$response->setStatusCode(418, 'I’m a teapot');
                //}

                return $response;
                break;
            case 'resendSms':
                return $loginClass->ajaxResendSms($request->get('phone', ''));
                break;
            case 'savePhone':
                return $loginClass->ajaxSavePhone($request->get('phone', ''), $request->get('confirmCode', ''),
                    $request->get('backurl', ''));
                break;
            case 'unionBasket':
                return $loginClass->ajaxUnionBasket($request);
                break;
            case 'notUnionBasket':
                return $loginClass->ajaxNotUnionBasket($request);
                break;
            case 'get':
                return $loginClass->ajaxGet($request);
                break;
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/register-r/", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws SystemException
     */
    public function registerAction(Request $request): JsonResponse
    {
        $action = $request->get('action');

        CBitrixComponent::includeComponentClass('fourpaws:register');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        try {
            $registerClass = new FourPawsRegisterComponent();
        } catch (SystemException | ServiceNotFoundException | ServiceCircularReferenceException | RuntimeException | Exception $e) {
            return $this->ajaxMess->getSystemError();
        }

        switch ($action) {
            case 'resendSms':
                return $registerClass->ajaxResendSms($request->get('phone', ''), $request->get(ProtectorHelper::getField(ProtectorHelper::TYPE_REGISTER_SMS_RESEND), false), $request->get('g-recaptcha-response'));
                break;
            case 'register':
                return $registerClass->ajaxRegister($request->request->all());
                break;
            case 'savePhone':
                return $registerClass->ajaxSavePhone($request);
                break;
            case 'get':
                return $registerClass->ajaxGet($request);
                break;
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/forgotPassword/", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function forgotPasswordAction(Request $request): JsonResponse
    {
        $action = $request->get('action', '');

        CBitrixComponent::includeComponentClass('fourpaws:forgotpassword');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        try {
            $forgotPasswordClass = new \FourPawsForgotPasswordFormComponent();
        } catch (SystemException|ServiceNotFoundException|ServiceCircularReferenceException|RuntimeException|Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());

            return $this->ajaxMess->getSystemError();
        }
        switch ($action) {
            case 'savePassword':
                return $forgotPasswordClass->ajaxSavePassword($request);

                break;
            case 'resendSms':
                return $forgotPasswordClass->ajaxResendSms($request->get('phone', ''), $request);

                break;
            case 'get':
                return $forgotPasswordClass->ajaxGet($request);
                break;
        }

        return $this->ajaxMess->getSystemError();
    }
}
