<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\EcommerceBundle\Enum\DataLayer;
use FourPaws\EcommerceBundle\Service\DataLayerService;
use FourPaws\EcommerceBundle\Service\RetailRocketService;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\LocationBundle\Model\City;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaInterface;
use FourPaws\Search\Table\FourPawsSmsProtectorTable;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\AuthException;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Exception\RuntimeException as UserRuntimeException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\Helpers\ProtectorHelper;
use FourPaws\AppBundle\AjaxController\LandingController;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsRegisterComponent extends \CBitrixComponent
{
    public const BASKET_BACK_URL = '/cart/';
    public const PERSONAL_URL    = '/personal/';
    public const PHONE_HOT_LINE  = '8 (800) 770-00-22';

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
    /** @var AjaxMess */
    private $ajaxMess;
    /**
     * @var RetailRocketService|object
     */
    private $retailRocketService;
    /**
     * @var DataLayerService
     */
    private $dataLayerService;

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
            return;
        }

        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->userAuthorizationService = $container->get(UserAuthorizationInterface::class);
        $this->userRegistrationService = $container->get(UserRegistrationProviderInterface::class);
        $this->ajaxMess = $container->get('ajax.mess');
        $this->serializer = $container->get(SerializerInterface::class);
        $this->retailRocketService = $container->get(RetailRocketService::class);
        $this->dataLayerService = $container->get(DataLayerService::class);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function renderDataLayerByType(string $type): string
    {
        return $this->dataLayerService->renderRegister($type);
    }

    /**
     * @return bool
     */
    public function executeComponent()
    {
        try {
            $this->arResult['STEP'] = 'begin';
            $this->arResult['KIOSK'] = KioskService::isKioskMode();

            $request = Application::getInstance()->getContext()->getRequest();

            $emailGet = (string)$request->get('email');
            $hash = (string)$request->get('hash');
            if (!empty($emailGet) && !empty($hash)) {
                try {
                    $res = $this->currentUserProvider->authByHash($hash, $emailGet, 'email_register');
                    if ($res) {
                        $user = $this->currentUserProvider->getCurrentUser();
                        $user->setEmailConfirmed(true);
                        $res = $this->currentUserProvider->getUserRepository()->update($user);
                        if (!$res) {
                            $this->showError('Не удалось подтвердить эл. почту');

                            return false;
                        }
                        if (!empty($_COOKIE['BACK_URL']) && $_COOKIE['BACK_URL'] === static::BASKET_BACK_URL) {
                            $backUrl = $_COOKIE['BACK_URL'];
                            unset($_COOKIE['BACK_URL']);
                            setcookie('BACK_URL', '', time() - 5, '/');

                            LocalRedirect($backUrl);
                        } else {
                            LocalRedirect(static::PERSONAL_URL);
                        }
                    }
                } catch (TooManyUserFoundException $e) {
                    $this->showError('Найдено больше одного пользователя c эл. почтой ' . $emailGet
                                     . ', пожалуйста обратитесь на горячую линию');

                    return false;
                } catch (UsernameNotFoundException $e) {
                    $this->showError('Не найдено пользователей c эл. почтой ' . $emailGet
                                     . ', пожалуйста обратитесь на горячую линию');

                    return false;
                } catch (ExpiredConfirmCodeException|NotFoundConfirmedCodeException $e) {
                    $this->showError('Проверка не пройдена, попробуйте восстановить пароль еще раз');

                    return false;
                } catch (AuthException $e) {
                    $this->showError($e->getMessage());

                    return false;
                }
            }

            $code = (string)$request->get('code');
            $user_id = (int)$request->get('user_id');
            if ($user_id > 0 && !empty($code)) {
                if (!$this->userAuthorizationService->isAuthorized()) {
                    $this->userAuthorizationService->authorize($user_id);
                }

                /** @var ConfirmCodeService $confirmService */
                $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                try {
                    if ($confirmService::checkCode($code, 'confirm_register')) {
                        $this->arResult['STEP'] = 'confirm';
                        $curUser = $this->currentUserProvider->getCurrentUser();
                        $this->arResult['USER_NAME'] = $curUser->getName();
                        global $APPLICATION;
                        $APPLICATION->SetTitle('Ура, можно покупать!');


                        /** [todo] Просто комшмарный костыль что бы на лендинге грандина после регистрации кидало на главную */
                        if (in_array(SITE_ID, LandingController::$landingSites)) {
                            LocalRedirect('/#registr-check');
                        }


                    } else {
                        $this->showError('Проверка не пройдена');

                        return false;
                    }
                } catch (ExpiredConfirmCodeException|NotFoundConfirmedCodeException $e) {
                    $this->showError('Проверка не пройдена');

                    return false;
                }
            } else {
                if ($this->userAuthorizationService->isAuthorized()) {
                    $curUser = $this->currentUserProvider->getCurrentUser();
                    if (!empty($curUser->getExternalAuthId()) && !$curUser->hasPhone()) {
                        $this->arResult['STEP'] = 'addPhone';
                    } else {
                        LocalRedirect(static::PERSONAL_URL);
                    }
                }
            }

            if ($this->arResult['STEP'] === 'begin') {
                $this->setSocial();
            }

            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }

        return true;
    }

    /**
     * @param $error
     */
    public function showError($error): void
    {
        $this->arResult['ERROR_MESSAGE'] = $error;
        $this->includeComponentTemplate('error');
    }

    /**
     * @param string $phone
     * @param string|bool $token
     * @param string $captcha
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws SystemException
     */
    public function ajaxResendSms($phone, $token = false, $captcha = ''): JsonResponse
    {
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }

        /** @var \FourPaws\ReCaptchaBundle\Service\ReCaptchaService $recaptchaService */
        $recaptchaService = App::getInstance()->getContainer()->get(ReCaptchaInterface::class);

        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);

            if ((true)
                && ProtectorHelper::checkToken($token, ProtectorHelper::TYPE_REGISTER_SMS_RESEND)
                && $recaptchaService->checkCaptcha($captcha)
            ) {
                $res = $confirmService::sendConfirmSms($phone);
            } else {
                $res = true;
            }

            if (!$res) {
                return $this->ajaxMess->getSmsSendErrorException();
            }
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            try {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }

            return $this->ajaxMess->getSystemError();
        }

        return JsonSuccessResponse::create('Смс успешно отправлено');
    }

    /**
     * @param array $data
     *
     * @return JsonResponse
     */
    public function ajaxRegister(array $data): JsonResponse
    {
        if (!empty($data['PERSONAL_PHONE'])) {
            try {
                $data['PERSONAL_PHONE'] = PhoneHelper::normalizePhone($data['PERSONAL_PHONE']);
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            }
            $data['LOGIN'] = $data['PERSONAL_PHONE'];
        } elseif (!empty($data['EMAIL'])) {
            $data['LOGIN'] = $data['EMAIL'];
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $haveUsers = $userRepository->havePhoneAndEmailByUsers(
            [
                'PERSONAL_PHONE' => $data['PERSONAL_PHONE'],
                'EMAIL'          => $data['EMAIL'],
            ]
        );

        $userId = false;

        try {
            $userId = $this->currentUserProvider->getCurrentUserId();
        } catch (Exception $e) {}

        if ($haveUsers['email'] && !$userId) {
            return $this->ajaxMess->getHaveEmailError();
        }
        if ($haveUsers['phone'] && !$userId) {
            return $this->ajaxMess->getHavePhoneError();
        }
        if ($haveUsers['login'] && !$userId) {
            return $this->ajaxMess->getHaveLoginError();
        }

        if ($data['UF_CONFIRMATION'] === 'on' || $data['UF_CONFIRMATION'] === 'Y') {
            $data['UF_CONFIRMATION'] = true;
        } else {
            $data['UF_CONFIRMATION'] = false;
        }

        $data['UF_PHONE_CONFIRMED'] = true;

        /** @var User $userEntity */
        $userEntity = $this->serializer->fromArray(
            $data,
            User::class,
            DeserializationContext::create()->setGroups('create')
        );
        $logger = LoggerFactory::create('register');
        try {
            $isBasketBackUrl = !empty($data['backurl']) && $data['backurl'] === static::BASKET_BACK_URL;
            if ($isBasketBackUrl) {
                $_SESSION['FROM_BASKET'] = true;
            }
            if ($userId) {
                $userEntity->setId($userId);
                $userRepository->updateData(
                    $userId,
                    [
                        'EMAIL' => $userEntity->getEmail(),
                        'PASSWORD' => $userEntity->getPassword(),
                        'CONFIRM_PASSWORD' => $userEntity->getPassword(),
                        'NAME' => $userEntity->getName(),
                        'LAST_NAME' => $userEntity->getLastName(),
                        'SECOND_NAME' => $userEntity->getSecondName(),
                        'EXTERNAL_AUTH_ID' => '',
                    ]
                );
                $regUser = $userEntity;
            } else {
                $regUser = $this->userRegistrationService->register($userEntity, true);
            }
            if ($regUser instanceof User && $regUser->getId() > 0) {
                $this->userAuthorizationService->authorize($regUser->getId());

                try {
                    $container = App::getInstance()->getContainer();
                    $confirmService = $container->get(ConfirmCodeInterface::class);
                    $confirmService::setGeneratedCode('confirm_' . $regUser->getId(), 'confirm_register');
                    $uri = new Uri('/personal/register/');
                    $uri->addParams([
                        'user_id' => $regUser->getId(),
                        'backurl' => $data['backurl'],
                        'code'    => $confirmService::getGeneratedCode('confirm_register'),
                    ]);

                    return JsonSuccessResponse::create(
                        '',
                        200,
                        [],
                        ['redirect' => $uri->getUri()]
                    );
                } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException $e) {
                    $logger->error('ошибка загрузки сервисов');
                } catch (ArgumentException $e) {
                    $logger->error('ошибка аргументов - ' . $e->getMessage());
                } catch (\Exception $e) {
                    $logger->error('ошибка - ' . $e->getMessage());
                }
            }
        } catch (UserRuntimeException $exception) {
            return $this->ajaxMess->getRegisterError($exception->getMessage());
        } catch (SqlQueryException $e) {
            $logger->error('ошибка sql - ' . $e->getMessage());
        } catch (SystemException $e) {
            $logger->error('ошибка system - ' . $e->getMessage());
        } catch (Exception $e) {
            $logger->error('ошибка - ' . $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxSavePhone(Request $request): JsonResponse
    {
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            return $this->ajaxMess->getSystemError();
        }

        $phone = $request->get('phone', '');
        $newAction = $request->get('newAction', '');
        $confirmCode = $request->get('confirmCode', '');
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $haveUsers = $userRepository->havePhoneAndEmailByUsers(
            [
                'PERSONAL_PHONE' => $phone,
            ]
        );
        if ($haveUsers['phone']) {
            return $this->ajaxMess->getHavePhoneError();
        }
        if ($haveUsers['login']) {
            return $this->ajaxMess->getHaveLoginError();
        }

        $checkedCaptcha = true;
        if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] > 3) {
            try {
                $recaptchaService = $container->get(ReCaptchaInterface::class);
                $checkedCaptcha = $recaptchaService->checkCaptcha();
            } catch (SystemException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
                try {
                    $logger = LoggerFactory::create('system');
                    $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
                } catch (\RuntimeException $e) {
                    /** оч. плохо - логи мы не получим */
                }

                return $this->ajaxMess->getSystemError();
            }
        }
        if (!$checkedCaptcha) {
            return $this->ajaxMess->getFailCaptchaCheckError();
        }

        try {
            /** @var ConfirmCodeService $confirmService */
            $confirmService = $container->get(ConfirmCodeInterface::class);
            $res = $confirmService::checkConfirmSms(
                $phone,
                $confirmCode
            );
            if (!$res) {
                if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] === 3) {
                    $html = $this->getHtml(
                        'sendSmsCode',
                        'Подтверждение телефона',
                        [
                            'phone'     => $phone,
                            'newAction' => $newAction
                        ]
                    );

                    return $this->ajaxMess->getWrongConfirmCode(['html' => $html]);
                }

                return $this->ajaxMess->getWrongConfirmCode();
            }
        } catch (ExpiredConfirmCodeException $e) {
            if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml(
                    'sendSmsCode',
                    'Подтверждение телефона',
                    [
                        'phone'     => $phone,
                        'newAction' => $newAction
                    ]
                );

                return $this->ajaxMess->getExpiredConfirmCodeException(['html' => $html]);
            }

            return $this->ajaxMess->getExpiredConfirmCodeException();
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (NotFoundConfirmedCodeException $e) {
            if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml(
                    'sendSmsCode',
                    'Подтверждение телефона',
                    [
                        'phone'     => $phone,
                        'newAction' => $newAction
                    ]
                );

                return $this->ajaxMess->getNotFoundConfirmedCodeException(['html' => $html]);
            }

            return $this->ajaxMess->getNotFoundConfirmedCodeException();
        } catch (ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            try {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }

            return $this->ajaxMess->getSystemError();
        }

        unset($_SESSION['COUNT_REGISTER_CONFIRM_CODE']);

        $data = [
            'UF_PHONE_CONFIRMED' => true,
            'PERSONAL_PHONE'     => $phone,
            'LOGIN'              => $phone,
        ];
        try {
            if ($userRepository->updateData(
                $this->currentUserProvider->getCurrentUserId(),
                $data
            )) {
                /** @var ManzanaService $manzanaService */
                $manzanaService = $container->get('manzana.service');
                $client = null;
                try {
                    $contactId = $manzanaService->getContactIdByPhone(PhoneHelper::getManzanaPhone($phone));
                    $client = new Client();
                    if (!empty($contactId)) {
                        $client->contactId = $contactId;
                    }
                } catch (ManzanaServiceException $e) {
                    $client = new Client();
                } catch (NotAuthorizedException $e) {
                    return $this->ajaxMess->getNotAuthorizedException();
                }

                if ($client instanceof Client) {
                    $this->currentUserProvider->setClientPersonalDataByCurUser($client);
                    $manzanaService->updateContactAsync($client);
                }
            }
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (ValidationException|InvalidIdentifierException|ConstraintDefinitionException $e) {
            try {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }

            return $this->ajaxMess->getSystemError();
        } catch (SystemException|ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException $e) {
            try {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }

            return $this->ajaxMess->getSystemError();
        }

        $title = 'Регистрация';

        $manzanaItem = $client;

        ob_start(); ?>
        <header class="b-registration__header">
            <div class="b-title b-title--h1 b-title--registration"><?= $title ?></div>
        </header>
        <?php
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot() . '/local/components/fourpaws/register/templates/.default/include/step2.php';
        $html = ob_get_clean();

        return JsonSuccessResponse::createWithData(
            'Телефон сохранен',
            [
                'html'  => $html,
                'step'  => 'step2',
            ]
        );

        return JsonSuccessResponse::create('Телефон сохранен', 200, [], ['reload' => true]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \RuntimeException
     */
    public function ajaxGet($request): JsonResponse
    {
        if (isset($_SESSION['COUNT_REGISTER_CONFIRM_CODE']) && $_SESSION['COUNT_REGISTER_CONFIRM_CODE'] > 3) {
            $_SESSION['COUNT_REGISTER_CONFIRM_CODE'] = 0;
        }

        $step = $request->get('step', '');
        $phone = $request->get('phone', '');
        if (!empty($phone)) {
            try {
                $phone = PhoneHelper::normalizePhone($phone);
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            }
        }
        $mess = '';
        $title = 'Регистрация';
        switch ($step) {
            case 'step2':
                $res = $this->ajaxGetStep2($request->get('confirmCode', ''), $phone);
                if ($res instanceof JsonResponse) {
                    return $res;
                }
                /** @noinspection PhpUnusedLocalVariableInspection */
                [
                    $mess,
                    $manzanaItem
                ] = $res;
                break;
            case 'sendSmsCode':
                unset($_SESSION['COUNT_REGISTER_CONFIRM_CODE']);
                /** @noinspection PhpUnusedLocalVariableInspection */
                $newAction = $request->get('newAction');

                /** @var \FourPaws\ReCaptchaBundle\Service\ReCaptchaService $recaptchaService */
                $recaptchaService = App::getInstance()->getContainer()->get(ReCaptchaInterface::class);

                /** csrf custom sms send protection */
                if ((true)
                    && ProtectorHelper::checkToken($request->get(ProtectorHelper::getField(ProtectorHelper::TYPE_REGISTER_SMS_SEND)), ProtectorHelper::TYPE_REGISTER_SMS_SEND)
                    && ($recaptchaService->checkCaptcha($request->get('g-recaptcha-response')) || KioskService::isKioskMode())
                ) {
                    $res = $this->ajaxGetSendSmsCode($phone);
                } else {
                    $res = [
                        'mess' => 'Смс успешно отправлено',
                        'step' => '',
                    ];
                }

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

        /** @noinspection PhpUnusedLocalVariableInspection */
        $formSubmit = \str_replace('"', '\'',
            \sprintf(
                '%s%s%s%s',
                $this->renderDataLayerByType(DataLayer::REGISTER_TYPE_LOGIN),
                'if ($(this).find("input[type=email]").val().indexOf("register.phone") == -1){',
                $this->retailRocketService->renderSendEmail('$(this).find("input[type=email]").val(), {name: $(this).find("input[name=NAME]").val()}'),
                '}'
            )
        );

        $phone = PhoneHelper::formatPhone($phone, PhoneHelper::FORMAT_FULL);
        ob_start(); ?>
        <header class="b-registration__header">
            <div class="b-title b-title--h1 b-title--registration"><?= $title ?></div>
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
     * @throws LoaderException
     * @throws SystemException
     */
    protected function setSocial(): void
    {
        $authManager = new \CSocServAuthManager();
        $startParams['AUTH_SERVICES'] = false;
        $startParams['CURRENT_SERVICE'] = false;
        $startParams['FORM_TYPE'] = 'login';
        $services = $authManager->GetActiveAuthServices($startParams);

        if ($services) {
            $this->arResult['AUTH_SERVICES'] = $services;
            foreach ($services as &$service) {
                $service['ONCLICK'] = $this->renderDataLayerByType(DataLayer::SOCIAL_SERVICE_MAP[$service['ID']]
                                                                   ?? '') . $service['ONCLICK'];
            }
            unset($service);

            $authServiceId = Application::getInstance()->getContext()->getRequest()->get('auth_service_id');
            if (
                $authServiceId !== ''
                && isset($authServiceId, $this->arResult['AUTH_SERVICES'][$authServiceId])
            ) {
                $this->arResult['CURRENT_SERVICE'] = $authServiceId;
                $authServiceError = Application::getInstance()->getContext()->getRequest()->get('auth_service_error');
                if ($authServiceError) {
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

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return string
     */
    protected function getSitePhone(): string
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
     * @param string $confirmCode
     * @param string $phone
     * @param string $newAction
     *
     * @return array|JsonResponse
     */
    private function ajaxGetStep2(string $confirmCode, string $phone, string $newAction = '')
    {
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            return $this->ajaxMess->getSystemError();
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->currentUserProvider->getUserRepository();
        $haveUsers = $userRepository->havePhoneAndEmailByUsers(
            [
                'PERSONAL_PHONE' => $phone,
            ]
        );
        if ($haveUsers['phone']) {
            return $this->ajaxMess->getHavePhoneError();
        }
        if ($haveUsers['login']) {
            return $this->ajaxMess->getHaveLoginError();
        }

        if (!isset($_SESSION['COUNT_REGISTER_CONFIRM_CODE'])) {
            $_SESSION['COUNT_REGISTER_CONFIRM_CODE'] = 0;
        }
        $_SESSION['COUNT_REGISTER_CONFIRM_CODE']++;

        $checkedCaptcha = true;
        if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] > 3) {
            try {
                $recaptchaService = $container->get(ReCaptchaInterface::class);
                $checkedCaptcha = $recaptchaService->checkCaptcha();
            } catch (SystemException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
                try {
                    $logger = LoggerFactory::create('system');
                    $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
                } catch (\RuntimeException $e) {
                    /** оч. плохо - логи мы не получим */
                }

                return $this->ajaxMess->getSystemError();
            }
        }

        if (!$checkedCaptcha) {
            return $this->ajaxMess->getFailCaptchaCheckError()->extendData(['gcaptcha' => true]);
        }

        try {
            /** @var ConfirmCodeService $confirmService */

            $confirmService = $container->get(ConfirmCodeInterface::class);
            $res = $confirmService::checkConfirmSms(
                $phone,
                $confirmCode
            );
            if (!$res) {
                if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] === 3) {
                    $html = $this->getHtml(
                        'sendSmsCode',
                        'Подтверждение телефона',
                        [
                            'phone'     => $phone,
                            'newAction' => $newAction
                        ]
                    );

                    return $this->ajaxMess->getWrongConfirmCode(['html' => $html])->extendData(['gcaptcha' => true]);
                }

                return $this->ajaxMess->getWrongConfirmCode();
            }
        } catch (ExpiredConfirmCodeException $e) {
            if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml(
                    'sendSmsCode',
                    'Подтверждение телефона',
                    [
                        'phone'     => $phone,
                        'newAction' => $newAction
                    ]
                );

                return $this->ajaxMess->getExpiredConfirmCodeException(['html' => $html])->extendData(['gcaptcha' => true]);
            }

            return $this->ajaxMess->getExpiredConfirmCodeException();
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (NotFoundConfirmedCodeException $e) {
            if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml(
                    'sendSmsCode',
                    'Подтверждение телефона',
                    [
                        'phone'     => $phone,
                        'newAction' => $newAction
                    ]
                );

                return $this->ajaxMess->getNotFoundConfirmedCodeException(['html' => $html])->extendData(['gcaptcha' => true]);
            }

            if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] > 3) {
                return $this->ajaxMess->getNotFoundConfirmedCodeException()->extendData(['gcaptcha' => true]);
            } else {
                return $this->ajaxMess->getNotFoundConfirmedCodeException();
            }
        } catch (ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            try {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                /** оч. плохо - логи мы не получим */
            }

            return $this->ajaxMess->getSystemError();
        }
        unset($_SESSION['COUNT_REGISTER_CONFIRM_CODE']);
        $mess = 'Смс прошло проверку';

        /** @var ManzanaService $manzanaService */
        $manzanaItem = null;
        try {
            if (!empty($phone)) {
                $manzanaService = $container->get('manzana.service');
                /** @noinspection PhpUnusedLocalVariableInspection */
                $manzanaItem = $manzanaService->getContactByPhone(PhoneHelper::getManzanaPhone($phone));
            }
        } catch (ManzanaServiceException $e) {
            $logger = LoggerFactory::create('manzana');
            $logger->critical('Ошибка manzana - ' . $e->getMessage());
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (ServiceNotFoundException|ServiceCircularReferenceException $e) {
            return $this->ajaxMess->getSystemError();
        }

        return [
            $mess,
            $manzanaItem
        ];
    }

    /**
     * @param string $phone
     *
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
            try {
                return $this->ajaxMess->getTooManyUserFoundException($this->getSitePhone(), $phone,
                    'логином/телефоном');
            } catch (ApplicationCreateException $e) {
                return $this->ajaxMess->getTooManyUserFoundException('', $phone, 'логином/телефоном');
            }
        } catch (UsernameNotFoundException $e) {
            try {
                $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($phone, false);

                return $this->ajaxMess->getNotActiveUserError();
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            } catch (TooManyUserFoundException $e) {
                try {
                    return $this->ajaxMess->getTooManyUserFoundException($this->getSitePhone(), $phone,
                        'логином/телефоном');
                } catch (ApplicationCreateException $e) {
                    return $this->ajaxMess->getTooManyUserFoundException('', $phone, 'логином/телефоном');
                }
            } catch (UsernameNotFoundException $e) {
                /** если пользователя не найдено регистрируем */
            }
            /** если пользователь не найден можно регаться */
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }

        if ($id > 0) {
            $step = 'authByPhone';
        } else {
            /** @noinspection PhpUnusedLocalVariableInspection */

            /** второй чек на совпадение полей */
            /** @var UserRepository $userRepository */
            $userRepository = $this->currentUserProvider->getUserRepository();
            $haveUsers = $userRepository->havePhoneAndEmailByUsers(
                [
                    'PERSONAL_PHONE' => $phone,
                ]
            );
            if ($haveUsers['phone']) {
                return $this->ajaxMess->getHavePhoneError();
            }
            if ($haveUsers['login']) {
                return $this->ajaxMess->getHaveLoginError();
            }

            try {
                /** @var ConfirmCodeService $confirmService */
                $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $res = $confirmService::sendConfirmSms($phone);
                if ($res) {
                    $mess = 'Смс успешно отправлено';
                } else {
                    return $this->ajaxMess->getSmsSendErrorException();
                }
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
                try {
                    $logger = LoggerFactory::create('system');
                    $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
                } catch (\RuntimeException $e) {
                    /** оч. плохо - логи мы не получим */
                }

                return $this->ajaxMess->getSystemError();
            }
        }

        return \compact('mess', 'step');
    }

    /**
     * @param string $page
     * @param string $title
     * @param array  $params
     *
     * @return string
     */
    private function getHtml(string $page, string $title = '', array $params = []): string
    {
        if (!empty($params)) {
            extract($params, EXTR_OVERWRITE);
        }
        ob_start();
        if (!empty($title)) {
            ?>
            <header class="b-registration__header">
                <div class="b-title b-title--h1 b-title--registration"><?= $title ?></div>
            </header>
            <?php
        }
        /** @noinspection PhpIncludeInspection */
        require_once App::getDocumentRoot()
                     . '/local/components/fourpaws/register/templates/.default/include/' . $page . '.php';

        return ob_get_clean();
    }
}
