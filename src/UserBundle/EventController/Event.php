<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\EventController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\main\Application as BitrixApplication;
use Bitrix\Main\EventManager;
use FourPaws\App\Application as App;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\MainTemplate;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Service\UserPasswordService;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;
use FourPaws\UserBundle\Service\UserSearchInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\UserBundle\EventController
 */
class Event extends BaseServiceHandler
{
    public const GROUP_ADMIN = 1;
    public const GROUP_TECHNICAL_USERS = 8;
    public const GROUP_FRONT_OFFICE_USERS = 28;

    protected static $isEventsDisable = false;

    public static function disableEvents(): void
    {
        self::$isEventsDisable = true;
    }

    public static function enableEvents(): void
    {
        self::$isEventsDisable = false;
    }

    /**
     * @param EventManager $eventManager
     *
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        /** установка обязательного поля при регистрации через соц. сеть */
        static::initHandlerCompatible('OnBeforeUserAdd', [self::class, 'checkSocserviseRegisterHandler'], 'main');

        /** События форматирования телефона */
        static::initHandlerCompatible('OnBeforeUserAdd', [self::class, 'checkPhoneFormat'], 'main');
        static::initHandlerCompatible('OnBeforeUserUpdate', [self::class, 'checkPhoneFormat'], 'main');

        /** замена логина */
        static::initHandlerCompatible('OnBeforeUserLogon', [self::class, 'replaceLogin'], 'main');

        /** фикс базовой авторизации */
        static::initHandlerCompatible('onBeforeUserLoginByHttpAuth', [self::class, 'deleteBasicAuth'], 'main');

        /** деактивация перед регистрацией */
        static::initHandlerCompatible('OnBeforeUserRegister', [self::class, 'preventAuthorizationOnRegister'], 'main');

        /** отправка email после регистрации */
        static::initHandlerCompatible('OnAfterUserRegister', [self::class, 'sendEmail'], 'main');

        /** обновление данных в манзане */
        static::initHandlerCompatible('OnAfterUserUpdate', [self::class, 'updateManzana'], 'main');

        /** обновляем логин если он равняется телефону или email */
        static::initHandlerCompatible('OnBeforeUserUpdate', [self::class, 'replaceLoginOnUpdate'], 'main');

        /** Работа с паролями некоторых групп пользователей (see FRONT_OFFICE_USERS)*/
        static::initHandlerCompatible('OnBeforeUserUpdate', [self::class, 'checkPasswordChange'], 'main');
        static::initHandlerCompatible('OnAfterUserAdd', [self::class, 'resetStoreUserPassword'], 'main');

        /** очистка кеша пользователя */
        static::initHandlerCompatible('OnAfterUserUpdate', [self::class, 'clearUserCache'], 'main');

        /** чистим кеш юзера при авторизации */
        static::initHandlerCompatible('OnAfterUserAuthorize', [self::class, 'clearUserCache'], 'main');
        static::initHandlerCompatible('OnAfterUserLogin', [self::class, 'clearUserCache'], 'main');
        static::initHandlerCompatible('OnAfterUserLoginByHash', [self::class, 'clearUserCache'], 'main');

        /** действия при авторизации(обновление группы оптовиков, обновление карты) */
        static::initHandlerCompatible('OnAfterUserAuthorize', [self::class, 'refreshUserOnAuth'], 'main');
        static::initHandlerCompatible('OnAfterUserLogin', [self::class, 'refreshUserOnAuth'], 'main');
        static::initHandlerCompatible('OnAfterUserLoginByHash', [self::class, 'refreshUserOnAuth'], 'main');

        /** деавторизация перед авторизацией - чтобы не мешали корзины с уже авторизованными юзерами */
        static::initHandlerCompatible('OnBeforeUserLogin', [self::class, 'logoutBeforeAuth'], 'main');
        static::initHandlerCompatible('OnBeforeUserLoginByHash', [self::class, 'logoutBeforeAuth'], 'main');

