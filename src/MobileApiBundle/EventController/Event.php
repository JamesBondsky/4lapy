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

        // toDo не понятный для чего легаси код
        // $module = 'main';
        // static::initHandler('OnUserLogin', [self::class,'updateTokenAfterLogin'], $module);
        // static::initHandler('OnAfterUserLogout', [self::class,'updateTokenAfterLogout'], $module);
        // static::initHandler('onAfterUserUpdate', [self::class,'updateUser'], $module);
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

        $user_class = new \CUser;
        $user_id = (int) $GLOBALS['USER']->GetID();
        $total_sessions = $user_class::GetByID( $user_id )->Fetch()['UF_SESSION_CNTS'];

        $user_class->Update($user_id, ['UF_SESSION_CNTS' => (int) $total_sessions+1]);

        // TODO: выбрасывает 500, но БД обновляет - нужно думать почему.
        // TODO: P.S при обновлении в битре полей также выкидывает эррор.
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
