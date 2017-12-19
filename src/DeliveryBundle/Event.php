<?php

namespace FourPaws\DeliveryBundle;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;

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

        if (Loader::includeModule('ipol.dpd')) {
            $eventManager->addEventHandlerCompatible(
                IPOLH_DPD_MODULE,
                'onCalculate',
                [__CLASS__, 'onDPDCalculate']
            );
        }
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

    public static function addCustomRestrictions(BitrixEvent $event)
    {
        return new EventResult(
            EventResult::SUCCESS,
            [
                '\FourPaws\DeliveryBundle\Restrictions\LocationExceptRestriction' => __DIR__ . '/Restrictions/LocationExceptRestriction.php',
            ]
        );
    }

    public static function onDPDCalculate($result, $profile)
    {
        switch ($profile) {
            case 'PICKUP':
                $profile = DeliveryService::DPD_PICKUP_CODE;
                break;
            default:
                $profile = DeliveryService::DPD_DELIVERY_CODE;
                break;
        }

        $interval = explode('-', Option::get(IPOLH_DPD_MODULE, 'DELIVERY_TIME_PERIOD'));
        $result['INTERVALS'] = [
            [
                'FROM' => $interval[0],
                'TO'   => $interval[1],
            ],
        ];

        $_SESSION['DPD_DATA'][$profile] = $result;
    }
}
