<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LocationBundle\EventController;

use Bitrix\Main\EventManager;
use FourPaws\App\BaseServiceHandler;
use FourPaws\Helpers\TaggedCacheHelper;

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
            static::class,
            'resetLocationGroupCache',
        ], $module);
        static::initHandler('GroupOnUpdate', [
            static::class,
            'resetLocationGroupCache',
        ], $module);
        static::initHandler('GroupOnDelete', [
            static::class,
            'resetLocationGroupCache',
        ], $module);
    }

    public static function resetLocationGroupCache()
    {
        TaggedCacheHelper::clearManagedCache(['location:groups']);
    }
}
