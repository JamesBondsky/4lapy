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
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Location\Model\City;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

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
        $this->currentUserProvider      = $container->get(CurrentUserProviderInterface::class);
        $this->userAuthorizationService = $container->get(UserAuthorizationInterface::class);
    }
    
    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->arResult['STEP'] = '';
            if ($this->getMode() === static::MODE_FORM) {
                $this->arResult['STEP'] = 'begin';
            }
            
            $currentUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
            $userAuthService    = App::getInstance()->getContainer()->get(UserAuthorizationInterface::class);
            if ($userAuthService->isAuthorized()) {
                $curUser = $currentUserService->getCurrentUser();
                if (!empty($curUser->getExternalAuthId() && empty($curUser->getPersonalPhone()))) {
                    $this->arResult['STEP'] = 'addPhone';
                } else {
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
     * @throws LoaderException
     * @throws SystemException
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
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
     */
    public function ajaxLogin(string $rawLogin, string $password) : JsonResponse
    {
        $needWritePhone = false;
        if (empty($rawLogin)) {
            return JsonErrorResponse::createWithData(
                'Не указан телефон или email',
                ['errors' => ['emptyData' => 'Не указан телефон или email']]
            );
        }
        if (empty($password)) {
            return JsonErrorResponse::createWithData(
                'Не указан пароль',
                ['errors' => ['emptyPassword' => 'Не указан пароль']]
            );
        }
        try {
            $this->userAuthorizationService->login($rawLogin, $password);
            if ($this->userAuthorizationService->isAuthorized()) {
                $phone = $this->currentUserProvider->getCurrentUser()->getPersonalPhone();
                if (empty($phone)) {
                    $needWritePhone = true;
                }
            }
        } catch (UsernameNotFoundException $e) {
            return JsonErrorResponse::createWithData(
                'Неверный логин или пароль',
                ['errors' => ['wrongPassword' => 'Неверный логин или пароль']]
            );
        } catch (InvalidCredentialException $e) {
            return JsonErrorResponse::createWithData(
                'Неверный логин или пароль',
                ['errors' => ['wrongPassword' => 'Неверный логин или пароль']]
            );
        } catch (TooManyUserFoundException $e) {
            /** @noinspection PhpUnhandledExceptionInspection */
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $logger = LoggerFactory::create('auth');
            $logger->critical('Найдено больше одного совпадения по логину/email/телефону ' . $rawLogin);
            
            return JsonErrorResponse::createWithData(
                'Найдено больше одного совпадения, обратитесь на горячую линию по телефону ' . $this->getSitePhone(),
                [
                    'errors' => [
                        'moreOneUser' => 'Найдено больше одного совпадения, обратитесь на горячую линию по телефону'
                                         . $this->getSitePhone(),
                    ],
                ]
            );
        } catch (\Exception $e) {
            return JsonErrorResponse::createWithData(
                'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
                ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
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
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return string
     */
    protected function getSitePhone() : string
    {
        $defCity = App::getInstance()->getContainer()->get('location.service')->getDefaultCity();
        if ($defCity instanceof City) {
            $phone = $defCity->getPhone();
        } else {
            $phone = static::PHONE_HOT_LINE;
        }
        
        return $phone;
    }
    
    /**
     * @param string $phone
     *
     * @throws ServiceNotFoundException
     * @throws WrongPhoneNumberException
     * @return JsonResponse
     */
    public function ajaxResendSms($phone) : JsonResponse
    {
        if (PhoneHelper::isPhone($phone)) {
            $phone = PhoneHelper::normalizePhone($phone);
        } else {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        
        try {
            $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::sendConfirmSms($phone);
            if (!$res) {
                return JsonErrorResponse::createWithData(
                    'Ошибка при отправке смс, попробуйте позднее',
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
                $e->getMessage(),
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
        
        return JsonSuccessResponse::create('Смс успешно отправлено');
    }
    
    /**
     * @param string $phone
     *
     * @param string $confirmCode
     *
     * @throws ContactUpdateException
     * @throws ManzanaServiceException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
     */
    public function ajaxSavePhone(string $phone, string $confirmCode) : JsonResponse
    {
        $container = App::getInstance()->getContainer();
        try {
            $res = $container->get(ConfirmCodeInterface::class)::checkConfirmSms(
                $phone,
                $confirmCode
            );
            if (!$res) {
                return JsonErrorResponse::createWithData(
                    'Код подтверждения не соответствует',
                    ['errors' => ['wrongConfirmCode' => 'Код подтверждения не соответствует']]
                );
            }
        } catch (ExpiredConfirmCodeException $e) {
            return JsonErrorResponse::createWithData(
                $e->getMessage(),
                ['errors' => ['expiredConfirmCode' => $e->getMessage()]]
            );
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                $e->getMessage(),
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        
        $data = [
            'PERSONAL_PHONE'     => $phone,
            'UF_PHONE_CONFIRMED' => 'Y',
        ];
        
        if ($this->currentUserProvider->getUserRepository()->update(
            SerializerBuilder::create()->build()->fromArray($data, User::class)
        )) {
            /** @var ManzanaService $manzanaService */
            $manzanaService = $container->get('manzana.service');
            $contactId      = $manzanaService->getContactIdByPhone($phone);
            if($contactId >= 0) {
                $client = new Client();
                if ($contactId > 0) {
                    $client->contactId = $contactId;
                    $client->phone = $phone;
                }
                else{
                    $this->currentUserProvider->setClientPersonalDataByCurUser($client);
                }
                $manzanaService->updateContact($client);
            }
        }
        
        return JsonSuccessResponse::create('Телефон сохранен', 200, [], ['reload' => true]);
    }
    
    /**
     * @param Request $request
     *
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws WrongPhoneNumberException
     * @return JsonResponse
     */
    public function ajaxGet($request) : JsonResponse
    {
        $mess = '';
        $step = $request->get('step', '');
        $phone = $request->get('phone', '');
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
        include_once App::getDocumentRoot() . '/local/components/fourpaws/auth.form/templates/popup/include/' . $step
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
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws WrongPhoneNumberException
     * @return JsonResponse|string
     */
    private function ajaxGetSendSmsCode($phone)
    {
        if (PhoneHelper::isPhone($phone)) {
            $phone = PhoneHelper::normalizePhone($phone);
        } else {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        
        try {
            $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($phone);
        } catch (TooManyUserFoundException $e) {
            return JsonErrorResponse::createWithData(
                'Найдено больше одного совпадения, обратитесь на горячую линию по телефону' . $this->getSitePhone(),
                [
                    'errors' => [
                        'moreOneUser' => 'Найдено больше одного совпадения, обратитесь на горячую линию по телефону'
                                         . $this->getSitePhone(),
                    ],
                ]
            );
        } catch (UsernameNotFoundException $e) {
        }
        
        $mess = '';
        try {
            $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::sendConfirmSms($phone);
            if ($res) {
                $mess = 'Смс успешно отправлено';
            } else {
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
                $e->getMessage(),
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
        
        return $mess;
    }
}
