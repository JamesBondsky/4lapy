<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LocationBundle\EventController;

use Bitrix\Main\EventManager;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\Helpers\TaggedCacheHelper;

/**
 * Class Event
 *
 * @package FourPaws\DeliveryBundle
 */
class Event implements ServiceHandlerInterface
{
    /**
     * @param EventManager $eventManager
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('sale', 'GroupOnAdd', [
            static::class,
            'resetLocationGroupCache',
        ]);
        $eventManager->addEventHandler('sale', 'GroupOnUpdate', [
            static::class,
            'resetLocationGroupCache',
        ]);
        $eventManager->addEventHandler('sale', 'GroupOnDelete', [
            static::class,
            'resetLocationGroupCache',
        ]);
    }

    public static function resetLocationGroupCache()
    {
        TaggedCacheHelper::clearManagedCache(['location:groups']);
    }
}
