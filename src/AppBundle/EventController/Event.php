<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\EventController;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\EventManager;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\Search\Helper\IndexHelper;
use RuntimeException;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\AppBundle\EventController
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
    public static function initHandlers(EventManager $eventManager): void
    {
        self::$eventManager = $eventManager;

        /** исправление буфера вывода - доп обертка - дикая дикость - но проблема в прологе common/bitrix/modules/main/classes/general/main.php - стркои 3420-3432
         * при нахождении более лучшего варианта выпилить
         */
//        self::initHandler('OnPageStart', [static::class, 'startBuffer'], 'main');
//        self::initHandler('OnAfterEpilog', [static::class, 'endBuffer'], 'main');
    }

    /**
     *
     *
     * @param string $eventName
     * @param callable $callback
     * @param string $module
     *
     */
    public static function initHandler(string $eventName, callable $callback, string $module = 'main'): void
    {
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            $callback
        );
    }

    /**
     * @param $id
     */
    public static function startBuffer(): void
    {
        ob_start();
    }

    public static function endBuffer(): void
    {
        $contant = ob_get_clean();
        $contant = preg_replace('/^\n*/', '', $contant);
        echo $contant;
    }
}
