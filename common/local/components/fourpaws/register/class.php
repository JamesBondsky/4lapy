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
use FourPaws\External\Manzana\Model\Clients;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Location\Model\City;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;
use GuzzleHttp\Exception\GuzzleException;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsRegisterComponent extends \CBitrixComponent
{
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
     * @var UserRegistrationProviderInterface
     */
    private $userRegistrationService;
    
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
     * @param string $phone
     *
     * @throws WrongPhoneNumberException
     * @return JsonResponse
     */
    public function ajaxResendSms($phone) : JsonResponse
    {
        if (PhoneHelper::isPhone($phone)) {
            $phone = PhoneHelper::normalizePhone($phone);
        } else {
            return JsonErrorResponse::createWithData(
                'Введен некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        
        try {
            $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::sendConfirmSms($phone);
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
        
        return JsonSuccessResponse::create('Смс успешно отправлено');
    }
    
    /**
     * @param array $data
     *
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ManzanaServiceException
     * @throws ContactUpdateException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @return JsonResponse
     */
    public function ajaxRegister($data) : JsonResponse
    {
        if (!empty($data['PERSONAL_PHONE'])) {
            $data['LOGIN'] = $data['PERSONAL_PHONE'];
        } elseif (!empty($data['EMAIL'])) {
            $data['LOGIN'] = $data['EMAIL'];
        }
        
        $data['UF_PHONE_CONFIRMED'] = 'Y';
        
        $userEntity = SerializerBuilder::create()->build()->fromArray($data, User::class);
        try {
            $res = $this->userRegistrationService->register($userEntity);
            if (!$res) {
                return JsonErrorResponse::createWithData(
                    'При регистрации произошла ошибка',
                    ['errors' => ['registerError' => 'При регистрации произошла ошибка']]
                );
            }
        } catch (BitrixRuntimeException $e) {
            return JsonErrorResponse::createWithData(
                'При регистрации произошла ошибка - ' . $e->getMessage(),
                [
                    'errors' => [
                        'registerError' => 'При регистрации произошла ошибка - ' . $e->getMessage(),
                    ],
                ]
            );
        }
        
        /** @var ManzanaService $manzanaService */
        $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
        $contactId      = $manzanaService->getContactIdByCurUser($userEntity);
        $client         = new Client();
        if ($contactId > 0) {
            $client->contactId = $contactId;
        }
        $this->currentUserProvider->setClientPersonalDataByCurUser($client, $userEntity);
        $manzanaService->updateContact($client);
        
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
                     . '/local/components/fourpaws/register/templates/.default/include/confirm.php';
        $html = ob_get_clean();
        
        return JsonSuccessResponse::createWithData(
            'Регистрация прошла успешно',
            [
                'html' => $html,
            ]
        );
    }
    
    /**
     * @param Request $request
     *
     * @throws NotAuthorizedException
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
    public function ajaxSavePhone(Request $request) : JsonResponse
    {
        $phone       = $request->get('phone', '');
        $confirmCode = $request->get('confirmCode', '');
        try {
            $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::checkConfirmSms(
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
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        
        $data = [
            'UF_PHONE_CONFIRMED' => 'Y',
            'PERSONAL_PHONE'     => $phone,
        ];
        if ($this->currentUserProvider->getUserRepository()->update(
            SerializerBuilder::create()->build()->fromArray($data, User::class)
        )) {
            /** @var ManzanaService $manzanaService */
            $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
            $contactId      = $manzanaService->getContactIdByCurUser();
            $client         = new Client();
            if ($contactId > 0) {
                $client->contactId = $contactId;
            }
            $this->currentUserProvider->setClientPersonalDataByCurUser($client);
            $manzanaService->updateContact($client);
        }
        
        return JsonSuccessResponse::create('Телефон сохранен', 200, [], ['reload' => true]);
    }
    
    /**
     * @param Request $request
     *
     * @throws \RuntimeException
     * @throws GuzzleException
     * @throws SystemException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ManzanaServiceException
     * @throws WrongPhoneNumberException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
     */
    public function ajaxGet($request) : JsonResponse
    {
        $step  = $request->get('step', '');
        $phone = $request->get('phone', '');
        $mess  = '';
        $title = 'Регистрация';
        switch ($step) {
            case 'step1':
            case 'addPhone':
                break;
            case 'step2':
                $mess = $this->ajaxGetStep2($request->get('confirmCode', ''), $phone);
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
        ob_start();?>
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration"><?= $title ?></h1>
        </header>
        <?php/** @noinspection PhpIncludeInspection */
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
     * @throws \RuntimeException
     * @throws GuzzleException
     * @throws SystemException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ManzanaServiceException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse|string
     */
    private function ajaxGetStep2($confirmCode, $phone)
    {
        if (!App::getInstance()->getContainer()->get('recaptcha.service')->checkCaptcha()) {
            return JsonErrorResponse::create(
                'Проверка капчей не пройдена'
            );
        }
        try {
            $res = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class)::checkConfirmSms(
                $phone,
                (string)$confirmCode
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
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        $mess = 'Смс прошло проверку';
        
        /** @var ManzanaService $manzanaService */
        $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
        $manzanaData    = $manzanaService->getUserDataByPhone($phone);
        /** @var Clients $clients */
        $clients = $manzanaData->clients;
        if (\is_array($clients) && \count($clients) === 1) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            /** @var Client $manzanaItem */
            $manzanaItem = current($clients);
        }
        
        return $mess;
    }
    
    /**
     * @param string $phone
     *
     * @throws WrongPhoneNumberException
     * @return JsonResponse|string
     */
    private function ajaxGetAuthByPhone($phone)
    {
        if (PhoneHelper::isPhone($phone)) {
            $phone = PhoneHelper::normalizePhone($phone);
        } else {
            return JsonErrorResponse::createWithData(
                'Введен некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        
        return $phone;
    }
    
    /**
     * @param string $phone
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws WrongPhoneNumberException
     * @return array|JsonResponse
     */
    private function ajaxGetSendSmsCode($phone)
    {
        if (PhoneHelper::isPhone($phone)) {
            $phone = PhoneHelper::normalizePhone($phone);
        } else {
            return JsonErrorResponse::createWithData(
                'Введен некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        $mess = '';
        $step = '';
        
        $id = 0;
        try {
            $id = $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($phone);
        } catch (TooManyUserFoundException $e) {
            return JsonErrorResponse::createWithData(
                'Найдено больше одного совпадения, обратитесь на горячую линию по телефону ' . $this->getSitePhone(),
                [
                    'errors' => [
                        'moreOneUser' => 'Найдено больше одного совпадения, обратитесь на горячую линию по телефону '
                                         . $this->getSitePhone(),
                    ],
                ]
            );
        } catch (UsernameNotFoundException $e) {
        }
        
        if ($id > 0) {
            $step = 'authByPhone';
        } else {
            /** @noinspection PhpUnusedLocalVariableInspection */
            
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
                JsonErrorResponse::createWithData(
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
        }
        
        return [
            'mess' => $mess,
            'step' => $step,
        ];
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
}
