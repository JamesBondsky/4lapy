<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\EventController;

use Bitrix\Main\EventManager;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\MobileApiBundle\Services\Session\SessionHandlerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\MobileApiBundle\EventController
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
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        self::$eventManager = $eventManager;

        self::initHandler('OnUserLogin', 'updateTokenAfterLogin');
        self::initHandler('OnAfterUserLogout', 'updateTokenAfterLogout');
        self::initHandler('onAfterUserUpdate', 'updateUser');
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public static function updateTokenAfterLogin()
    {
        $sessionHandler = Application::getInstance()->getContainer()->get(SessionHandlerInterface::class);
        $sessionHandler->login();
    }

    /**
     * @param array $fields
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public static function updateTokenAfterLogout(array &$fields)
    {
        $sessionHandler = Application::getInstance()->getContainer()->get(SessionHandlerInterface::class);
        $sessionHandler->logout();
    }

    /**
     * @param array $fields
     *
     * @throws ApplicationCreateException
     */
    public static function updateUser(array &$fields)
    {
        if ($fields['RESULT'] && $fields['ID'] ?? 0) {
            $sessionHandler = Application::getInstance()->getContainer()->get(SessionHandlerInterface::class);
            $sessionHandler->update((int)$fields['ID']);
        }
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
            ],
            false,
            1
        );
    }
}
