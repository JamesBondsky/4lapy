<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsForgotPasswordFormComponent extends \CBitrixComponent
{
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    
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
    }
    
    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $userAuthService = App::getInstance()->getContainer()->get(UserAuthorizationInterface::class);
            if ($userAuthService->isAuthorized()) {
                LocalRedirect('/personal/');
            }
            $this->arResult['STEP'] = 'begin';
            
            /** @todo перешли по ссылке из письма для восстановления пароля */
            if (1 === 2) {
                $this->arResult['EMAIL'] = 'email';
                $this->arResult['STEP']  = 'createNewPassword';
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
        
        if (empty($password) || empty($confirm_password)) {
            return JsonErrorResponse::create('Должны быть заполнены все поля');
        }
        
        if (\strlen($password) < 6) {
            return JsonErrorResponse::create('Пароль должен содержать минимум 6 символов');
        }
        
        if ($password !== $confirm_password) {
            return JsonErrorResponse::create('Пароли не соответсвуют');
        }
        
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $res = $this->currentUserProvider->getUserRepository()->update(
                SerializerBuilder::create()->build()->fromArray(['PASSWORD' => $password], User::class)
            );
            if (!$res) {
                return JsonErrorResponse::create('Произошла ошибка при обновлении');
            }
            
            return JsonSuccessResponse::create('Пароль обновлен');
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::create('Произошла ошибка при обновлении ' . $e->getMessage());
        } catch (ConstraintDefinitionException $e) {
        }
        
        return JsonErrorResponse::create('Непредвиденная ошибка');
    }
    
    /**
     * @param $phone
     *
     * @return JsonResponse
     */
    public function ajaxResendSms($phone) : JsonResponse
    {
        if (PhoneHelper::isPhone($phone)) {
            try {
                $phone = PhoneHelper::normalizePhone($phone);
            } catch (WrongPhoneNumberException $e) {
                return JsonErrorResponse::create($e->getMessage());
            }
        } else {
            return JsonErrorResponse::create(
                'Введен некорректный номер телефона'
            );
        }
        
        try {
            $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::sendConfirmSms($phone);
            if (!$res) {
                return JsonErrorResponse::create(
                    'Ошибка при отправке смс'
                );
            }
        } catch (SmsSendErrorException $e) {
            JsonErrorResponse::create('Ошибка отправки смс, попробуйте позднее');
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::create($e->getMessage());
        } catch (\RuntimeException $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
        } catch (\Exception $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
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
        
        $phone = $request->get('phone', '');
        $email = $request->get('email', '');
        if (empty($step)) {
            $recovery = $request->get('recovery', '');
            if ($recovery === 'phone') {
                $step = 'sendSmsCode';
                $res  = $this->ajaxGetSendSmsCode($phone);
                if ($res instanceof JsonResponse) {
                    return $res;
                }
                
                $phone = $res;
            } elseif ($recovery === 'email') {
                /** @todo отправка письма для верификации */
                $res = $this->ajaxGetSendEmailCode($email);
                if ($res instanceof JsonResponse) {
                    return $res;
                }
                if (is_bool($res) && !$res) {
                    return JsonErrorResponse::create('Отправка письма не удалась, пожалуйста попробуйте позднее');
                }
                $step = 'compileSendEmail';
            } else {
                return JsonErrorResponse::create('Не найдено действие для выполнения');
            }
        }
        
        switch ($step) {
            case 'createNewPassword':
                if (!empty($phone)) {
                    try {
                        $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::checkConfirmSms(
                            $phone,
                            $request->get('confirmCode')
                        );
                        if (!$res) {
                            return JsonErrorResponse::create(
                                'Код подтверждения не соответствует'
                            );
                        }
                    } catch (ExpiredConfirmCodeException $e) {
                        return JsonErrorResponse::create(
                            $e->getMessage()
                        );
                    } catch (WrongPhoneNumberException $e) {
                        return JsonErrorResponse::create(
                            $e->getMessage()
                        );
                    }
                }
                
                /** @noinspection PhpUnusedLocalVariableInspection */
                $login = !empty($phone) ? $phone : $email;
                break;
        }
        
        ob_start();
        /** @noinspection PhpIncludeInspection */
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
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::create($e->getMessage());
        }
        
        try {
            App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::sendConfirmSms($phone);
        } catch (SmsSendErrorException $e) {
            JsonErrorResponse::create('Ошибка отправки смс, попробуйте позднее');
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::create($e->getMessage());
        } catch (\RuntimeException $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
        } catch (\Exception $e) {
            return JsonErrorResponse::create('Непредвиденная ошибка - обратитесь к администратору');
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
            return JsonErrorResponse::create('Введен неверный email');
        }
        
        /** @todo отправка сообщения для верификации по email через expertSender */
        return true;
    }
}
