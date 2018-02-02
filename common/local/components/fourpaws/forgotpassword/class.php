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
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
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
    
    const PERSONAL_URL    = '/personal/';
    
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    
    /** @var UserAuthorizationInterface $authService */
    private $authService;
    
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
        $this->authService         = $container->get(UserAuthorizationInterface::class);
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
            $request     = Application::getInstance()->getContext()->getRequest();
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
                if($backUrl === static::BASKET_BACK_URL){
                    $confirmAuth = $request->get('confirm_auth');
                    if (!empty($confirmAuth) && !empty($backUrl)) {
                        /** @var ConfirmCodeService $confirmService */
                        $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                        if ($confirmService::getGeneratedCode() === $confirmAuth) {
                            $this->authService->authorize($request->get('user_id'));
                            LocalRedirect($backUrl);
                        }
                    }
                }
                else {
                    $this->arResult['EMAIL'] = 'email';
                    $this->arResult['STEP']  = 'createNewPassword';
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
    public function ajaxSavePassword(Request $request) : JsonResponse
    {
        $password         = $request->get('password', '');
        $confirm_password = $request->get('confirmPassword', '');
        $login            = $request->get('login', '');
        
        try {
            $userId = $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($login);
        } catch (TooManyUserFoundException $e) {
            return JsonErrorResponse::createWithData(
                'Найдено больше одного пользователя с данным логином ' . $login,
                ['errors' => ['moreOneUser' => 'Найдено больше одного пользователя с данным логином ' . $login]]
            );
        } catch (UsernameNotFoundException $e) {
            return JsonErrorResponse::createWithData(
                'Не найдено пользователей с данным логином ' . $login,
                ['errors' => ['noUser' => 'Не найдено пользователей с данным логином ' . $login]]
            );
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        
        if (empty($password) || empty($confirm_password)) {
            return JsonErrorResponse::createWithData(
                'Должны быть заполнены все поля',
                ['errors' => ['emptyData' => 'Должны быть заполнены все поля']]
            );
        }
        
        if (\strlen($password) < 6) {
            return JsonErrorResponse::createWithData(
                'Пароль должен содержать минимум 6 символов',
                ['errors' => ['errorValidMinLengthPassword' => 'Пароль должен содержать минимум 6 символов']]
            );
        }
        
        if ($password !== $confirm_password) {
            return JsonErrorResponse::createWithData(
                'Пароли не соответсвуют',
                ['errors' => ['notEqualPassword' => 'Пароли не соответсвуют']]
            );
        }
        
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $res = $this->currentUserProvider->getUserRepository()->updatePassword($userId, $password);
            if (!$res) {
                return JsonErrorResponse::createWithData(
                    'Произошла ошибка при обновлении',
                    ['errors' => ['errorUpdate' => 'Произошла ошибка при обновлении']]
                );
            }
            
            $res = $this->authService->authorize($userId);
            
            if (!$res) {
                return JsonErrorResponse::createWithData(
                    'Произошла ошибка при авторизации',
                    ['errors' => ['errorAuth' => 'Произошла ошибка при авторизации']]
                );
            }
            
            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $confirmService::setGeneratedCode('user_' . $userId);
            
            $backUrl = $request->get('backurl', '');
            $uri     = new Uri(static::PERSONAL_URL . 'forgot-password/');
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
            return JsonErrorResponse::createWithData(
                'Произошла ошибка при обновлении ' . $e->getMessage(),
                ['errors' => ['errorUpdate' => 'Произошла ошибка при обновлении ' . $e->getMessage()]]
            );
        } catch (\Exception $e) {
        }
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта ' . $e->getMessage(),
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @param $phone
     *
     * @return JsonResponse
     */
    public function ajaxResendSms($phone) : JsonResponse
    {
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        
        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $res            = $confirmService::sendConfirmSms($phone);
            if (!$res) {
                return JsonErrorResponse::createWithData(
                    'Ошибка отправки смс, попробуйте позднее',
                    ['errors' => ['errorSmsSend' => 'Ошибка отправки смс, попробуйте позднее']]
                );
            }
        } catch (SmsSendErrorException $e) {
            return JsonErrorResponse::createWithData(
                'Ошибка отправки смс, попробуйте позднее',
                ['errors' => ['errorSmsSend' => 'Ошибка отправки смс, попробуйте позднее']]
            );
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        } catch (\RuntimeException $e) {
            return JsonErrorResponse::createWithData(
                'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
                ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
            );
        } catch (\Exception $e) {
            return JsonErrorResponse::createWithData(
                'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
                ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
            );
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
    public function ajaxGet($request) : JsonResponse
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
                return JsonErrorResponse::createWithData(
                    'Некорректный номер телефона',
                    ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
                );
            }
        }
        $email = $request->get('email', '');
        $title = 'Восстановление пароля';
        if (empty($step)) {
            $recovery = $request->get('recovery', '');
            if ($recovery === 'phone') {
                $title = 'Восстановление пароля';
                $step  = 'sendSmsCode';
                $res   = $this->ajaxGetSendSmsCode($phone);
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
                    return JsonErrorResponse::createWithData(
                        'Отправка письма не удалась, пожалуйста попробуйте позднее',
                        ['errors' => ['errorEmailSend' => 'Отправка письма не удалась, пожалуйста попробуйте позднее']]
                    );
                }
                $step = 'compileSendEmail';
            } else {
                return JsonErrorResponse::createWithData(
                    'Не найдено действие для выполнения',
                    ['errors' => ['noAction' => 'Не найдено действие для выполнения']]
                );
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
                        $res            = $confirmService::checkConfirmSms(
                            $phone,
                            $request->get('confirmCode')
                        );
                        if (!$res) {
                            return JsonErrorResponse::createWithData(
                                'Код подтверждения не соответствует',
                                ['errors' => ['wrongConfirmCode' => 'Код подтверждения не соответствует']]
                            );
                        }
                        
                        if ($backUrl === static::BASKET_BACK_URL) {
                            return $this->redirectByBasket($backUrl, $login);
                        }
                    } catch (ExpiredConfirmCodeException $e) {
                        return JsonErrorResponse::createWithData(
                            $e->getMessage(),
                            ['errors' => ['expiredConfirmCode' => $e->getMessage()]]
                        );
                    } catch (WrongPhoneNumberException $e) {
                        return JsonErrorResponse::createWithData(
                            'Некорректный номер телефона',
                            ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
                        );
                    } catch (NotFoundConfirmedCodeException $e) {
                        return JsonErrorResponse::createWithData(
                            $e->getMessage(),
                            ['errors' => ['notFoundConfirmCode' => $e->getMessage()]]
                        );
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
            return JsonErrorResponse::createWithData(
                'Пользователей с номером ' . $phone . ' найдено больше 1, пожалуйста обратитесь к администрации сайта',
                [
                    'errors' => [
                        'moreOneUsersByPhone' => 'Пользователей с номером ' . $phone
                                                 . ' найдено больше 1, пожалуйста обратитесь к администрации сайта',
                    ],
                ]
            );
        }
        
        if (count($users) === 0) {
            return JsonErrorResponse::createWithData(
                'Пользователей с номером ' . $phone . ' не найдено',
                ['errors' => ['notFoundUsers' => 'Пользователей с номером ' . $phone . ' не найдено']]
            );
        }
        
        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $confirmService::sendConfirmSms($phone);
        } catch (SmsSendErrorException $e) {
            return JsonErrorResponse::createWithData(
                'Ошибка отправки смс, попробуйте позднее',
                ['errors' => ['errorSmsSend' => 'Ошибка отправки смс, попробуйте позднее']]
            );
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        } catch (\RuntimeException $e) {
            return JsonErrorResponse::createWithData(
                'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
                ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
            );
        } catch (\Exception $e) {
            return JsonErrorResponse::createWithData(
                'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
                ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
            );
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
            return JsonErrorResponse::createWithData(
                'Введен неверный email',
                ['errors' => ['wrongEmail' => 'Введен неверный email']]
            );
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
    private function redirectByBasket(string $backUrl, string $login) : JsonResponse
    {
        /** @var ConfirmCodeService $confirmService */
        $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
        
        try {
            $userId = $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($login);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        } catch (TooManyUserFoundException $e) {
            return JsonErrorResponse::createWithData(
                'Найдено больше одного пользователя с данным логином ' . $login,
                ['errors' => ['moreOneUser' => 'Найдено больше одного пользователя с данным логином ' . $login]]
            );
        } catch (UsernameNotFoundException $e) {
            return JsonErrorResponse::createWithData(
                'Не найдено пользователей с данным логином ' . $login,
                ['errors' => ['noUser' => 'Не найдено пользователей с данным логином ' . $login]]
            );
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
