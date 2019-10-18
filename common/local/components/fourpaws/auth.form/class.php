<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
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
use FourPaws\EcommerceBundle\Enum\DataLayer;
use FourPaws\EcommerceBundle\Service\DataLayerService;
use FourPaws\EcommerceBundle\Service\RetailRocketService;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\ProtectorHelper;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\LocationBundle\Model\City;
use FourPaws\UserBundle\Service\UserService;
use FourPaws\PersonalBundle\Service\PetService;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaInterface;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ExpiredConfirmCodeException;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Service\UserSearchInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsAuthFormComponent extends \CBitrixComponent
{
    public const MODE_PROFILE   = 0;
    public const MODE_FORM      = 1;
    public const PHONE_HOT_LINE = '8 (800) 770-00-22';
    public const PETS_TYPE = [
        'koshki' => 'cat',
        'sobaki' => 'dog',
        'ryby' => 'fish',
        'ptitsy' => 'bird',
        '9' => 'reptile',
        'gryzuny' => 'rodent'
    ];

    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthorizationService;
    /**
     * @var UserSearchInterface
     */
    private $userSearchService;


    /** @var AjaxMess */
    private $ajaxMess;
    /**
     * @var RetailRocketService
     */
    private $retailRocketService;
    /**
     * @var DataLayerService
     */
    private $dataLayerService;
    /**
     * @var KioskService
     */
    private $kioskService;
    /**
     * @var int
     */
    private $limitAuthAuthorizeAttempts;

    /**
     * FourPawsAuthFormComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws SystemException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();

            $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
            $this->retailRocketService = $container->get(RetailRocketService::class);
            $this->userAuthorizationService = $container->get(UserAuthorizationInterface::class);
            $this->userSearchService = $container->get(UserSearchInterface::class);
            $this->dataLayerService = $container->get(DataLayerService::class);
            $this->kioskService = $container->get('kiosk.service');
            $this->ajaxMess = $container->get('ajax.mess');
        } catch (Exception $e) {
        }
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
                if (!empty($curUser->getExternalAuthId() && !$curUser->hasEmail())) {
                    $this->arResult['STEP'] = 'addPhone';
                }
                $this->arResult['NAME'] = $curUser->getName() ?? $curUser->getLogin();
            }

            if (KioskService::isKioskMode() && !$this->userAuthorizationService->isAuthorized()) {
                $this->arResult['KIOSK'] = true;
                $this->arResult['AUTH_LINK'] = $this->kioskService->getAuthLink();
                $this->arResult['REDIRECT_TO_BONUS'] = $this->kioskService->isRedirectToBonusAfterAuth();
                if($this->arResult['REDIRECT_TO_BONUS']){
                    $backUrl = $this->kioskService->getBonusPageUrl();
                } else {
                    $backUrl = $this->kioskService->removeParamFromUrl('showScan');
                }
                // при сканировании шк backurl надо хранить в сессии
                $this->kioskService->setLastPageUrl($backUrl);
                $this->arResult['BACK_URL'] = $backUrl;
            }

            $this->arResult['IS_SHOW_CAPTCHA'] = $this->isShowCapthca();
            $this->setSocial();

            $this->arResult['LOGIN'] = $this->getRawLogin();

            $this->arResult['LIMIT_AUTH_ATTEMPT'] = $this->getLimitAuthAuthorizeAttempts($this->arResult['LOGIN']);
            $this->includeComponentTemplate();
        } catch (Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (RuntimeException $e) {
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
     * @param string $type
     *
     * @return string
     */
    public function renderDataLayerByType(string $type): string
    {
        return $this->dataLayerService->renderAuth($type);
    }

    /**
     * @param string $rawLogin
     * @param string $password
     * @param string $backUrl
     * @param string|bool $token
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws SystemException
     * @throws WrongPhoneNumberException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function ajaxLogin(string $rawLogin, string $password, string $backUrl = '', $token = false): JsonResponse
    {
        // CSRF-защита
        if (!ProtectorHelper::checkToken($token, ProtectorHelper::TYPE_AUTH))
        {
            $options = ['reload' => true];
            if (!empty($backUrl)) {
                $options = ['redirect' => $backUrl];
            }
            return JsonSuccessResponse::createWithData('Вы успешно авторизованы.', [], 200, $options); // на самом деле, нет
        }

        $newToken = ProtectorHelper::generateToken(ProtectorHelper::TYPE_AUTH);
        $this->arResult['token'] = $newToken;
        $newToken['value'] = $newToken['token'];
        unset($newToken['token']);
        $newTokenResponse = ['token' => $newToken];

        $this->arResult['LOGIN'] = $rawLogin;

        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            return $this->ajaxMess->getSystemError()->extendData($newTokenResponse);
        }
        $needWritePhone = false;
        if (empty($rawLogin)) {
            return $this->ajaxMess->getEmptyDataError()->extendData($newTokenResponse);
        }
        if (empty($password)) {
            return $this->ajaxMess->getEmptyPasswordError()->extendData($newTokenResponse);
        }

        if (!isset($_SESSION['COUNT_AUTH_AUTHORIZE'])) {
            $_SESSION['COUNT_AUTH_AUTHORIZE'] = 0;
        }

        $_SESSION['COUNT_AUTH_AUTHORIZE']++;

        if ($_SESSION['COUNT_AUTH_AUTHORIZE'] > $this->getLimitAuthAuthorizeAttempts($rawLogin)) {
            try {
                if ($this->showBitrixCaptcha($rawLogin)) {
                    $recaptchaService = $container->get(ReCaptchaInterface::class);
                    $checkedCaptcha = $recaptchaService->checkCaptcha();

                    if (!$checkedCaptcha) {
                        $html = $this->getHtml(
                            'begin',
                            '',
                            [
                                'isAjax'   => true,
                                'backUrl'  => $backUrl,
                                'arResult' => $this->arResult
                            ]
                        );

                        return $this->ajaxMess->getWrongPasswordError(array_merge(
                            ['html' => $html],
                            $newTokenResponse
                        ));
                    }
                }
                $this->userAuthorizationService->clearLoginAttempts($rawLogin);
            } catch (Exception $e) {
                return $this->ajaxMess->getSystemError()->extendData($newTokenResponse);
            }
        }

        $needConfirmBasket = false;
        try {
            $basketService = $container->get(BasketService::class);
        } catch (Exception $e) {
            return $this->ajaxMess->getSystemError()->extendData($newTokenResponse);
        }

        $curBasket = $basketService->getBasket();
        $userBasket = null;
        $delBasketIds = [];
        $delBasketKeys = [];
        $addQuantityBasketIds = [];
        $delItemsByUnionIds = [];
        $delItemsByUnionKeys = [];
        $basketPrice = 0;

        if (!$curBasket->isEmpty()) {
            try {
                $userId = $this->currentUserProvider->getUserRepository()->findIdentifierByRawLogin($rawLogin);
                if ($userId > 0) {
                    $fUserId = (int)FuserTable::query()
                        ->setFilter(['USER_ID' => $userId])
                        ->setSelect(['ID'])
                        ->setCacheTtl(360000)
                        ->exec()
                        ->fetch()['ID'];
                    if ($fUserId > 0) {
                        $userBasket = $basketService->getBasket(true, $fUserId);

                        // привязывать к заказу нужно для расчета скидок
                        if (null === $order = $userBasket->getOrder()) {
                            $userId = null;
                            try {
                                $userId = $this->currentUserProvider->getCurrentUserId();
                            } catch (NotAuthorizedException $e) {
                            }
                            $order = Order::create(SITE_ID, $userId);
                            try {
                                $order->setBasket($userBasket);
                            } catch (NotSupportedException|ObjectNotFoundException $e) {
                                $logger = LoggerFactory::create('unionBasket');
                                $logger->critical('Ошибка инициализации заказа при объединении корзин - '
                                    . $e->getMessage());
                            }
                        }

                        if (!$curBasket->isEmpty() && !$userBasket->isEmpty()) {
                            $needConfirmBasket = true;
                            /** @var BasketItem $item */
                            foreach ($userBasket->getBasketItems() as $key => $item) {
                                if ($item->getId() <= 0) {
                                    $delBasketKeys[] = $item->getInternalIndex();
                                } else {
                                    $delBasketIds[] = $item->getId();
                                }
                                $props = $item->getPropertyCollection();
                                $isGift = false;
                                $isGiftSelected = false;
                                $detachFrom = '';
                                foreach ($props->getPropertyValues() as $propertyValue) {
                                    switch ($propertyValue['CODE']) {
                                        case 'IS_GIFT':
                                            if (!empty($propertyValue['VALUE'])) {
                                                $isGift = true;
                                            }
                                            break;
                                        case 'IS_GIFT_SELECTED':
                                            if (!empty($propertyValue['VALUE']) && $propertyValue['VALUE'] === 'Y') {
                                                $isGiftSelected = true;
                                            }
                                            break;
                                        case 'DETACH_FROM':
                                            if (!empty($propertyValue['VALUE'])) {
                                                $detachFrom = $propertyValue['VALUE'];
                                            }
                                            break;
                                    }
                                }
                                if ($isGift || $isGiftSelected || !empty($detachFrom)) {
                                    if ($item->getId() > 0) {
                                        $delItemsByUnionIds[] = $item->getId();
                                    } else {
                                        $delItemsByUnionKeys[] = $item->getInternalIndex();
                                    }
                                    if (!empty($detachFrom)) {
                                        $addQuantityBasketIds[$detachFrom] = $item->getQuantity();
                                    }
                                }
                            }
                            $basketPrice = $userBasket->getPrice();
                        }
                    }
                }
            } catch (Exception $e) {
                /** обработка ниже */
            }
        }

        try {
            $this->userAuthorizationService->login($rawLogin, $password);
            if ($this->userAuthorizationService->isAuthorized()
                && !$this->currentUserProvider->getCurrentUser()
                    ->hasPhone()) {
                $needWritePhone = true;
            }
            if ($this->userAuthorizationService->isAuthorized()) {
                unset($_SESSION['COUNT_AUTH_AUTHORIZE']);
            }
        } catch (UsernameNotFoundException $e) {
            if ($_SESSION['COUNT_AUTH_AUTHORIZE'] >= $token-$this->getLimitAuthAuthorizeAttempts($rawLogin)) {
                try {
                    $this->setSocial();
                    $html = $this->getHtml(
                        'begin',
                        '',
                        [
                            'isAjax'   => true,
                            'backUrl'  => $backUrl,
                            'arResult' => $this->arResult
                        ]
                    );

                    return $this->ajaxMess->getWrongPasswordError(array_merge(
                        ['html' => $html],
                        $newTokenResponse
                    ));
                } catch (Exception $e) {
                    return $this->ajaxMess->getSystemError()->extendData($newTokenResponse);
                }
            }

            return $this->ajaxMess->getWrongPasswordError($newTokenResponse);
        } catch (InvalidCredentialException $e) {
            if (($_SESSION['COUNT_AUTH_AUTHORIZE'] >= $this->getLimitAuthAuthorizeAttempts($rawLogin)) && $this->showBitrixCaptcha($rawLogin)) {
                try {
                    $this->setSocial();
                    $html = $this->getHtml(
                        'begin',
                        '',
                        [
                            'isAjax'   => true,
                            'backUrl'  => $backUrl,
                            'arResult' => $this->arResult
                        ]
                    );

                    return $this->ajaxMess->getWrongPasswordError(array_merge(
                        ['html' => $html],
                        $newTokenResponse
                    ));
                } catch (Exception $e) {
                    return $this->ajaxMess->getSystemError()->extendData($newTokenResponse);
                }
            }

            return $this->ajaxMess->getWrongPasswordError($newTokenResponse);
        } catch (TooManyUserFoundException $e) {
            /** @noinspection PhpUnhandledExceptionInspection */
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $logger = LoggerFactory::create('auth');
            $logger->info('Найдено больше одного совпадения по логину/email/телефону ' . $rawLogin);

            try {
                return $this->ajaxMess->getTooManyUserFoundException($this->getSitePhone(), $rawLogin,
                    'логином/email/телефоном')->extendData($newTokenResponse);
            } catch (ApplicationCreateException $e) {
                return $this->ajaxMess->getSystemError()->extendData($newTokenResponse);
            }
        }

        if ($needConfirmBasket) {
            $html = $this->getHtml(
                'unionBasket',
                'Объединение корзины',
                [
                    'backUrl'             => $backUrl,
                    'needAddPhone'        => $needWritePhone ? 'Y' : 'N',
                    'delBasketIds'        => $delBasketIds,
                    'delBasketKeys'       => $delBasketKeys,
                    'delItemsByUnion'     => $delItemsByUnionIds,
                    'delItemsByUnionKeys' => $delItemsByUnionKeys,
                    'addQuantityByUnion'  => $addQuantityBasketIds,
                    'sum'                 => $basketPrice,
                ]
            );

            return JsonSuccessResponse::createWithData('Объединение корзины', [
                'html'    => $html,
                'backUrl' => $backUrl
            ]);
        }
        if ($needWritePhone) {
            $html = $this->getHtml('addPhone', 'Добавление телефона', ['backurl' => $backUrl]);

            return JsonSuccessResponse::createWithData('Необходимо заполнить номер телефона',
                [
                    'html'    => $html,
                    'backUrl' => $backUrl
                ]);
        }

        $options = ['reload' => true];
        if (!empty($backUrl)) {
            $options = ['redirect' => $backUrl];
        }

        $userID = $this->getCurrentUserProvider()->getCurrentUserId();
        $data['email'] = $this->getCurrentUserProvider()->getCurrentUser()->getEmail();
        $data['name'] = $this->getCurrentUserProvider()->getCurrentUser()->getName();
        $data['pets'] = [];
        /** @var PetService $petService */
        $petService = App::getInstance()->getContainer()->get('pet.service');
        $data['pets'] = $petService->getUserPetsTypesCodes($userID);

        return JsonSuccessResponse::createWithData('Вы успешно авторизованы.', $data, 200, $options);
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
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|RuntimeException|Exception $e) {
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
        if ($_SESSION['COUNT_AUTH_CONFIRM_CODE'] > 3 && $this->isShowCapthca()) {
            try {
                $recaptchaService = $container->get(ReCaptchaInterface::class);
                $checkedCaptcha = $recaptchaService->checkCaptcha();
            } catch (ServiceNotFoundException|ServiceCircularReferenceException|SystemException|RuntimeException|Exception $e) {
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
                        [
                            'phone'   => $phone,
                            'backUrl' => $backUrl
                        ]
                    );

                    return $this->ajaxMess->getWrongConfirmCode(['html' => $html]);
                }

                return $this->ajaxMess->getWrongConfirmCode();
            }
        } catch (ExpiredConfirmCodeException $e) {
            if ($_SESSION['COUNT_AUTH_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml(
                    'sendSmsCode',
                    'Подтверждение телефона',
                    [
                        'phone'   => $phone,
                        'backUrl' => $backUrl
                    ]
                );

                return $this->ajaxMess->getExpiredConfirmCodeException(['html' => $html]);
            }

            return $this->ajaxMess->getExpiredConfirmCodeException();
        } catch (NotFoundConfirmedCodeException $e) {
            if ($_SESSION['COUNT_AUTH_CONFIRM_CODE'] === 3) {
                $html = $this->getHtml(
                    'sendSmsCode',
                    'Подтверждение телефона',
                    [
                        'phone'   => $phone,
                        'backUrl' => $backUrl
                    ]
                );

                return $this->ajaxMess->getNotFoundConfirmedCodeException(['html' => $html]);
            }

            return $this->ajaxMess->getNotFoundConfirmedCodeException();
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        } catch (ServiceNotFoundException | ServiceCircularReferenceException | RuntimeException | Exception $e) {
            /**
             * @skip
             */
        }

        unset($_SESSION['COUNT_AUTH_CONFIRM_CODE']);
        $data = [
            'UF_PHONE_CONFIRMED' => true,
            'PERSONAL_PHONE'     => $phone,
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
                    if (!empty($phone)) {
                        $manzanaPhone = PhoneHelper::getManzanaPhone($phone);
                        $contactId = $manzanaService->getContactIdByPhone($manzanaPhone);
                        $client = new Client();
                        if (!empty($contactId)) {
                            $client->contactId = $contactId;
                        }
                        $client->phone = $manzanaPhone;
                    }
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
                    } catch (ApplicationCreateException | ServiceNotFoundException | ServiceCircularReferenceException $e) {
                        /**
                         * @skip
                         */
                    }
                }
            }
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (Exception $e) {
            /**
             * @skip
             */
        }

        $options = ['reload' => true];
        if (!empty($backUrl)) {
            $options = ['redirect' => $backUrl];
        }

        return JsonSuccessResponse::create('Телефон сохранен', 200, [], $options);
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
        $backUrl = $request->get('backUrl', '');
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
        $phone = PhoneHelper::formatPhone($phone, PhoneHelper::FORMAT_FULL);
        $html = $this->getHtml($step, $title, [
            'phone'   => $phone,
            'step'    => $step,
            'backUrl' => $backUrl
        ]);

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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxUnionBasket(Request $request): JsonResponse
    {
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            return $this->ajaxMess->getSystemError();
        }

        $delItemsByUnion = $request->get('del_items_by_union', []);
        if (!empty($delItemsByUnion)) {
            $delItemsByUnion = explode(',', $delItemsByUnion);
        }

        $delItemsByUnionKeys = $request->get('del_items_by_union_keys', []);
        if (!empty($delItemsByUnionKeys)) {
            $delItemsByUnionKeys = explode(',', $delItemsByUnionKeys);
        }

        $addQuantityByUnion = $request->get('add_quantity_by_union', []);
        if (!empty($addQuantityByUnion)) {
            $addQuantityByUnion = json_decode($addQuantityByUnion, true);
        }

        if (!empty($delItemsByUnion) || !empty($addQuantityByUnion) || !empty($delItemsByUnionKeys)) {
            try {
                $basketService = $container->get(BasketService::class);
            } catch (ServiceNotFoundException|ServiceCircularReferenceException|RuntimeException $e) {
                return $this->ajaxMess->getSystemError();
            }

            if (\is_array($delItemsByUnionKeys) && !empty($delItemsByUnionKeys)) {
                foreach ($delItemsByUnionKeys as $key) {
                    try {
                        $basketItem = $basketService->getBasket()->getItemByIndex($key);
                        if ($basketItem !== null) {
                            $id = $basketItem->getId();
                            if ($id > 0) {
                                $basketService->deleteOfferFromBasket($id);
                            }
                        }
                    } catch (ObjectNotFoundException|BitrixProxyException|Exception $e) {
                        return $this->ajaxMess->getSystemError();
                    }
                }
            }

            if (\is_array($delItemsByUnion) && !empty($delItemsByUnion)) {
                foreach ($delItemsByUnion as $id) {
                    try {
                        $basketService->deleteOfferFromBasket($id);
                    } catch (ObjectNotFoundException|BitrixProxyException|Exception $e) {
                        return $this->ajaxMess->getSystemError();
                    }
                }
            }

            if (\is_array($addQuantityByUnion) && !empty($addQuantityByUnion)) {
                foreach ($addQuantityByUnion as $id => $quantity) {
                    try {
                        $basketService->getBasket()->getBasketItems();
                        $basketItem = $basketService->getBasket()->getItemById($id);
                        if ($basketItem === null) {
                            $oldQuantity = 0;
                        } else {
                            $oldQuantity = $basketItem->getQuantity();
                        }
                        $basketService->updateBasketQuantity($id, $oldQuantity + $quantity);
                    } catch (ObjectNotFoundException | BitrixProxyException | Exception $e) {
                        return $this->ajaxMess->getSystemError();
                    }
                }
            }
        }

        $backUrl = $request->get('backurl', '');
        $needAddPhone = $request->get('need_add_phone', 'N');

        $data = [];
        $options = [];

        if ($needAddPhone === 'Y') {
            $data = [
                'html' => $this->getHtml('addPhone', 'Добавление телефона'),
                ['backurl' => $backUrl]
            ];
        } else {
            $options = ['reload' => true];

            if ($backUrl) {
                $options = ['redirect' => $backUrl];
            }
        }

        return JsonSuccessResponse::createWithData('Корзины объединены', $data, 200, $options);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxNotUnionBasket(Request $request): JsonResponse
    {
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            return $this->ajaxMess->getSystemError();
        }

        $delBasketItems = $request->get('del_basket_items', []);
        if (!empty($delBasketItems)) {
            $delBasketItems = explode(',', $delBasketItems);
        }

        $delItemsByKeys = $request->get('del_basket_items_by_keys', []);
        if (!empty($delItemsByKeys)) {
            $delItemsByKeys = explode(',', $delItemsByKeys);
        }

        if (!empty($delBasketItems) || !empty($delBasketItems)) {
            try {
                $basketService = $container->get(BasketService::class);
            } catch (ServiceNotFoundException | ServiceCircularReferenceException | RuntimeException $e) {
                return $this->ajaxMess->getSystemError();
            }

            if (\is_array($delItemsByKeys) && !empty($delItemsByKeys)) {
                foreach ($delItemsByKeys as $key) {
                    try {
                        $basketItem = $basketService->getBasket()->getItemByIndex($key);
                        if ($basketItem !== null) {
                            $id = $basketItem->getId();
                            if ($id > 0) {
                                $basketService->deleteOfferFromBasket($id, [BasketService::GIFT_DOBROLAP_XML_ID, BasketService::GIFT_DOBROLAP_XML_ID_ALT]);
                            }
                        }
                    } catch (ObjectNotFoundException|BitrixProxyException|Exception $e) {
                        try {
                            $logger = LoggerFactory::create('basket');
                            $logger->critical('Ошибка удаления - ' . $e->getMessage());
                        } catch (RuntimeException $e) {
                            /** оч. плохо - логи мы не получим */
                        }

                        return $this->ajaxMess->getSystemError();
                    }
                }
            }

            if (\is_array($delBasketItems) && !empty($delBasketItems)) {
                foreach ($delBasketItems as $id) {
                    try {
                        $basketService->deleteOfferFromBasket($id, [BasketService::GIFT_DOBROLAP_XML_ID, BasketService::GIFT_DOBROLAP_XML_ID_ALT]);
                    } catch (ObjectNotFoundException|BitrixProxyException|Exception $e) {
                        return $this->ajaxMess->getSystemError();
                    }
                }
            }
        }

        $backUrl = $request->get('backurl', '');
        $needAddPhone = $request->get('need_add_phone', 'N');
        $data = [];
        $options = [];

        if ($needAddPhone === 'Y') {
            $data = [
                'html' => $this->getHtml('addPhone', 'Добавление телефона'),
                ['backurl' => $backUrl]
            ];
        } else {
            $options = ['reload' => true];

            if ($backUrl) {
                $options = ['redirect' => $backUrl];
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
        $authManager = new \CSocServAuthManager();
        $startParams['AUTH_SERVICES'] = false;
        $startParams['CURRENT_SERVICE'] = false;
        $startParams['FORM_TYPE'] = 'login';
        $services = $authManager->GetActiveAuthServices($startParams);

        if ($services) {
            foreach ($services as &$service) {
                $service['ONCLICK'] = $this->renderDataLayerByType(DataLayer::SOCIAL_SERVICE_MAP[$service['ID']]
                        ?? '') . $service['ONCLICK'];
            }
            unset($service);

            $this->arResult['AUTH_SERVICES'] = $services;
            $authServiceId =
                Application::getInstance()->getContext()->getRequest()->get('auth_service_id');
            if ($authServiceId !== ''
                && isset($authServiceId, $this->arResult['AUTH_SERVICES'][$authServiceId])) {
                $this->arResult['CURRENT_SERVICE'] = $authServiceId;
                $authServiceError =
                    Application::getInstance()->getContext()->getRequest()->get('auth_service_error');
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

        $this->arResult['LOGIN'] = $this->getRawLogin();

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
        } catch (Exception $e) {
            return $this->ajaxMess->getSystemError();
        }

        $data = [
            'UF_PHONE_CONFIRMED' => false,
        ];

        try {
            if (!$this->currentUserProvider->getUserRepository()->updateData(
                $this->currentUserProvider->getCurrentUserId(),
                $data
            )) {
                return $this->ajaxMess->getUpdateError();
            }
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        } catch (Exception $e) {
            return $this->ajaxMess->getSystemError();
        }

        return $mess;
    }

    protected function isShowCapthca()
    {
        return !KioskService::isKioskMode();
    }

    protected function showBitrixCaptcha($rawLogin = '')
    {
        $this->arResult['IS_SHOW_CAPTCHA'] = ($_SESSION['COUNT_AUTH_AUTHORIZE'] >= $this->getLimitAuthAuthorizeAttempts($rawLogin));

        return $this->arResult['IS_SHOW_CAPTCHA'];
    }

    protected function isShowBitrixCaptcha($word, $code)
    {
        return !empty($code) && !empty($word);
    }

    /**
     * @param string $rawLogin
     * @return int
     */
    protected function getLimitAuthAuthorizeAttempts(string $rawLogin = ''): int
    {
        if ($this->limitAuthAuthorizeAttempts === null) {
            if (!$rawLogin || empty($rawLogin)) {
                return UserService::DEFAULT_AUTH_ATTEMPTS;
            }

            try {
                $this->limitAuthAuthorizeAttempts = $this->userSearchService->getLimitAuthAuthorizeAttemptsByRawLogin($rawLogin);
            } catch (\Exception $e) {
                return UserService::DEFAULT_AUTH_ATTEMPTS;
            }
        }

        return $this->limitAuthAuthorizeAttempts;
    }

    protected function getRawLogin(): string
    {
        if (isset($this->arResult['LOGIN']) && !empty($this->arResult['LOGIN'])) {
            return $this->arResult['LOGIN'];
        }

        $rowLogin = Context::getCurrent()->getRequest()->getCookie('LOGIN');
        if ($rowLogin && !empty($rowLogin)) {
            return $rowLogin;
        }

        return '';
    }
}
