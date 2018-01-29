<?php

namespace FourPaws\DeliveryBundle;

use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use FourPaws\App\ServiceHandlerInterface;

class Event implements ServiceHandlerInterface
{
    /**
     * @param EventManager $eventManager
     */
    public static function initHandlers(EventManager $eventManager)
    {
        $eventManager->addEventHandler(
            'sale',
            'onSaleDeliveryHandlersClassNamesBuildList',
            [__CLASS__, 'addCustomDeliveryServices']
        );

        $eventManager->addEventHandler(
            'sale',
            'onSaleDeliveryRestrictionsClassNamesBuildList',
            [__CLASS__, 'addCustomRestrictions']
        );
    }

    /**
     * @return EventResult
     */
    public static function addCustomDeliveryServices()
    {
        $result = new EventResult(
            EventResult::SUCCESS,
            [
                Service\InnerDeliveryService::class => __DIR__ . '/Service/InnerDeliveryService.php',
                Service\InnerPickupService::class   => __DIR__ . '/Service/InnerPickupService.php',
            ]
        );

        return $result;
    }

    /**
     * @return EventResult
     */
    public static function addCustomRestrictions()
    {
        return new EventResult(
            EventResult::SUCCESS,
            [
                Restrictions\LocationExceptRestriction::class => __DIR__ . '/Restrictions/LocationExceptRestriction.php',
            ]
        );
    }
}
