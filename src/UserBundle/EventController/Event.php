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
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
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

        $module = 'main';
        /** установка обязательного поля при регистрации через соц. сеть */
        static::initHandlerCompatible('OnBeforeUserAdd', [self::class, 'checkSocserviseRegisterHandler'], $module);

        /** События форматирования телефона */
        static::initHandlerCompatible('OnBeforeUserAdd', [self::class, 'checkPhoneFormat'], $module);
        static::initHandlerCompatible('OnBeforeUserUpdate', [self::class, 'checkPhoneFormat'], $module);

        /** замена логина */
        static::initHandlerCompatible('OnBeforeUserLogon', [self::class, 'replaceLogin'], $module);

        /** фикс базовой авторизации */
        static::initHandlerCompatible('onBeforeUserLoginByHttpAuth', [self::class, 'deleteBasicAuth'], $module);

        /** деактивация перед регистрацией */
        static::initHandlerCompatible('OnBeforeUserRegister', [self::class, 'preventAuthorizationOnRegister'], $module);

        /** отправка email после регистрации */
        static::initHandlerCompatible('OnAfterUserRegister', [self::class, 'sendEmail'], $module);

        /** обновление данных в манзане */
        static::initHandlerCompatible('OnAfterUserUpdate', [self::class, 'updateManzana'], $module);

        /** обновляем логин если он равняется телефону или email */
        static::initHandlerCompatible('OnBeforeUserUpdate', [self::class, 'replaceLoginOnUpdate'], $module);

        /** очистка кеша пользователя */
        static::initHandlerCompatible('OnAfterUserUpdate', [self::class, 'clearUserCache'], $module);

        /** чистим кеш юзера при авторизации */
        static::initHandlerCompatible('OnAfterUserAuthorize', [self::class, 'clearUserCache'], $module);
        static::initHandlerCompatible('OnAfterUserLogin', [self::class, 'clearUserCache'], $module);
        static::initHandlerCompatible('OnAfterUserLoginByHash', [self::class, 'clearUserCache'], $module);

        /** действия при авторизации(обновление группы оптовиков, обновление карты) */
        static::initHandlerCompatible('OnAfterUserAuthorize', [self::class, 'refreshUserOnAuth'], $module);
        static::initHandlerCompatible('OnAfterUserLogin', [self::class, 'refreshUserOnAuth'], $module);
        static::initHandlerCompatible('OnAfterUserLoginByHash', [self::class, 'refreshUserOnAuth'], $module);

        /** деавторизация перед авторизацией - чтобы не мешали корзины с уже авторизованными юзерами */
        static::initHandlerCompatible('OnBeforeUserLogin', [self::class, 'logoutBeforeAuth'], $module);
        static::initHandlerCompatible('OnBeforeUserLoginByHash', [self::class, 'logoutBeforeAuth'], $module);

        $module = 'socialservices';
        /** поиск юзера по email при регистрации из соцсетей */
        static::initHandlerCompatible('OnFindSocialservicesUser', [self::class, 'findSocialServicesUser'], $module);
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
     * @return bool
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
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
            } catch (NotFoundException $e) {}
        }

        return $result;
    }
}
