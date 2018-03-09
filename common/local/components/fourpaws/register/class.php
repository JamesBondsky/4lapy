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
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\Manzana\Model\Client;
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
use GuzzleHttp\Exception\GuzzleException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsRegisterComponent extends \CBitrixComponent
{
    const BASKET_BACK_URL = '/cart/';

    const PERSONAL_URL = '/personal/';

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

    /** @var AjaxMess */
    private $ajaxMess;

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
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->userAuthorizationService = $container->get(UserAuthorizationInterface::class);
        $this->userRegistrationService = $container->get(UserRegistrationProviderInterface::class);
        $this->ajaxMess = $container->get('ajax.mess');

        $this->serializer = $container->get(SerializerInterface::class);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->arResult['STEP'] = 'begin';

            $request = Application::getInstance()->getContext()->getRequest();

            $emailGet = $request->get('email');
            $hash = $request->get('hash');
            if (!empty($emailGet) && !empty($hash)) {
                /** @var ConfirmCodeService $confirmService */
                $confirmService = App::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                try {
                    if ($confirmService::checkConfirmEmail($hash)) {
                        try {
                            $userRepository = $this->currentUserProvider->getUserRepository();
                            $userId = $userRepository->findIdentifierByRawLogin($emailGet);
                            if ($userId > 0) {
                                $user = $userRepository->find($userId);
                                if ($user instanceof User) {
                                    $user->setEmailConfirmed(true);
                                    $res = $this->currentUserProvider->getUserRepository()->update($user);
                                    if ($res) {
                                        $this->userAuthorizationService->authorize($userId);
                                    } else {
                                        ShowError('Не удалось подтвердить эл. почту');
                                        return false;
                                    }
                                } else {
                                    ShowError('Не найден пользователь');
                                    return false;
                                }
                            } else {
                                ShowError('Не найден активный пользователь c эл. почтой ' . $emailGet);
                                return false;
                            }
                        } catch (TooManyUserFoundException $e) {
                            ShowError('Найдено больше одного пользователя c эл. почтой ' . $emailGet . ', пожалуйста обратитесь на горячую линию');
                            return false;
                        } catch (UsernameNotFoundException $e) {
                            ShowError('Не найдено пользователей c эл. почтой ' . $emailGet . ', пожалуйста обратитесь на горячую линию');
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
                    } else {
                        ShowError('Проверка не пройдена, попробуйте восстановить пароль еще раз');
                        return false;
                    }
                }
                catch (ExpiredConfirmCodeException|NotFoundConfirmedCodeException $e){
                    ShowError('Проверка не пройдена, попробуйте восстановить пароль еще раз');
                    return false;
                }
            }

            if ($this->userAuthorizationService->isAuthorized()) {
                $curUser = $this->currentUserProvider->getCurrentUser();
                if (!empty($curUser->getExternalAuthId() && empty($curUser->getPersonalPhone()))) {
                    $this->arResult['STEP'] = 'addPhone';
                } else {
                    LocalRedirect(static::PERSONAL_URL);
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
     * @param string $phone
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

        return JsonSuccessResponse::create('Смс успешно отправлено');
    }

    /**
     * @param array $data
     *
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
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
        if ($haveUsers['email']) {
            return $this->ajaxMess->getHaveEmailError();
        }
        if ($haveUsers['phone']) {
            return $this->ajaxMess->getHavePhoneError();
        }
        if ($haveUsers['login']) {
            return $this->ajaxMess->getHaveLoginError();
        }

        if($data['UF_CONFIRMATION'] === 'on' || $data['UF_CONFIRMATION'] === 'Y'){
            $data['UF_CONFIRMATION'] = true;
        }
        else{
            $data['UF_CONFIRMATION'] = false;
        }

        $data['UF_PHONE_CONFIRMED'] = true;

        /** @var User $userEntity */
        $userEntity = $this->serializer->fromArray(
            $data,
            User::class,
            DeserializationContext::create()->setGroups('create')
        );
        try {
            $isBasketBackUrl = !empty($data['backurl']) && $data['backurl'] === static::BASKET_BACK_URL;
            if($isBasketBackUrl){
                $_SESSION['FROM_BASKET'] = true;
            }
            $_SESSION['MANZANA_UPDATE'] = true;
            $regUser = $this->userRegistrationService->register($userEntity, true);
            if ($regUser instanceof User && $regUser->getId() > 0) {

                $this->userAuthorizationService->authorize($regUser->getId());

                $title = 'Ура, можно покупать! ';
                /** @noinspection PhpUnusedLocalVariableInspection */
                $name = $userEntity->getName();
                ob_start(); ?>
                <header class="b-registration__header">
                    <h1 class="b-title b-title--h1 b-title--registration"><?= $title ?></h1>
                </header>
                <?php /** @noinspection PhpIncludeInspection */
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
        } catch (UserRuntimeException $exception) {
            return $this->ajaxMess->getRegisterError($exception->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @param Request $request
     *
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Bitrix\Main\SystemException
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
            $recaptchaService = $container->get('recaptcha.service');
            $checkedCaptcha = $recaptchaService->checkCaptcha();
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
                    $html = $this->getHtml('sendSmsCode', 'Подтверждение телефона',
                        ['phone' => $phone, 'newAction' => $newAction]);

                    return JsonSuccessResponse::createWithData('',
                        ['html' => $html]);
                }
                return $this->ajaxMess->getWrongConfirmCode();
            }
        } catch (ExpiredConfirmCodeException $e) {
            if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml('sendSmsCode', 'Подтверждение телефона',
                    ['phone' => $phone, 'newAction' => $newAction]);

                return JsonSuccessResponse::createWithData('',
                    ['html' => $html]);
            }
            return $this->ajaxMess->getExpiredConfirmCodeException();
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (NotFoundConfirmedCodeException $e) {
            if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml('sendSmsCode', 'Подтверждение телефона',
                    ['phone' => $phone, 'newAction' => $newAction]);

                return JsonSuccessResponse::createWithData('',
                    ['html' => $html]);
            }
            return $this->ajaxMess->getNotFoundConfirmedCodeException();
        }

        unset($_SESSION['COUNT_REGISTER_CONFIRM_CODE']);

        $data = [
            'UF_PHONE_CONFIRMED' => true,
            'PERSONAL_PHONE'     => $phone,
        ];
        if ($userRepository->updateData(
            $this->currentUserProvider->getCurrentUserId(),
            $data
        )) {
            /** @var ManzanaService $manzanaService */
            $manzanaService = $container->get('manzana.service');
            $client = null;
            try {
                $contactId = $manzanaService->getContactIdByUser();
                $client = new Client();
                $client->contactId = $contactId;
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
    public function ajaxGet($request): JsonResponse
    {
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
                list($mess, $manzanaItem) = $res;
                break;
            case 'sendSmsCode':
                unset($_SESSION['COUNT_REGISTER_CONFIRM_CODE']);
                /** @noinspection PhpUnusedLocalVariableInspection */
                $newAction = $request->get('newAction');
                $res = $this->ajaxGetSendSmsCode($phone);
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
     * @throws LoaderException
     * @throws SystemException
     */
    protected function setSocial()
    {
        if (Loader::includeModule('socialservices')) {
            $authManager = new \CSocServAuthManager();
            $startParams['AUTH_SERVICES'] = false;
            $startParams['CURRENT_SERVICE'] = false;
            $startParams['FORM_TYPE'] = 'login';
            $services = $authManager->GetActiveAuthServices($startParams);

            if (!empty($services)) {
                $this->arResult['AUTH_SERVICES'] = $services;
                $authServiceId =
                    Application::getInstance()->getContext()->getRequest()->get('auth_service_id');
                if ($authServiceId !== ''
                    && isset($authServiceId, $this->arResult['AUTH_SERVICES'][$authServiceId])) {
                    $this->arResult['CURRENT_SERVICE'] = $authServiceId;
                    $authServiceError =
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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws GuzzleException
     * @throws Exception
     * @return JsonResponse|array
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
            $recaptchaService = $container->get('recaptcha.service');
            $checkedCaptcha = $recaptchaService->checkCaptcha();
        }
        if (!$checkedCaptcha) {
            return $this->ajaxMess->getFailCaptchaCheckError();
        }

        try {
            /** @var ConfirmCodeService $confirmService */
            try {
                $confirmService = $container->get(ConfirmCodeInterface::class);
            } catch (ServiceNotFoundException $e) {
                return $this->ajaxMess->getSystemError();
            } catch (ServiceCircularReferenceException $e) {
                return $this->ajaxMess->getSystemError();
            }
            try {
                $res = $confirmService::checkConfirmSms(
                    $phone,
                    $confirmCode
                );
            } catch (ServiceNotFoundException $e) {
                return $this->ajaxMess->getSystemError();
            }
            if (!$res) {
                if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] === 3) {
                    $html = $this->getHtml('sendSmsCode', 'Подтверждение телефона',
                        ['phone' => $phone, 'newAction' => $newAction]);

                    return JsonSuccessResponse::createWithData('',
                        ['html' => $html]);
                }
                return $this->ajaxMess->getWrongConfirmCode();
            }
        } catch (ExpiredConfirmCodeException $e) {
            if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml('sendSmsCode', 'Подтверждение телефона',
                    ['phone' => $phone, 'newAction' => $newAction]);

                return JsonSuccessResponse::createWithData('',
                    ['html' => $html]);
            }
            return $this->ajaxMess->getExpiredConfirmCodeException();
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (NotFoundConfirmedCodeException $e) {
            if ($_SESSION['COUNT_REGISTER_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml('sendSmsCode', 'Подтверждение телефона',
                    ['phone' => $phone, 'newAction' => $newAction]);

                return JsonSuccessResponse::createWithData('',
                    ['html' => $html]);
            }
            return $this->ajaxMess->getNotFoundConfirmedCodeException();
        }
        unset($_SESSION['COUNT_REGISTER_CONFIRM_CODE']);
        $mess = 'Смс прошло проверку';

        /** @var ManzanaService $manzanaService */
        try {
            $manzanaService = $container->get('manzana.service');
        } catch (ServiceNotFoundException $e) {
            return $this->ajaxMess->getSystemError();
        } catch (ServiceCircularReferenceException $e) {
            return $this->ajaxMess->getSystemError();
        }
        try {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $manzanaItem = $manzanaService->getContactByPhone(PhoneHelper::getManzanaPhone($phone));
        } catch (ManzanaServiceException $e) {
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }

        return [$mess, $manzanaItem];
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
            return $this->ajaxMess->getTooManyUserFoundException($this->getSitePhone(), $phone);
        } catch (UsernameNotFoundException $e) {
            try {
                $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($phone, false);
                return $this->ajaxMess->getNotActiveUserError();
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            } catch (TooManyUserFoundException $e) {
                return $this->ajaxMess->getTooManyUserFoundException($this->getSitePhone(), $phone);
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
                    'PERSONAL_PHONE' => $phone
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
            } catch (SmsSendErrorException $e) {
                return $this->ajaxMess->getSmsSendErrorException();
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            } catch (\RuntimeException $e) {
                return $this->ajaxMess->getSystemError();
            } catch (\Exception $e) {
                return $this->ajaxMess->getSystemError();
            }
        }

        return [
            'mess' => $mess,
            'step' => $step,
        ];
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
        if (!empty($title)) { ?>
            <header class="b-registration__header">
                <h1 class="b-title b-title--h1 b-title--registration"><?= $title ?></h1>
            </header>
            <?php
        }
        /** @noinspection PhpIncludeInspection */
        require_once App::getDocumentRoot()
            . '/local/components/fourpaws/register/templates/.default/include/' . $page . '.php';

        return ob_get_clean();
    }
}
