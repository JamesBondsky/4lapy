<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Location\Model\City;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use JMS\Serializer\SerializerBuilder;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsAuthFormComponent extends \CBitrixComponent
{
    const MODE_PROFILE   = 0;
    
    const MODE_FORM      = 1;
    
    const PHONE_HOT_LINE = '8 (800) 770-00-22';
    
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    
    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthorizationService;
    
    /**
     * FourPawsAuthFormComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \RuntimeException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
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
        $this->currentUserProvider      = $container->get(CurrentUserProviderInterface::class);
        $this->userAuthorizationService = $container->get(UserAuthorizationInterface::class);
    }
    
    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->arResult['STEP'] = 'begin';
            
            $currentUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
            $userAuthService    = App::getInstance()->getContainer()->get(UserAuthorizationInterface::class);
            if ($userAuthService->isAuthorized()) {
                $curUser = $currentUserService->getCurrentUser();
                if (!empty($curUser->getExternalAuthId() && empty($curUser->getPersonalPhone()))) {
                    $this->arResult['STEP'] = 'addPhone';
                }
                else{
                    $this->arResult['NAME'] = $curUser->getName() ?? $curUser->getLogin();
                }
            }
            $this->setSocial();
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
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    protected function setSocial()
    {
        if (Loader::includeModule('socialservices')) {
            $authManager                    = new \CSocServAuthManager();
            $startParams['AUTH_SERVICES']   = false;
            $startParams['CURRENT_SERVICE'] = false;
            $startParams['FORM_TYPE']       = 'login';
            $services                       = $authManager->GetActiveAuthServices($startParams);
            
            if (!empty($services)) {
                $this->arResult['AUTH_SERVICES'] = $services;
                $authServiceId                   =
                    Application::getInstance()->getContext()->getRequest()->get('auth_service_id');
                if ($authServiceId !== ''
                    && isset($authServiceId, $this->arResult['AUTH_SERVICES'][$authServiceId])) {
                    $this->arResult['CURRENT_SERVICE'] = $authServiceId;
                    $authServiceError                  =
                        Application::getInstance()->getContext()->getRequest()->get('auth_service_error');
                    if (!empty($authServiceError)) {
                        $this->arResult['ERROR_MESSAGE'] = $authManager->GetError(
                            $this->arResult['CURRENT_SERVICE'],
                            $authServiceError
                        );
                    } elseif (!$authManager->Authorize($authServiceId)) {
                        global $APPLICATION;
                        $ex = $APPLICATION->GetException();
                        if ($ex) {
                            $this->arResult['ERROR_MESSAGE'] = $ex->GetString();
                        }
                    }
                }
            }
        }
    }
    
    /**
     * @return int
     */
    public function getMode() : int
    {
        return $this->getUserAuthorizationService()->isAuthorized() ? static::MODE_PROFILE : static::MODE_FORM;
    }
    
    /**
     * @return UserAuthorizationInterface
     */
    public function getUserAuthorizationService() : UserAuthorizationInterface
    {
        return $this->userAuthorizationService;
    }
    
    /**
     * @return CurrentUserProviderInterface
     */
    public function getCurrentUserProvider() : CurrentUserProviderInterface
    {
        return $this->currentUserProvider;
    }
    
    /**
     * @param string $rawLogin
     * @param string $password
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function ajaxLogin($rawLogin, $password) : JsonResponse
    {
        $needWritePhone = false;
        try {
            $this->userAuthorizationService->login($rawLogin, $password);
            if ($this->userAuthorizationService->isAuthorized()) {
                $phone = $this->currentUserProvider->getCurrentUser()->getPersonalPhone();
                if (empty($phone)) {
                    $needWritePhone = true;
                }
            }
        } catch (UsernameNotFoundException $e) {
            return JsonErrorResponse::create('Неверный логин или пароль.');
        } catch (InvalidCredentialException $e) {
            return JsonErrorResponse::create('Неверный логин или пароль.');
        } catch (TooManyUserFoundException $e) {
            /** @noinspection PhpUnhandledExceptionInspection */
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $defCity = App::getInstance()->getContainer()->get('location.service')->getDefaultCity();
            if ($defCity instanceof City) {
                $phone = $defCity->getPhone();
            } else {
                $phone = static::PHONE_HOT_LINE;
            }
            
            /** @noinspection PhpUnhandledExceptionInspection */
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $logger = LoggerFactory::create('auth');
            $logger->critical('Найдено больше одного совпадения по логину/email/телефону ' . $rawLogin);
            
            return JsonErrorResponse::create(
                'Обратитесь на горячую линию по телефону ' . $phone
            );
        } catch (\Exception $e) {
            return JsonErrorResponse::create(
                'Системная ошибка при попытке авторизации. Пожалуйста, обратитесь к администратору сайта.'
            );
        }
        
        if (!$needWritePhone) {
            return JsonSuccessResponse::create('Вы успешно авторизованы.', 200, [], ['reload' => true]);
        }
        
        ob_start();
        require_once App::getInstance()->getRootDir()
                     . '/local/components/fourpaws/auth.form/templates/.default/include/addPhone.php';
        $html = ob_get_clean();
        
        return JsonSuccessResponse::createWithData('Необходимо заполнить номер телефона', ['html' => $html]);
    }
    
    /**
     * @param string $phone
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function ajaxResendSms($phone) : JsonResponse
    {
        if (PhoneHelper::isPhone($phone)) {
            $phone = PhoneHelper::normalizePhone($phone);
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
        
        return JsonSuccessResponse::create('Смс успешно отправлено');
    }
    
    /**
     * @param string $phone
     *
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function ajaxSavePhone($phone) : JsonResponse
    {
        $data = ['PERSONAL_PHONE' => $phone];
        
        if ($this->currentUserProvider->getUserRepository()->update(
            SerializerBuilder::create()->build()->fromArray($data, User::class)
        )) {
            /** todo отправить данные в манзану о пользователе */
            /** @var \FourPaws\External\ManzanaService $manzanaService */
            $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
            $manzanaService->updateContact([]);
        }
        
        return JsonSuccessResponse::create('Телефон сохранен', 200, [], ['reload' => true]);
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param  string                                   $phone
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function ajaxGet($request, $phone) : JsonResponse
    {
        $mess = '';
        $step = $request->get('step', '');
        switch ($step) {
            case 'sendSmsCode':
                $mess = $this->ajaxGetSendSmsCode($phone);
                if ($mess instanceof JsonResponse) {
                    return $mess;
                }
                break;
        }
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot() . '/local/components/fourpaws/auth.form/templates/.default/include/' . $step
                     . '.php';
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
     * @param string $phone
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @return \FourPaws\App\Response\JsonResponse|string
     */
    private function ajaxGetSendSmsCode($phone)
    {
        if (PhoneHelper::isPhone($phone)) {
            $phone = PhoneHelper::normalizePhone($phone);
        } else {
            return JsonErrorResponse::create(
                'Введен некорректный номер телефона'
            );
        }
        
        try {
            $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($phone);
        } catch (TooManyUserFoundException $e) {
            return JsonErrorResponse::create(
                'Найдено больше 1 пользователя с данным телефоном, пожалуйста обратитесь к администрации'
            );
        } catch (UsernameNotFoundException $e) {
        }
        
        $mess = '';
        try {
            $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::sendConfirmSms($phone);
            if ($res) {
                $mess = 'Смс успешно отправлено';
            } else {
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
        
        return $mess;
    }
}
