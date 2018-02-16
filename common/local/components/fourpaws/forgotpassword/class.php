<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsForgotPasswordFormComponent extends \CBitrixComponent
{
    const BASKET_BACK_URL = '/cart/';

    const PERSONAL_URL = '/personal/';

    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;

    /** @var UserAuthorizationInterface $authService */
    private $authService;

    /** @var AjaxMess */
    private $ajaxMess;

    /**
     * FourPawsAuthFormComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->authService = $container->get(UserAuthorizationInterface::class);
        $this->ajaxMess = $container->get('ajax.mess');
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            if ($this->authService->isAuthorized()) {
                LocalRedirect(static::PERSONAL_URL);
            }
            $this->arResult['STEP'] = 'begin';

            /** авторизация и показ сообщения об успешной смене */
            $request = Application::getInstance()->getContext()->getRequest();
            $confirmAuth = $request->get('confirm_auth');
            $backUrl = $request->get('backurl');
            if (!empty($confirmAuth)) {
                /** @var ConfirmCodeService $confirmService */
                $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                if ($confirmService::getGeneratedCode() === $confirmAuth) {
                    $this->authService->authorize($request->get('user_id'));
                    if (!empty($backUrl)) {
                        LocalRedirect($backUrl);
                    } else {
                        $this->arResult['STEP'] = 'confirmPhone';
                    }
                }
            }

            /** @todo верификаци по ссылке из email */
            if (1 === 2) {
                if ($backUrl === static::BASKET_BACK_URL) {
                    $confirmAuth = $request->get('confirm_auth');
                    if (!empty($confirmAuth) && !empty($backUrl)) {
                        /** @var ConfirmCodeService $confirmService */
                        $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                        if ($confirmService::getGeneratedCode() === $confirmAuth) {
                            $this->authService->authorize($request->get('user_id'));
                            LocalRedirect($backUrl);
                        }
                    }
                } else {
                    $this->arResult['EMAIL'] = 'email';
                    $this->arResult['STEP'] = 'createNewPassword';
                }
            }

            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxSavePassword(Request $request): JsonResponse
    {
        $password = $request->get('password', '');
        $confirm_password = $request->get('confirmPassword', '');
        $login = $request->get('login', '');

        try {
            $userId = $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($login);
        } catch (TooManyUserFoundException $e) {
            return $this->ajaxMess->getTooManyUserFoundException('', $login);
        } catch (UsernameNotFoundException $e) {
            $userId = 0;
            try {
                $userId = $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($login, false);
                return $this->ajaxMess->getNotActiveUserError();
            } catch (UsernameNotFoundException $e) {
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            } catch (TooManyUserFoundException $e) {
            }
            if ($userId <= 0) {
                return $this->ajaxMess->getUsernameNotFoundException($login);
            }
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }

        if (empty($password) || empty($confirm_password)) {
            return $this->ajaxMess->getEmptyDataError();
        }

        if (\strlen($password) < 6) {
            return $this->ajaxMess->getPasswordLengthError(6);
        }

        if ($password !== $confirm_password) {
            return $this->ajaxMess->getNotEqualPasswordError();
        }

        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $res = $this->currentUserProvider->getUserRepository()->updatePassword($userId, $password);
            if (!$res) {
                return $this->ajaxMess->getUpdateError();
            }

            $res = $this->authService->authorize($userId);

            if (!$res) {
                return $this->ajaxMess->getAuthError();
            }

            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $confirmService::setGeneratedCode('user_' . $userId);

            $backUrl = $request->get('backurl', '');
            $uri = new Uri(static::PERSONAL_URL . 'forgot-password/');
            $uri->addParams(
                [
                    'confirm_auth' => $confirmService::getGeneratedCode(),
                    'user_id'      => $userId,
                    'backurl'      => $backUrl,
                ]
            );

            return JsonSuccessResponse::create(
                'Пароль успешно изменен',
                200,
                [],
                [
                    'redirect' => $uri->getUri(),
                ]
            );
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (\Exception $e) {
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @param $phone
     *
     * @return JsonResponse
     */
    public function ajaxResendSms($phone): JsonResponse
    {
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }

        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $res = $confirmService::sendConfirmSms($phone);
            if (!$res) {
                return $this->ajaxMess->getSmsSendErrorException();
            }
        } catch (SmsSendErrorException $e) {
            return $this->ajaxMess->getSmsSendErrorException();
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (\RuntimeException $e) {
            return $this->ajaxMess->getSystemError();
        } catch (\Exception $e) {
            return $this->ajaxMess->getSystemError();
        }

        return JsonSuccessResponse::create('Смс отправлено');
    }

    /**
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws \Exception
     * @return JsonResponse
     */
    public function ajaxGet($request): JsonResponse
    {
        $step = $request->get('step', '');
        $mess = '';
        /** @noinspection PhpUnusedLocalVariableInspection */
        $backUrl = $request->get('backurl', '');

        $phone = $request->get('phone', '');
        if (!empty($phone)) {
            try {
                $phone = PhoneHelper::normalizePhone($phone);
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            }
        }
        $email = $request->get('email', '');
        $title = 'Восстановление пароля';
        if (empty($step)) {
            $recovery = $request->get('recovery', '');
            if ($recovery === 'phone') {
                $title = 'Восстановление пароля';
                $step = 'sendSmsCode';
                $res = $this->ajaxGetSendSmsCode($phone);
                if ($res instanceof JsonResponse) {
                    return $res;
                }

                $phone = $res;
            } elseif ($recovery === 'email') {
                $title = 'Создание нового пароля';
                /** @todo отправка письма для верификации */
                $res = $this->ajaxGetSendEmailCode($email);
                if ($res instanceof JsonResponse) {
                    return $res;
                }
                if (is_bool($res) && !$res) {
                    return $this->ajaxMess->getEmailSendError();
                }
                $step = 'compileSendEmail';
            } else {
                return $this->ajaxMess->getNoActionError();
            }
        }

        switch ($step) {
            case 'createNewPassword':
                $title = 'Создание нового пароля';
                /** @noinspection PhpUnusedLocalVariableInspection */
                $login = !empty($phone) ? $phone : $email;
                if (!empty($phone)) {
                    try {
                        /** @var ConfirmCodeService $confirmService */
                        $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                        $res = $confirmService::checkConfirmSms(
                            $phone,
                            $request->get('confirmCode')
                        );
                        if (!$res) {
                            return $this->ajaxMess->getWrongConfirmCode();
                        }

                        if ($backUrl === static::BASKET_BACK_URL) {
                            return $this->redirectByBasket($backUrl, $login);
                        }
                    } catch (ExpiredConfirmCodeException $e) {
                        return $this->ajaxMess->getExpiredConfirmCodeException();
                    } catch (WrongPhoneNumberException $e) {
                        return $this->ajaxMess->getWrongPhoneNumberException();
                    } catch (NotFoundConfirmedCodeException $e) {
                        return $this->ajaxMess->getNotFoundConfirmedCodeException();
                    }
                } elseif (!empty($email)) {
                    /** @todo верификация по ссылке из письма */
                    if ($backUrl === static::BASKET_BACK_URL) {
                        return $this->redirectByBasket($backUrl, $login);
                    }
                }

                break;
        }
        $phone = PhoneHelper::formatPhone($phone, '+7 (%s%s%s) %s%s%s-%s%s-%s%s');
        ob_start(); ?>
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration"><?= $title ?></h1>
        </header>
        <?php /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot() . '/local/components/fourpaws/forgotpassword/templates/.default/include/'
            . $step . '.php';
        $html = ob_get_clean();

        return JsonSuccessResponse::createWithData(
            $mess,
            [
                'html'  => $html,
                'step'  => $step,
                'phone' => $phone ?? '',
            ]
        );
    }

    /**
     * @param $phone
     *
     * @return JsonResponse|string
     */
    private function ajaxGetSendSmsCode($phone)
    {
        $users = $this->currentUserProvider->getUserRepository()->findBy(
            [
                '=PERSONAL_PHONE' => $phone,
            ]
        );
        if (count($users) > 1) {
            return $this->ajaxMess->getTooManyUserFoundException('', $phone);
        }

        if (count($users) === 0) {
            return $this->ajaxMess->getUsernameNotFoundException($phone);
        }

        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $confirmService::sendConfirmSms($phone);
        } catch (SmsSendErrorException $e) {
            return $this->ajaxMess->getSmsSendErrorException();
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (\RuntimeException $e) {
            return $this->ajaxMess->getSystemError();
        } catch (\Exception $e) {
            return $this->ajaxMess->getSystemError();
        }

        return $phone;
    }

    /**
     * @param string $email
     *
     * @return bool|JsonResponse
     */
    private function ajaxGetSendEmailCode(string $email)
    {
        //входящая строка, в которой может быть все, что угодно, а должна быть почта
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->ajaxMess->getWrongEmailError();
        }

        $users = $this->currentUserProvider->getUserRepository()->findBy(
            [
                '=EMAIL' => $email,
            ]
        );
        if (count($users) > 1) {
            return $this->ajaxMess->getTooManyUserFoundException('', $email);
        }

        if (count($users) === 0) {
            return $this->ajaxMess->getUsernameNotFoundException($email);
        }

        /** @todo отправка сообщения для верификации по email через expertSender */
        return true;
    }

    /**
     * @param string $backUrl
     * @param string $login
     *
     * @return JsonResponse
     * @throws \Exception
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    private function redirectByBasket(string $backUrl, string $login): JsonResponse
    {
        /** @var ConfirmCodeService $confirmService */
        $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);

        try {
            $userId = $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($login);
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (TooManyUserFoundException $e) {
            return $this->ajaxMess->getTooManyUserFoundException('', $login);
        } catch (UsernameNotFoundException $e) {
            try {
                $userId = $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($login, false);
                if ($userId > 0) {
                    return $this->ajaxMess->getNotActiveUserError();
                }
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            } catch (\Exception $e) {
            }
            return $this->ajaxMess->getUsernameNotFoundException($login);
        }
        $confirmService::setGeneratedCode('user_' . $userId);
        try {
            $generatedCode = $confirmService::getGeneratedCode();
        } catch (Exception $e) {
            $generatedCode = '';
        }

        $uri = new Uri(static::PERSONAL_URL . 'forgot-password/');
        $uri->addParams(
            [
                'confirm_auth' => $generatedCode,
                'user_id'      => $userId,
                'backurl'      => $backUrl,
            ]
        );

        return JsonSuccessResponse::create(
            'Пароль успешно изменен',
            200,
            [],
            [
                'redirect' => $uri->getUri(),
            ]
        );
    }
}
