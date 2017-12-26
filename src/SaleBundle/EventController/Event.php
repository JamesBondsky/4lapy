<?php

namespace FourPaws\SaleBundle\EventController;

use Bitrix\Main\EventManager;
use FourPaws\App\ServiceHandlerInterface;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\SaleBundle\EventController
 */
class Event implements ServiceHandlerInterface
{
    /**
     * @var EventManager
     */
    protected static $eventManager;
    
    /**
     * @param \Bitrix\Main\EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager)
    {
        self::$eventManager = $eventManager;
        
        self::initHandler('HandleSomething', 'doSomething');
    }
    
    /**
     * @param string $eventName
     * @param string $method
     * @param string $module
     */
    public static function initHandler(string $eventName, string $method, string $module = 'sale')
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
     * @return bool|void
     */
    public static function doSomething(array &$fields)
    {
        /**
         * Пример обработчика
         */
    }
}
