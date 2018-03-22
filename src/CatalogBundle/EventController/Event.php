<?php

namespace FourPaws\CatalogBundle\EventController;

use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\EventManager;
use Bitrix\Main\SystemException;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\Helpers\TaggedCacheHelper;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\CatalogBundle\EventController
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
        /** Очистка кеша при изменении количества и оффера*/
        self::initHandler('OnStoreProductUpdate', [static::class, 'clearProductCache']);
        self::initHandler('OnStoreProductAdd', [static::class, 'clearProductCache']);
        self::initHandler('OnProductUpdate', [static::class, 'clearProductCache']);
        self::initHandler('OnProductAdd', [static::class, 'clearProductCache']);

        /** очистка кеша при изменении элемента инфоблока */
        self::initHandler('OnAfterIBlockElementUpdate', [static::class, 'clearIblockItemCache']);
    }

    /**
     *
     *
     * @param string   $eventName
     * @param callable $callback
     * @param string   $module
     *
     */
    public static function initHandler(string $eventName, callable $callback, string $module = 'catalog'): void
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
    public static function clearProductCache($id): void
    {
        TaggedCacheHelper::clearManagedCache([
            'catalog:offer:' . $id,
            'catalog:stocks:' . $id,
            'catalog:product:' . $id
        ]);
    }

    /**
     * @param $id
     */
    public static function clearIblockItemCache($id): void
    {
        TaggedCacheHelper::clearManagedCache([
            'iblock:item:' . $id,
        ]);
    }
}
