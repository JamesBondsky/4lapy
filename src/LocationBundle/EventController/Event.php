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
    }

    public static function resetLocationGroupCache(): void
    {
        TaggedCacheHelper::clearManagedCache(['location:groups']);
    }
}
