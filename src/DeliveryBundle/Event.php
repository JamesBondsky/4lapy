<?php

namespace FourPaws\DeliveryBundle;

use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;
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
    }

    /**
     * @param BitrixEvent $event
     *
     * @return EventResult
     */
    public static function addCustomDeliveryServices(BitrixEvent $event)
    {
        $result = new EventResult(
            EventResult::SUCCESS,
            [
                '\FourPaws\DeliveryBundle\Service\InnerDeliveryService' => __DIR__ . '/Service/InnerDeliveryService.php',
                '\FourPaws\DeliveryBundle\Service\InnerPickupService'   => __DIR__ . '/Service/InnerPickupService.php',
            ]
        );

        return $result;
    }
}
