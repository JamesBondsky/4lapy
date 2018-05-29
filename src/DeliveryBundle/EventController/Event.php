<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\EventController;

use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use FourPaws\App\BaseServiceHandler;
use FourPaws\DeliveryBundle\Handler\InnerDeliveryHandler;
use FourPaws\DeliveryBundle\Handler\InnerPickupHandler;
use FourPaws\DeliveryBundle\InputTypes\DeliveryInterval;
use FourPaws\DeliveryBundle\Restrictions\LocationExceptRestriction;

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
        static::initHandler('onSaleDeliveryHandlersClassNamesBuildList', [self::class, 'addCustomDeliveryServices'],
            $module);
        static::initHandler('onSaleDeliveryRestrictionsClassNamesBuildList', [self::class, 'addCustomRestrictions'],
            $module);
        static::initHandler('registerInputTypes', [self::class, 'addCustomTypes'], $module);
    }

    /**
     * @return EventResult
     */
    public static function addCustomDeliveryServices(): EventResult
    {
        $result = new EventResult(
            EventResult::SUCCESS,
            [
                InnerDeliveryHandler::class => __DIR__ . '/Handler/InnerDeliveryHandler.php',
                InnerPickupHandler::class   => __DIR__ . '/Handler/InnerPickupHandler.php',
            ]
        );

        return $result;
    }

    /**
     * @return EventResult
     */
    public static function addCustomRestrictions(): EventResult
    {
        return new EventResult(
            EventResult::SUCCESS,
            [
                LocationExceptRestriction::class => __DIR__ . '/Restrictions/LocationExceptRestriction.php',
            ]
        );
    }

    /**
     * @return EventResult
     */
    public static function addCustomTypes(): EventResult
    {
        return new EventResult(
            EventResult::SUCCESS,
            [
                'DELIVERY_INTERVALS' => [
                    'NAME'  => 'Интервал доставки',
                    'CLASS' => DeliveryInterval::class,
                ],
            ]
        );
    }
}
