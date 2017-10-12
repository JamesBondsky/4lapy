<?php

namespace FourPaws\User;

use Bitrix\Main\EventManager;
use FourPaws\App\ServiceHandlerInterface;

/**
 * Class UserServiceHandlers
 *
 * Обработчики событий
 *
 * @package FourPaws\User
 */
abstract class UserServiceHandlers implements ServiceHandlerInterface
{
    /**
     * @var EventManager
     */
    protected static $eventManager;
    
    public static function initHandlers(EventManager $eventManager)
    {
        self::$eventManager = $eventManager;
        
        self::initHandler('OnBeforeUserAdd', 'checkSocserviseRegisterHandler');
    }
    
    /**
     * @param string $eventName
     * @param string $method
     * @param string $module
     */
    public static function initHandler(string $eventName, string $method, string $module = 'main')
    {
        self::$eventManager->addEventHandler($module,
                                             $eventName,
                                             [
                                                 self::class,
                                                 $method,
                                             ]);
    }
    
    /**
     * @param array $fields
     *
     * @return bool
     */
    public static function checkSocserviseRegisterHandler(array $fields) : bool
    {
        /**
         * @todo может, можно как-то иначе?
         */
        global $APPLICATION;
        
        if ($fields['EXTERNAL_AUTH_ID'] === 'socservices' && !$fields['PERSONAL_PHONE']) {
            $APPLICATION->ThrowException('Phone number must be defined');
            
            return false;
        }
        
        return true;
    }
}