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
use FourPaws\ConfirmCode\Exception\ExpiredConfirmCodeException;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;
use JMS\Serializer\SerializerBuilder;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsRegisterComponent extends \CBitrixComponent
{
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    
    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthorizationService;
    
    /**
     * @var UserRegistrationProviderInterface
     */
    private $userRegistrationService;
    
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
        $this->userRegistrationService  = $container->get(UserRegistrationProviderInterface::class);
    }
    
    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->arResult['STEP'] = 'begin';
            
            if ($this->userAuthorizationService->isAuthorized()) {
                $curUser = $this->currentUserProvider->getCurrentUser();
                if (!empty($curUser->getExternalAuthId() && empty($curUser->getPersonalPhone()))) {
                    $this->arResult['STEP'] = 'addPhone';
                } else {
                    LocalRedirect('/personal/');
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
     * @param string $phone
     *
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
            $res = App::getInstance()->getContainer()->get('confirm_code.service')::sendConfirmSms($phone);
            if (!$res) {
                return JsonErrorResponse::create(
                    'Ошибка отправки смс, попробуйте позднее'
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
     * @param array $data
     *
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function ajaxRegister($data) : JsonResponse
    {
        if (!empty($data['PERSONAL_PHONE'])) {
            $data['LOGIN'] = $data['PERSONAL_PHONE'];
        } elseif (!empty($data['EMAIL'])) {
            $data['LOGIN'] = $data['EMAIL'];
        }
        
        try {
            $res = $this->userRegistrationService->register(
                SerializerBuilder::create()->build()->fromArray($data, User::class)
            );
            if (!$res) {
                return JsonErrorResponse::create('При регистрации произошла ошибка');
            }
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::create('При регистрации произошла ошибка - ' . $e->getMessage());
        }
        
        /** todo отправить данные в манзану о пользователе */
        /** @var \FourPaws\External\ManzanaService $manzanaService */
        $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
        $manzanaService->updateContact([]);
        
        return JsonSuccessResponse::create('Регистрация прошла успешно');
    }
    
    /**
     * @param array $data
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
    public function ajaxSavePhone($data) : JsonResponse
    {
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
     * @param string                                    $phone
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \Exception
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function ajaxGet($request, $phone) : JsonResponse
    {
        $step = $request->get('step', '');
        $mess = '';
        switch ($step) {
            case 'step1':
            case 'addPhone':
                break;
            case 'step2':
                $mess = $this->ajaxGetStep2($request->get('confirmCode'), $phone);
                if ($mess instanceof JsonResponse) {
                    return $mess;
                }
                break;
            case 'authByPhone':
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $phone = $this->ajaxGetAuthByPhone($phone);
                if ($phone instanceof JsonResponse) {
                    return $phone;
                }
                break;
            case 'sendSmsCode':
                /** @noinspection PhpUnusedLocalVariableInspection */
                $newAction = $request->get('newAction');
                $res       = $this->ajaxGetSendSmsCode($phone);
                if ($res instanceof JsonResponse) {
                    return $res;
                }
                
                if (is_array($res)) {
                    if (!empty($res['mess'])) {
                        $mess = $res['mess'];
                    }
                    if (!empty($res['step'])) {
                        $step = $res['step'];
                    }
                }
                break;
        }
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot() . '/local/components/fourpaws/register/templates/.default/include/' . $step
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
     * @param string $confirmCode
     * @param string $phone
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @return \FourPaws\App\Response\JsonResponse|string
     */
    private function ajaxGetStep2($confirmCode, $phone)
    {
        try {
            $res = App::getInstance()->getContainer()->get('confirm_code.service')::checkConfirmSms(
                $phone,
                (string)$confirmCode
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
        $mess = 'Смс прошло проверку';
        
        /** @var \FourPaws\External\ManzanaService $manzanaService */
        $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
        $manzanaData    = $manzanaService->getUserDataByPhone($phone);
        /** @var \Doctrine\Common\Collections\ArrayCollection $clients */
        $clients = $manzanaData->clients;
        if ($clients instanceof Client) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $manzanaItem = $clients;
        } else {
            $clientsList = $clients->toArray();
            if (\is_array($clientsList) && \count($clientsList) === 1) {
                /** @noinspection PhpUnusedLocalVariableInspection */
                /** @var \FourPaws\External\Manzana\Model\Client $manzanaItem */
                $manzanaItem = current($clientsList);
            }
        }
        
        return $mess;
    }
    
    /**
     * @param string $phone
     *
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @return \FourPaws\App\Response\JsonResponse|string
     */
    private function ajaxGetAuthByPhone($phone)
    {
        if (PhoneHelper::isPhone($phone)) {
            $phone = PhoneHelper::normalizePhone($phone);
        } else {
            return JsonErrorResponse::create(
                'Введен некорректный номер телефона'
            );
        }
        
        return $phone;
    }
    
    /**
     * @param string $phone
     *
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @return array|\FourPaws\App\Response\JsonResponse
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
        $mess = '';
        $step = '';
        
        $id = 0;
        try {
            $id = $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($phone);
        } catch (TooManyUserFoundException $e) {
            return JsonErrorResponse::create(
                'Найдено больше 1 пользователя с данным телефоном, пожалуйста обратитесь к администрации'
            );
        } catch (UsernameNotFoundException $e) {
        }
        
        if ($id > 0) {
            $step = 'authByPhone';
        } else {
            /** @noinspection PhpUnusedLocalVariableInspection */
            
            try {
                $res = App::getInstance()->getContainer()->get('confirm_code.service')::sendConfirmSms($phone);
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
        }
        
        return [
            'mess' => $mess,
            'step' => $step,
        ];
    }
}
