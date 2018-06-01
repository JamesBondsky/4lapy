<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\EventController;

use Bitrix\Main\EventManager;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
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
class Event extends BaseServiceHandler
{
    /**
     * @param EventManager $eventManager
     *
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        $module = 'main';
        static::initHandler('OnUserLogin', [self::class,'updateTokenAfterLogin'], $module);
        static::initHandler('OnAfterUserLogout', [self::class,'updateTokenAfterLogout'], $module);
        static::initHandler('onAfterUserUpdate', [self::class,'updateUser'], $module);
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public static function updateTokenAfterLogin(): void
    {
        $sessionHandler = Application::getInstance()->getContainer()->get(SessionHandlerInterface::class);
        $sessionHandler->login();
    }

    /**
     * @throws ApplicationCreateException
     */
    public static function updateTokenAfterLogout(): void
    {
        $sessionHandler = Application::getInstance()->getContainer()->get(SessionHandlerInterface::class);
        $sessionHandler->logout();
    }

    /**
     * @param array $fields
     *
     * @throws ApplicationCreateException
     */
    public static function updateUser(array &$fields): void
    {
        if ($fields['RESULT'] && $fields['ID'] ?? 0) {
            $sessionHandler = Application::getInstance()->getContainer()->get(SessionHandlerInterface::class);
            $sessionHandler->update((int)$fields['ID']);
        }
    }
}
