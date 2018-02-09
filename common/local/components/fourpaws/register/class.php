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
use FourPaws\AppBundle\Serialization\ArrayOrFalseHandler;
use FourPaws\AppBundle\Serialization\BitrixBooleanHandler;
use FourPaws\AppBundle\Serialization\BitrixDateHandler;
use FourPaws\AppBundle\Serialization\BitrixDateTimeHandler;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Location\Model\City;
use FourPaws\ReCaptcha\ReCaptchaService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;
use GuzzleHttp\Exception\GuzzleException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer;
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
    
    /** @var Serializer */
    private $serializer;
    
    /**
     * FourPawsAuthFormComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws RuntimeException
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
        $this->serializer               = SerializerBuilder::create()->configureHandlers(
            function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new BitrixDateHandler());
                $registry->registerSubscribingHandler(new BitrixDateTimeHandler());
                $registry->registerSubscribingHandler(new BitrixBooleanHandler());
                $registry->registerSubscribingHandler(new ArrayOrFalseHandler());
            }
        )->build();
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
     * @return JsonResponse
     */
    public function ajaxResendSms($phone) : JsonResponse
    {
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Введен некорректный номер телефона',
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
        
        return JsonSuccessResponse::create('Смс успешно отправлено');
    }
    
    /**
     * @param array $data
     *
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @return JsonResponse
     */
    public function ajaxRegister($data) : JsonResponse
    {
        if (!empty($data['PERSONAL_PHONE'])) {
            try {
                $data['PERSONAL_PHONE'] = PhoneHelper::normalizePhone($data['PERSONAL_PHONE']);
            } catch (WrongPhoneNumberException $e) {
                return JsonErrorResponse::createWithData(
                    'Некорректный номер телефона',
                    ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
                );
            }
            $data['LOGIN'] = $data['PERSONAL_PHONE'];
        } elseif (!empty($data['EMAIL'])) {
            $data['LOGIN'] = $data['EMAIL'];
        }
        
        /** @var UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $haveUsers      = $userRepository->havePhoneAndEmailByUsers(
            [
                'PERSONAL_PHONE' => $data['PERSONAL_PHONE'],
                'EMAIL'          => $data['EMAIL'],
            ]
        );
        if ($haveUsers['email']) {
            return JsonErrorResponse::createWithData(
                'Такой email уже существует',
                ['errors' => ['haveEmail' => 'Такой email уже существует']]
            );
        }
        if ($haveUsers['phone']) {
            return JsonErrorResponse::createWithData(
                'Такой телефон уже существует',
                ['errors' => ['havePhone' => 'Такой телефон уже существует']]
            );
        }
        
        $data['UF_PHONE_CONFIRMED'] = 'Y';
        
        /** @var User $userEntity */
        $userEntity = $this->serializer->fromArray(
                $data,
                User::class,
            DeserializationContext::create()->setGroups('create')
        );
        try {
            $res = $this->userRegistrationService->register($userEntity, true);
            if (!$res) {
                return JsonErrorResponse::createWithData(
                    'При регистрации произошла ошибка',
                    ['errors' => ['registerError' => 'При регистрации произошла ошибка']]
                );
            }

            /** @noinspection PhpUnusedLocalVariableInspection */
            $name = $userEntity->getName();
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
    }
    
    /**
     * @param Request $request
     *
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
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                $e->getMessage(),
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $res            = $confirmService::checkConfirmSms(
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
        } catch (NotFoundConfirmedCodeException $e) {
            return JsonErrorResponse::createWithData(
                $e->getMessage(),
                ['errors' => ['notFoundConfirmCode' => $e->getMessage()]]
            );
        }
        
        $data = [
            'UF_PHONE_CONFIRMED' => 'Y',
            'PERSONAL_PHONE'     => $phone,
        ];
        if ($this->currentUserProvider->getUserRepository()->updateData($this->currentUserProvider->getCurrentUserId(), $data)) {
            /** @var ManzanaService $manzanaService */
            $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
            $client         = null;
            try {
                $contactId         = $manzanaService->getContactIdByCurUser();
                $client            = new Client();
                $client->contactId = $contactId;
            } catch (ManzanaServiceException $e) {
                $client = new Client();
            }
    
            if ($client instanceof Client) {
                $this->currentUserProvider->setClientPersonalDataByCurUser($client);
                $manzanaService->updateContactAsync($client);
            }
        }
        
        return JsonSuccessResponse::create('Телефон сохранен', 200, [], ['reload' => true]);
    }
    
    /**
     * @param Request $request
     *
     * @throws SystemException
     * @throws \RuntimeException
     * @throws GuzzleException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
     */
    public function ajaxGet($request) : JsonResponse
    {
        $step  = $request->get('step', '');
        $phone = $request->get('phone', '');
        if (!empty($phone)) {
            try {
                $phone = PhoneHelper::normalizePhone($phone);
            } catch (WrongPhoneNumberException $e) {
                return JsonErrorResponse::createWithData(
                    $e->getMessage(),
                    ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
                );
            }
        }
        $mess  = '';
        $title = 'Регистрация';
        switch ($step) {
            case 'step2':
                $mess = $this->ajaxGetStep2($request->get('confirmCode', ''), $phone);
                if ($mess instanceof JsonResponse) {
                    return $mess;
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
        $phone = PhoneHelper::formatPhone($phone, '+7 (%s%s%s) %s%s%s-%s%s-%s%s');
        ob_start(); ?>
        <header class="b-registration__header">
            <h1 class="b-title b-title--h1 b-title--registration"><?= $title ?></h1>
        </header>
        <?php
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
     * @throws SystemException
     * @throws \RuntimeException
     * @throws GuzzleException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse|string
     */
    private function ajaxGetStep2($confirmCode, $phone)
    {
        $request = Application::getInstance()->getContext()->getRequest();
        if ($request->offsetExists('g-recaptcha-response')) {
            $recaptcha = (string)$request->get('g-recaptcha-response');
            /** @var ReCaptchaService $recaptchaService */
            $recaptchaService = App::getInstance()->getContainer()->get('recaptcha.service');
            if (!$recaptchaService->checkCaptcha($recaptcha)) {
                return JsonErrorResponse::create(
                    'Проверка капчи не пройдена'
                );
            }
        }
        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
            $res            = $confirmService::checkConfirmSms(
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
        } catch (NotFoundConfirmedCodeException $e) {
            return JsonErrorResponse::createWithData(
                $e->getMessage(),
                ['errors' => ['notFoundConfirmCode' => $e->getMessage()]]
            );
        }
        $mess = 'Смс прошло проверку';
        
        /** @var ManzanaService $manzanaService */
        $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
        try {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $manzanaItem = $manzanaService->getContactByPhone($phone);
        } catch (ManzanaServiceException $e) {
        }
        
        return $mess;
    }
    
    /**
     * @param string $phone
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @return array|JsonResponse
     */
    private function ajaxGetSendSmsCode($phone)
    {
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
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорректный номер телефона',
                ['errors' => ['wrongPhone' => 'Некорректный номер телефона']]
            );
        }
        
        if ($id > 0) {
            $step = 'authByPhone';
        } else {
            /** @noinspection PhpUnusedLocalVariableInspection */
            
            try {
                /** @var ConfirmCodeService $confirmService */
                $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $res            = $confirmService::sendConfirmSms($phone);
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
