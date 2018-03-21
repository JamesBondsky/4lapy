<?php

namespace FourPaws\CatalogBundle\EventController;

use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\EventManager;
use Bitrix\Main\SystemException;
use FourPaws\App\ServiceHandlerInterface;

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
     *
     * @throws SystemException
     */
    public static function clearProductCache($id): void
    {
        if (\defined('BX_COMP_MANAGED_CACHE')) {
            /** Очистка кеша */
            $instance = BitrixApplication::getInstance();
            $tagCache = $instance->getTaggedCache();
            $tagCache->clearByTag('catalog:offer:' . $id);
            $tagCache->clearByTag('catalog:stocks:' . $id);
            $tagCache->clearByTag('catalog:product:' . $id);
        }
    }

    /**
     * @param $id
     *
     * @throws SystemException
     */
    public static function clearIblockItemCache($id): void
    {
        if (\defined('BX_COMP_MANAGED_CACHE')) {
            /** Очистка кеша */
            $instance = BitrixApplication::getInstance();
            $tagCache = $instance->getTaggedCache();
            $tagCache->clearByTag('iblock:item:' . $id);
        }
    }
}
