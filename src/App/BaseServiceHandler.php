<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.05.18
 * Time: 18:31
 */

namespace FourPaws\App;


use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\EventManager;
use FourPaws\App\Tools\StaticLoggerTrait;
use Psr\Log\LoggerInterface;

abstract class BaseServiceHandler implements ServiceHandlerInterface
{
    use StaticLoggerTrait;

    /** @var EventManager */
    protected static $eventManager;

    /** @inheritdoc */
    public static function initHandlers(EventManager $eventManager): void
    {
        self::$eventManager = $eventManager;
    }

    /**
     * @param string   $eventName
     * @param callable $callback
     * @param string   $module
     */
    public static function initHandler(string $eventName, callable $callback, string $module = ''): void
    {
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            $callback
        );
    }

    /**
     * @param string   $eventName
     * @param callable $callback
     * @param string   $module
     */
    public static function initHandlerCompatible(string $eventName, callable $callback, string $module = ''): void
    {
        static::$eventManager->addEventHandlerCompatible(
            $module,
            $eventName,
            $callback
        );
    }
}