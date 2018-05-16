<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\EventController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\EventManager;
use FourPaws\App\Application;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
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
class Event implements ServiceHandlerInterface
{
    public const GROUP_ADMIN = 1;
    public const GROUP_TECHNICAL_USERS = 8;
    /**
     * @var EventManager
     */
    protected static $eventManager;

    /**
     * @param EventManager $eventManager
     *
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        self::$eventManager = $eventManager;

        self::initHandler('OnBeforeUserAdd', 'checkSocserviseRegisterHandler');

        /**
         * События форматирования телефона
         */
        self::initHandler('OnBeforeUserAdd', 'checkPhoneFormat');
        self::initHandler('OnBeforeUserUpdate', 'checkPhoneFormat');

        self::initHandler('OnBeforeUserLogon', 'replaceLogin');

        self::initHandler('onBeforeUserLoginByHttpAuth', 'deleteBasicAuth');
        self::initHandler('OnBeforeUserRegister', 'preventAuthorizationOnRegister');
        self::initHandler('OnAfterUserRegister', 'sendEmail');
        self::initHandler('OnAfterUserUpdate', 'updateManzana');

        /** обновляем логин если он равняется телефону или email */
        self::initHandler('OnBeforeUserUpdate', 'replaceLoginOnUpdate');

        /** очистка кеша пользователя */
        self::initHandler('OnAfterUserUpdate', 'clearUserCache');
    }

    /**
     * @param string $eventName
     * @param string $method
     * @param string $module
     */
    public static function initHandler(string $eventName, string $method, string $module = 'main'): void
    {
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            [
                self::class,
                $method,
            ]
        );
    }

    /**
     * @param array $fields
     */
    public static function checkPhoneFormat(array &$fields): void
    {
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
        global $APPLICATION;
        $userService = Application::getInstance()->getContainer()->get(UserRegistrationProviderInterface::class);
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
        if (\is_array($auth) && isset($auth['basic'])) {
            unset($auth['basic']);
        }
    }

    /**
     * @param $fields
     */
    public static function preventAuthorizationOnRegister(&$fields): void
    {
        $fields['ACTIVE'] = 'N';
    }

    /**
     * @param $fields
     *
     * @throws \RuntimeException
     */
    public static function sendEmail($fields): void
    {
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
        if(!isset($_SESSION['NOT_MANZANA_UPDATE'])){
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

            /** contactId получим в очереди */
            $client = new Client();

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
        $notReplacedGroups= [static::GROUP_ADMIN, static::GROUP_TECHNICAL_USERS];
        if (!empty($fields['PERSONAL_PHONE']) || !empty($fields['EMAIL'])) {
            try {
                $container = App::getInstance()->getContainer();
                $userService = $container->get(UserSearchInterface::class);
                $user = $userService->getUserRepository()->find((int)$fields['ID']);

                if ($user instanceof User && $user->getActive()) {
                    foreach($notReplacedGroups as $groupId){
                        foreach ($user->getGroups() as $group) {
                            if($group->getId() === $groupId){
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
    public function clearUserCache($arFields): void
    {
        TaggedCacheHelper::clearManagedCache([
            'user:' . $arFields['ID'],
        ]);
    }
}
