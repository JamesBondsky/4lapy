<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LocationBundle\EventController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\EventManager;
use FourPaws\App\BaseServiceHandler;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\LocationBundle\Repository\Table\LocationParentsTable;

/**
 * Class Event
 *
 * @package FourPaws\DeliveryBundle
 */
class Event extends BaseServiceHandler
{
    /**
     * @param EventManager $eventManager
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        $module = 'sale';
        static::initHandler('GroupOnAdd', [
            self::class,
            'resetLocationGroupCache',
        ], $module);
        static::initHandler('GroupOnUpdate', [
            self::class,
            'resetLocationGroupCache',
        ], $module);
        static::initHandler('GroupOnDelete', [
            self::class,
            'resetLocationGroupCache',
        ], $module);
        static::initHandler('\Bitrix\Sale\Location\Location::OnAfterAdd', [
            self::class,
            'clearLocationsParentsTable',
        ], $module);
        static::initHandler('\Bitrix\Sale\Location\Location::OnAfterUpdate', [
            self::class,
            'clearLocationsParentsTable',
        ], $module);
        static::initHandler('\Bitrix\Sale\Location\Location::OnAfterDelete', [
            self::class,
            'clearLocationsParentsTable',
        ], $module);
    }

    public static function resetLocationGroupCache(): void
    {
        TaggedCacheHelper::clearManagedCache(['location:groups']);
    }

    /**
     * @param \Bitrix\Main\ORM\Event $event
     */
    public static function clearLocationsParentsTable(\Bitrix\Main\ORM\Event $event): void
    {
        // ATTENTION!
        // 1. Если запись об очистке таблицы в логе иногда повторяется много раз подряд, то лучше изменить механизм очистки,
        // чтобы она происходила только один раз после всех изменений/удалений (например, по таймауту).
        // 2. Кроме того, при необходимости можно сделать, чтобы очищались только те элементы, которые были изменены сами
        // или у которых изменились родители

        $tempLogger = LoggerFactory::create(__FUNCTION__);
        $tempLogger->info('Очистка таблицы locations parents. Событие: ' . $event->getEventType());

        LocationParentsTable::deleteAll();
    }
}
