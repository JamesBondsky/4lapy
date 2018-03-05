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
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;
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
    /**
     * @var EventManager
     */
    protected static $eventManager;

    /**
     * @param EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager)
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
    }

    /**
     * @param string $eventName
     * @param string $method
     * @param string $module
     */
    public static function initHandler(string $eventName, string $method, string $module = 'main')
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

    public static function checkPhoneFormat(array &$fields)
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
    public static function replaceLogin(array $fields)
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
    public static function deleteBasicAuth(&$auth)
    {
        if (\is_array($auth) && isset($auth['basic'])) {
            unset($auth['basic']);
        }
    }

    /**
     * @param $fields
     */
    public static function preventAuthorizationOnRegister(&$fields)
    {
        $fields['ACTIVE'] = 'N';
    }

    /**
     * @param $fields
     *
     * @throws \RuntimeException
     */
    public static function sendEmail($fields)
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
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public static function updateManzana($fields)
    {
        if ($_SESSION['MANZANA_UPDATE']) {
            unset($_SESSION['MANZANA_UPDATE']);
            $client = null;
            try {
                $container = App::getInstance()->getContainer();
                $userService = $container->get(CurrentUserProviderInterface::class);
                $user = $userService->getUserRepository()->find((int)$fields['ID']);
                if ($user instanceof User) {
                    $manzanaService = $container->get('manzana.service');
                    $contactId = $manzanaService->getContactIdByPhone($user->getManzanaNormalizePersonalPhone());
                    $client = new Client();
                    $client->contactId = $contactId;
                }
            } catch (ManzanaServiceException $e) {
                $client = new Client();
            } catch (ApplicationCreateException $e) {
                /** если вызывается эта ошибка вероятно умерло все */
            }

            if ($client instanceof Client) {
                $manzanaService->updateContactAsync($client);
            }
        }
    }

    /**
     * @param $fields
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public static function replaceLoginOnUpdate(&$fields)
    {
        if (!empty($fields['PERSONAL_PHONE']) || !empty($fields['EMAIL'])) {
            try {
                $container = App::getInstance()->getContainer();
                $userService = $container->get(CurrentUserProviderInterface::class);
                $user = $userService->getUserRepository()->find((int)$fields['ID']);
                if($user instanceof User) {
                    $oldEmail = $user->getEmail();
                    $oldPhone = $user->getPersonalPhone();
                    $oldLogin = $user->getLogin();
                    if(!empty($fields['PERSONAL_PHONE'])){
                        if ($oldPhone !== $fields['PERSONAL_PHONE'] || $fields['PERSONAL_PHONE'] !== $oldLogin) {
                            $fields['LOGIN'] = $fields['PERSONAL_PHONE'];
                        }
                    }
                    else{
                        if(!empty($oldPhone)) {
                            $fields['LOGIN'] = $oldPhone;
                        }
                        elseif(!empty($fields['EMAIL'])){
                            $fields['LOGIN'] = $fields['EMAIL'];
                        }
                        elseif(!empty($oldEmail)){
                            $fields['LOGIN'] = $oldEmail;
                        }
                    }
                }
            } catch (ApplicationCreateException $e) {
                /** если вызывается эта ошибка вероятно умерло все */
            }
        }
    }
}
