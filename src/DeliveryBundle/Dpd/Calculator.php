<?php

namespace FourPaws\DeliveryBundle\Dpd;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use FourPaws\DeliveryBundle\Service\DeliveryService;

if (!Loader::includeModule('ipol.dpd')) {
    class Calculator
    {
    }

    return;
}

class Calculator extends \Ipolh\DPD\Delivery\DPD
{
    public static function callback($method)
    {
        return [__CLASS__, $method];
    }

    public function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
    {
        $result = parent::Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP);

        switch ($profile) {
            case 'PICKUP':
                $profile = DeliveryService::DPD_PICKUP_CODE;
                break;
            default:
                $profile = DeliveryService::DPD_DELIVERY_CODE;
                break;
        }

        $interval = explode('-', Option::get(IPOLH_DPD_MODULE, 'DELIVERY_TIME_PERIOD'));

        if ($profile == DeliveryService::DPD_DELIVERY_CODE) {
            $result['DPD_TARIFF']['DAYS']++;
        }

        $_SESSION['DPD_DATA'][$profile] = [
            'INTERVALS' => [
                [
                    'FROM' => $interval[0],
                    'TO'   => $interval[1],
                ],
            ],
            'DAYS'      => $result['DPD_TARIFF']['DAYS'],
        ];

        return $result;
    }

    protected static function makeShipment($arOrder = false)
    {
        $defaultDimensions = [
            'WEIGHT' => 1, // 1g
            'WIDTH'  => 100, // 10cm
            'HEIGHT' => 100, // 10cm
            'LENGTH' => 100, // 10cm
        ];
        if (!self::$shipment || $arOrder) {
            self::$shipment = new Shipment();
            self::$shipment
                ->setSender(Utils::getSaleLocationId())
                ->setReceiver($arOrder['LOCATION_TO'])
                ->setItems($arOrder['ITEMS'], $arOrder['PRICE'], $defaultDimensions);
        }

        return self::$shipment;
    }

    public function Compability($arOrder, $arConfig)
    {
        $shipment = self::makeShipment($arOrder);

        if ($shipment->isPossibileSelfDelivery()) {
            $profiles = ['COURIER', 'PICKUP'];
        } elseif ($shipment->isPossibileDelivery()) {
            $profiles = ['COURIER'];
        } else {
            $profiles = [];
        }

        $event = new Event(IPOLH_DPD_MODULE, "onCompabilityBefore", [$profiles, $arOrder, $arConfig]);
        $event->send();

        foreach ($event->getResults() as $eventResult) {
            if ($eventResult->getType() != EventResult::SUCCESS) {
                continue;
            }

            $profiles = array_unique($eventResult->getParameters());
        }

        return $profiles;
    }
}