        /** поиск юзера по email при регистрации из соцсетей */
        static::initHandlerCompatible('OnFindSocialservicesUser', [self::class, 'findSocialServicesUser'],
            'socialservices');
    }

    /**
     * @param array $fields
     */
    public static function checkPhoneFormat(array &$fields): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        if ($fields['PERSONAL_PHONE'] ?? '') {
            try {
                $fields['PERSONAL_PHONE'] = PhoneHelper::normalizePhone($fields['PERSONAL_PHONE']);
            } catch (WrongPhoneNumberException $e) {
                unset($fields['PERSONAL_PHONE']);
            }
        }
    }

    /**
     * @param array $fields
     *
     * @return bool|void
     */
    public static function checkSocserviseRegisterHandler(array &$fields)
    {
        if (self::$isEventsDisable) {
            return;
        }

        if ($fields['EXTERNAL_AUTH_ID'] === 'socservices') {
            /** Установка обязательных пользовательских полей */
            $fields['UF_CONFIRMATION'] = 1;
        }
    }

    /**
     * @param array $fields
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public static function replaceLogin(array $fields): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        global $APPLICATION;
        $userService = App::getInstance()->getContainer()->get(UserRegistrationProviderInterface::class);
        if (!empty($fields['LOGIN'])) {
            $fields['LOGIN'] = $userService->getLoginByRawLogin((string)$fields['LOGIN']);
        } else {
            $APPLICATION->ThrowException('Поле не может быть пустым');
        }
    }

    /**
     * @param array $auth
     */
    public static function deleteBasicAuth(&$auth): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        if (\is_array($auth) && isset($auth['basic'])) {
            unset($auth['basic']);
        }
    }

    /**
     * @param $fields
     */
    public static function preventAuthorizationOnRegister(&$fields): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        $fields['ACTIVE'] = 'N';
    }

    /**
     * @param $fields
     *
     * @throws \RuntimeException
     */
    public static function sendEmail($fields): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        if ($_SESSION['SEND_REGISTER_EMAIL'] && (int)$fields['USER_ID'] > 0 && !empty($fields['EMAIL'])) {
            /** отправка письма о регистрации */
            try {
                $container = App::getInstance()->getContainer();
                $userService = $container->get(CurrentUserProviderInterface::class);
                $user = $userService->getUserRepository()->find((int)$fields['USER_ID']);
                if ($user instanceof User) {
                    $expertSenderService = $container->get('expertsender.service');
                    $expertSenderService->sendEmailAfterRegister($user);
                    /** установка в сессии ссылки коризны если инициализирвоали из корзины */
                    if ($_SESSION['FROM_BASKET']) {
                        setcookie('BACK_URL', '/cart/', time() + ConfirmCodeService::EMAIL_LIFE_TIME, '/');
                        $_COOKIE['BACK_URL'] = '/cart/';
                        unset($_SESSION['FROM_BASKET']);
                    }
                }
            } catch (\Exception $e) {
                $logger = LoggerFactory::create('expertsender');
                $logger->error(sprintf('Error send email: %s', $e->getMessage()));
            }
            unset($_SESSION['SEND_REGISTER_EMAIL']);
        }
    }

    /**
     * @param $fields
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     *
     * @return bool
     */
    public static function updateManzana($fields): bool
    {
        if (self::$isEventsDisable) {
            return false;
        }

        if (!isset($_SESSION['NOT_MANZANA_UPDATE'])) {
            $_SESSION['NOT_MANZANA_UPDATE'] = false;
        }
        if (!$_SESSION['NOT_MANZANA_UPDATE']) {
            try {
                $container = App::getInstance()->getContainer();
            } catch (ApplicationCreateException $e) {
                /** если вызывается эта ошибка вероятно умерло все */
                return false;
            }
            unset($_SESSION['NOT_MANZANA_UPDATE']);

            $userService = $container->get(CurrentUserProviderInterface::class);
            $user = $userService->getUserRepository()->find((int)$fields['ID']);
            if ($user === null) {
                return false;
            }

            $manzanaService = $container->get('manzana.service');

            $client = new Client();
            if ($_SESSION['MANZANA_CONTACT_ID']) {
                $client->contactId = $_SESSION['MANZANA_CONTACT_ID'];
                unset($_SESSION['MANZANA_CONTACT_ID']);
            }

            /** устанавливаем всегда все поля для передачи - что на обновление что на регистарцию */
            $userService->setClientPersonalDataByCurUser($client, $user);

            $manzanaService->updateContactAsync($client);
        }
        return true;
    }

    /**
     * @param $fields
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public static function replaceLoginOnUpdate(&$fields): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        $notReplacedGroups = [static::GROUP_ADMIN, static::GROUP_TECHNICAL_USERS, static::GROUP_FRONT_OFFICE_USERS];
        if (!empty($fields['PERSONAL_PHONE']) || !empty($fields['EMAIL'])) {
            try {
                $container = App::getInstance()->getContainer();
                $userService = $container->get(UserSearchInterface::class);
                $user = $userService->getUserRepository()->find((int)$fields['ID']);

                if ($user instanceof User && $user->getActive()) {
                    foreach ($notReplacedGroups as $groupId) {
                        foreach ($user->getGroups() as $group) {
                            if ($group->getId() === $groupId) {
                                return;
                            }
                        }
                    }
                    $oldEmail = $user->getEmail();
                    $oldPhone = $user->getPersonalPhone();
                    $oldLogin = $user->getLogin();
                    if (!empty($fields['PERSONAL_PHONE'])) {
                        if ($oldPhone !== $fields['PERSONAL_PHONE'] || $fields['PERSONAL_PHONE'] !== $oldLogin) {
                            $fields['LOGIN'] = $fields['PERSONAL_PHONE'];
                        }
                    } else {
                        if (!empty($oldPhone)) {
                            $fields['LOGIN'] = $oldPhone;
                        } elseif (!empty($fields['EMAIL'])) {
                            $fields['LOGIN'] = $fields['EMAIL'];
                        } elseif (!empty($oldEmail)) {
                            $fields['LOGIN'] = $oldEmail;
                        }
                    }
                }
            } catch (ApplicationCreateException $e) {
                /** если вызывается эта ошибка вероятно умерло все */
            }
        }
    }

    /**
     * @param $arFields
     */
    public static function clearUserCache($arFields): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        TaggedCacheHelper::clearManagedCache([
            'user:' . $arFields['ID'],
        ]);
    }

    /**
     * Если авторизованы, то выходим перед авторизацией.
     */
    public static function logoutBeforeAuth(): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        /** @var UserAuthorizationInterface $userService */
        try {
            $userService = App::getInstance()->getContainer()->get(UserAuthorizationInterface::class);
            if ($userService->isAuthorized()) {
                $userService->logout();
            }
        } catch (\Exception $e) {
            //ошибка сервиса - попробуем через глобальный объект
            global $USER;
            if (!\is_object($USER)) {
                $USER = new \CUser();
            }
            if ($USER->IsAuthorized()) {
                $USER->Logout();
            }
        }
    }

    public static function refreshUserOnAuth(): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        try {
            /** @var MainTemplate $template */
            $template = MainTemplate::getInstance(BitrixApplication::getInstance()->getContext());
            /** выполняем только при пользовательской авторизации(это аякс), либо из письма и обратных ссылок(это personal)
             *  так же чекаем что это не страница заказа
             */
            if (!$template->hasUserAuth()) {
                return;
            }
            $container = App::getInstance()->getContainer();
            $userService = $container->get(CurrentUserProviderInterface::class);

            /** обновление номера карты на авторизации */
            $userService->refreshUserCard($userService->getCurrentUser());
            /** обновление группы оптовиков */
            $userService->refreshUserOpt($userService->getCurrentUser());
        } catch (NotAuthorizedException $e) {
            // обработка не требуется
        } catch (\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('failed to update user account balance: ' . $e->getMessage());
        }
    }

    /**
     * @param array $fields
     *
     * @return int
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public static function findSocialServicesUser(array $fields): int
    {
        if (self::$isEventsDisable) {
            return 0;
        }

        $result = 0;
        if ($fields['EMAIL']) {
            $serviceContainer = App::getInstance()->getContainer();

            $userAuthService = $serviceContainer->get(UserAuthorizationInterface::class);
            /** @var UserSearchInterface $userSearchService */
            $userSearchService = $serviceContainer->get(UserSearchInterface::class);
            try {
                $result = $userSearchService->findOneByEmail($fields['EMAIL'])->getId();
                if (!$userAuthService->isAuthorized()) {
                    $serviceContainer
                        ->get('flash.message')
                        ->add('Пользователь с таким e-mail уже зарегистрирован');
                }
            } catch (NotFoundException $e) {
            }
        }

        return $result;
    }

    /**
     * Проверяет можно ли пользователю сменить пароль и если нельзя ансетит поля пароля
     *
     * @param array $fields
     *
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws ApplicationCreateException
     *
     * @return bool
     */
    public function checkPasswordChange(array &$fields): bool
    {
        global $APPLICATION;
        $result = true;
        if ($fields['PASSWORD'] || $fields['CONFIRM_PASSWORD']) {
            $serviceContainer = App::getInstance()->getContainer();
            $userPasswordService = $serviceContainer->get(UserPasswordService::class);
            if (
                !$userPasswordService->isChangePasswordPossibleForAll()
                &&
                !$userPasswordService->isChangePasswordPossible($fields['ID'])
            ) {
                unset($fields['PASSWORD'], $fields['CONFIRM_PASSWORD']);
                $APPLICATION->ThrowException('Вам запрещено менять пароль');
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param array $fields
     *
     * @throws InvalidIdentifierException
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function resetStoreUserPassword(array &$fields)
    {
        if ($fields['RESULT'] && \defined('ADMIN_SECTION') && ADMIN_SECTION) {
            dump($fields);
            $serviceContainer = App::getInstance()->getContainer();
            $userPasswordService = $serviceContainer->get(UserPasswordService::class);
            if (!$userPasswordService->isChangePasswordPossible($fields['RESULT'])) {
                $userPasswordService->resetPassword($fields['RESULT']);
            }
        }
    }
}
