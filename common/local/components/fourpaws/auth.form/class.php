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
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\FuserTable;
use Bitrix\Sale\Order;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\LocationBundle\Model\City;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsAuthFormComponent extends \CBitrixComponent
{
    public const MODE_PROFILE = 0;

    public const MODE_FORM = 1;

    public const PHONE_HOT_LINE = '8 (800) 770-00-22';

    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;

    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthorizationService;

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
        $this->userAuthorizationService = $container->get(UserAuthorizationInterface::class);
        $this->ajaxMess = $container->get('ajax.mess');
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->arResult['STEP'] = '';
            if ($this->getMode() === static::MODE_FORM) {
                $this->arResult['STEP'] = 'begin';
            }

            if ($this->userAuthorizationService->isAuthorized()) {
                $curUser = $this->currentUserProvider->getCurrentUser();
                if (!empty($curUser->getExternalAuthId() && empty($curUser->getPersonalPhone()))) {
                    $this->arResult['STEP'] = 'addPhone';
                } else {
                    $this->arResult['NAME'] = $curUser->getName() ?? $curUser->getLogin();
                }
            }
            $this->setSocial();
            unset($_SESSION['COUNT_AUTH_AUTHORIZE']);
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
    public function getMode(): int
    {
        return $this->getUserAuthorizationService()->isAuthorized() ? static::MODE_PROFILE : static::MODE_FORM;
    }

    /**
     * @return UserAuthorizationInterface
     */
    public function getUserAuthorizationService(): UserAuthorizationInterface
    {
        return $this->userAuthorizationService;
    }

    /**
     * @return CurrentUserProviderInterface
     */
    public function getCurrentUserProvider(): CurrentUserProviderInterface
    {
        return $this->currentUserProvider;
    }

    /**
     * @param string $rawLogin
     * @param string $password
     * @param string $backUrl
     *
     * @return JsonResponse
     */
    public function ajaxLogin(string $rawLogin, string $password, string $backUrl = ''): JsonResponse
    {
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }
        $needWritePhone = false;
        if (empty($rawLogin)) {
            return $this->ajaxMess->getEmptyDataError();
        }
        if (empty($password)) {
            return $this->ajaxMess->getEmptyPasswordError();
        }

        if (!isset($_SESSION['COUNT_AUTH_AUTHORIZE'])) {
            $_SESSION['COUNT_AUTH_AUTHORIZE'] = 0;
        }
        $_SESSION['COUNT_AUTH_AUTHORIZE']++;

        $checkedCaptcha = true;
        if ($_SESSION['COUNT_AUTH_AUTHORIZE'] > 3) {
            try {
                $recaptchaService = $container->get('recaptcha.service');
                $checkedCaptcha = $recaptchaService->checkCaptcha();
            } catch (SystemException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException $e) {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
                return $this->ajaxMess->getSystemError();
            }
        }
        if (!$checkedCaptcha) {
            return $this->ajaxMess->getFailCaptchaCheckError();
        }

        $needConfirmBasket = false;
        try {
            $basketService = $container->get(BasketService::class);
        } catch (ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }
        $curBasket = $basketService->getBasket();
        $userBasket = null;
        $delBasketIds = [];
        $basketPrice = 0;
        $curFuserId = \Bitrix\Sale\Fuser::getId();

        if (!$curBasket->isEmpty()) {
            try {
                $userId = $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($rawLogin);
                if ($userId > 0) {
                    $fUserId = (int)FuserTable::query()->setFilter(['USER_ID' => $userId])->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
                    if ($fUserId > 0) {
                        $userBasket = $basketService->getBasket(true, $fUserId);

                        // привязывать к заказу нужно для расчета скидок
                        if (null === $order = $userBasket->getOrder()) {
                            $order = Order::create(SITE_ID);
                            $order->setBasket($userBasket);
                        }

                        if (!$curBasket->isEmpty() && !$userBasket->isEmpty()) {
                            $needConfirmBasket = true;
                            $delBasketIds = [];
                            /** @var BasketItem $item */
                            foreach ($userBasket->getBasketItems() as $item) {
                                $delBasketIds[] = $item->getId();
                            }
                            $basketPrice = $userBasket->getPrice();
                        }
                    }
                }
            } catch (WrongPhoneNumberException|TooManyUserFoundException|UsernameNotFoundException $e) {
                /** обработка ниже, поэтому скипаем */
            }
        }

        try {
            $this->userAuthorizationService->login($rawLogin, $password);
            if ($this->userAuthorizationService->isAuthorized()) {
                if (!$this->currentUserProvider->getCurrentUser()->havePersonalPhone()) {
                    $needWritePhone = true;
                }
            }
        } catch (UsernameNotFoundException $e) {
            if ($_SESSION['COUNT_AUTH_AUTHORIZE'] === 3) {
                try {
                    $this->setSocial();
                    $html = $this->getHtml(
                        'begin',
                        '',
                        ['isAjax' => true, 'backurl' => $backUrl, 'arResult' => $this->arResult]
                    );

                    return JsonSuccessResponse::createWithData(
                        '',
                        ['html' => $html]
                    );
                } catch (SystemException|LoaderException $e) {
                    $logger = LoggerFactory::create('system');
                    $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
                    return $this->ajaxMess->getSystemError();
                }
            }
            return $this->ajaxMess->getWrongPasswordError();
        } catch (InvalidCredentialException $e) {
            if ($_SESSION['COUNT_AUTH_AUTHORIZE'] === 3) {
                try {
                    $this->setSocial();
                    $html = $this->getHtml(
                        'begin',
                        '',
                        ['isAjax' => true, 'backurl' => $backUrl, 'arResult' => $this->arResult]
                    );

                    return JsonSuccessResponse::createWithData('', ['html' => $html]);
                } catch (SystemException|LoaderException $e) {
                    $logger = LoggerFactory::create('system');
                    $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
                    return $this->ajaxMess->getSystemError();
                }
            }
            return $this->ajaxMess->getWrongPasswordError();
        } catch (TooManyUserFoundException $e) {
            /** @noinspection PhpUnhandledExceptionInspection */
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $logger = LoggerFactory::create('auth');
            $logger->critical('Найдено больше одного совпадения по логину/email/телефону ' . $rawLogin);

            try {
                return $this->ajaxMess->getTooManyUserFoundException($this->getSitePhone(), $rawLogin);
            } catch (ApplicationCreateException $e) {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
                return $this->ajaxMess->getSystemError();
            }
        }

        unset($_SESSION['COUNT_AUTH_AUTHORIZE']);
        if ($needConfirmBasket) {
            $html = $this->getHtml(
                'unionBasket',
                'Объединение корзины',
                [
                    'backurl'      => $backUrl,
                    'needAddPhone' => $needWritePhone ? 'Y' : 'N',
                    'delBasketIds' => $delBasketIds,
                    'sum'          => $basketPrice,
                ]
            );

            return JsonSuccessResponse::createWithData('Необходимо заполнить номер телефона', ['html' => $html]);
        }
        if ($needWritePhone) {
            $html = $this->getHtml('addPhone', 'Добавление телефона', ['backurl' => $backUrl]);

            return JsonSuccessResponse::createWithData('Необходимо заполнить номер телефона', ['html' => $html]);
        }
        return JsonSuccessResponse::create('Вы успешно авторизованы.', 200, [], ['reload' => true]);
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
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }

        return JsonSuccessResponse::create('Смс успешно отправлено');
    }

    /**
     * @param string $phone
     *
     * @param string $confirmCode
     *
     * @param string $backUrl
     *
     * @return JsonResponse
     */
    public function ajaxSavePhone(string $phone, string $confirmCode, string $backUrl): JsonResponse
    {
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }

        if (!isset($_SESSION['COUNT_AUTH_CONFIRM_CODE'])) {
            $_SESSION['COUNT_AUTH_CONFIRM_CODE'] = 0;
        }
        $_SESSION['COUNT_AUTH_CONFIRM_CODE']++;

        $checkedCaptcha = true;
        if ($_SESSION['COUNT_AUTH_CONFIRM_CODE'] > 3) {
            try {
                $recaptchaService = $container->get('recaptcha.service');
                $checkedCaptcha = $recaptchaService->checkCaptcha();
            } catch (ServiceNotFoundException|ServiceCircularReferenceException|SystemException|\RuntimeException|\Exception $e) {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
                return $this->ajaxMess->getFailCaptchaCheckError();
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
                if ($_SESSION['COUNT_AUTH_CONFIRM_CODE'] === 3) {
                    $html = $this->getHtml(
                        'sendSmsCode',
                        'Подтверждение телефона',
                        ['phone' => $phone, 'backurl' => $backUrl]
                    );

                    return JsonSuccessResponse::createWithData(
                        '',
                        ['html' => $html]
                    );
                }
                return $this->ajaxMess->getWrongConfirmCode();
            }
        } catch (ExpiredConfirmCodeException $e) {
            if ($_SESSION['COUNT_AUTH_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml(
                    'sendSmsCode',
                    'Подтверждение телефона',
                    ['phone' => $phone, 'backurl' => $backUrl]
                );

                return JsonSuccessResponse::createWithData(
                    '',
                    ['html' => $html]
                );
            }
            return $this->ajaxMess->getExpiredConfirmCodeException();
        } catch (NotFoundConfirmedCodeException $e) {
            if ($_SESSION['COUNT_AUTH_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml(
                    'sendSmsCode',
                    'Подтверждение телефона',
                    ['phone' => $phone, 'backurl' => $backUrl]
                );

                return JsonSuccessResponse::createWithData(
                    '',
                    ['html' => $html]
                );
            }
            return $this->ajaxMess->getNotFoundConfirmedCodeException();
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        unset($_SESSION['COUNT_AUTH_CONFIRM_CODE']);
        $data = [
            'UF_PHONE_CONFIRMED' => true,
        ];

        try {
            if ($this->currentUserProvider->getUserRepository()->updateData(
                $this->currentUserProvider->getCurrentUserId(),
                $data
            )) {
                /** @var ManzanaService $manzanaService */
                $manzanaService = $container->get('manzana.service');
                $client = null;
                try {
                    $contactId = $manzanaService->getContactIdByPhone(PhoneHelper::getManzanaPhone($phone));
                    $client = new Client();
                    $client->contactId = $contactId;
                    $client->phone = PhoneHelper::getManzanaPhone($phone);
                } catch (ManzanaServiceException $e) {
                    $client = new Client();

                    try {
                        $this->currentUserProvider->setClientPersonalDataByCurUser($client);
                    } catch (NotAuthorizedException $e) {
                        /** не должна быть вызвана */
                        $this->ajaxMess->getNeedAuthError();
                    }
                } catch (WrongPhoneNumberException $e) {
                    return $this->ajaxMess->getWrongPhoneNumberException();
                }

                if ($client instanceof Client) {
                    try {
                        $manzanaService->updateContactAsync($client);
                    } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException $e) {
                        $logger = LoggerFactory::create('system');
                        $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
                    }
                }
            }
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (InvalidIdentifierException|ConstraintDefinitionException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
        } catch (ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        return JsonSuccessResponse::create('Телефон сохранен', 200, [], ['reload' => true]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxGet($request): JsonResponse
    {
        $mess = '';
        $step = $request->get('step', '');
        $phone = $request->get('phone', '');
        $backUrl = $request->get('backurl', '');
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }
        $title = 'Авторизация';
        switch ($step) {
            case 'addPhone':
                $title = 'Добавление телефона';
                break;
            case 'sendSmsCode':
                unset($_SESSION['COUNT_AUTH_CONFIRM_CODE']);
                $title = 'Подтверждение телефона';
                $mess = $this->ajaxGetSendSmsCode($phone);
                if ($mess instanceof JsonResponse) {
                    return $mess;
                }
                break;
        }
        $phone = PhoneHelper::formatPhone($phone, '+7 (%s%s%s) %s%s%s-%s%s-%s%s');
        $html = $this->getHtml($step, $title, ['phone' => $phone, 'step' => $step, 'backurl' => $backUrl]);

        return JsonSuccessResponse::createWithData(
            $mess,
            [
                'html'  => $html,
                'step'  => $step,
                'phone' => $phone ?? '',
            ]
        );
    }

    public function ajaxUnionBasket(Request $request): JsonResponse
    {
        $backUrl = $request->get('backurl', '');
        $needAddPhone = $request->get('need_add_phone', 'N');

        $data = [];
        $options = [];

        if ($needAddPhone === 'Y') {
            $data = ['html' => $this->getHtml('addPhone', 'Добавление телефона'), ['backurl' => $backUrl]];
        } else {
            if (!empty($backUrl)) {
                $options = ['redirect' => $backUrl];
            } else {
                $options = ['reload' => true];
            }
        }

        return JsonSuccessResponse::createWithData('Корзины объединены', $data, 200, $options);
    }

    public function ajaxNotUnionBasket(Request $request): JsonResponse
    {
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }

        $backUrl = $request->get('backurl', '');
        $needAddPhone = $request->get('need_add_phone', 'N');
        $delBasketItems = $request->get('del_basket_items', []);
        if (!empty($delBasketItems)) {
            $delBasketItems = explode(',', $delBasketItems);
        }

        $data = [];
        $options = [];

        if ($needAddPhone === 'Y') {
            $data = ['html' => $this->getHtml('addPhone', 'Добавление телефона'), ['backurl' => $backUrl]];
        } else {
            if (!empty($backUrl)) {
                $options = ['redirect' => $backUrl];
            } else {
                $options = ['reload' => true];
            }
        }

        if (\is_array($delBasketItems) && !empty($delBasketItems)) {
            try {
                $basketService = $container->get(BasketService::class);
            } catch (ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException $e) {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
                return $this->ajaxMess->getSystemError();
            }
            foreach ($delBasketItems as $id) {
                try {
                    $basketService->deleteOfferFromBasket($id);
                } catch (ObjectNotFoundException|BitrixProxyException|Exception $e) {
                    $logger = LoggerFactory::create('basket');
                    $logger->critical('Ошибка удаления - ' . $e->getMessage());
                    return $this->ajaxMess->getSystemError();
                }
            }
        }

        return JsonSuccessResponse::createWithData('Сохранена текущая корзина', $data, 200, $options);
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
     * @throws LoaderException
     * @throws SystemException
     */
    protected function setSocial(): void
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
                <h1 class="b-title b-title--h1 b-title--registration"><?= $title ?></h1>
            </header>
            <?php
        }
        require_once App::getDocumentRoot()
            . '/local/components/fourpaws/auth.form/templates/popup/include/' . $page . '.php';

        return ob_get_clean();
    }

    /**
     * @param string $phone
     *
     * @return JsonResponse|string
     */
    private function ajaxGetSendSmsCode($phone)
    {
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
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }

        $data = [
            'PERSONAL_PHONE'     => $phone,
            'UF_PHONE_CONFIRMED' => false,
        ];

        if (!$this->currentUserProvider->getUserRepository()->updateData(
            $this->currentUserProvider->getCurrentUserId(),
            $data
        )) {
            return $this->ajaxMess->getSystemError();
        }

        return $mess;
    }

//    pubf
}
