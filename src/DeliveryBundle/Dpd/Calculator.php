<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Dpd;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;

use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Handler\DeliveryHandlerBase;
use FourPaws\Location\LocationService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Service\StoreService;

if (!Loader::includeModule('ipol.dpd')) {
    class Calculator
    {
    }

    return;
}

use Ipolh\DPD\Delivery\DPD;

class Calculator extends DPD
{
    public static function callback($method)
    {
        return [__CLASS__, $method];
    }

    public function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
    {
        $serviceContainer = Application::getInstance()->getContainer();
        /** @var BasketService $basketService */
        $basketService = $serviceContainer->get(BasketService::class);
        /** @var StoreService $storeService */
        $storeService = $serviceContainer->get('store.service');
        /** @var DeliveryService $deliveryService */
        $deliveryService = $serviceContainer->get('delivery.service');

        $profileCode = $profile === 'PICKUP' ? DeliveryService::DPD_PICKUP_CODE : DeliveryService::DPD_DELIVERY_CODE;

        try {
            $deliveryId = $deliveryService->getDeliveryIdByCode($profileCode);
        } catch (NotFoundException $e) {
            $result = [
                'RESULT' => 'ERROR',
                'TEXT'   => 'Доставка не найдена',
            ];

            return $result;
        }

        $arOrder['LOCATION_FROM'] = $arOrder['LOCATION_TO'];
        $storesAvailable = $storeService->getByLocation($arOrder['LOCATION_FROM'], StoreService::TYPE_STORE, true);
        if ($storesAvailable->isEmpty()) {
            $arOrder['LOCATION_FROM'] = LocationService::LOCATION_CODE_MOSCOW;
            $storesAvailable = $storeService->getByLocation($arOrder['LOCATION_FROM'], StoreService::TYPE_STORE, true);
        }

        $storesDelay = new StoreCollection();

        $result = parent::Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP);
        if ($result['RESULT'] === 'ERROR') {
            return $result;
        }

        if (!empty($arOrder['ITEMS'])) {
            $basket = $basketService->getBasket()->getOrderableItems();
            if ($offers = DeliveryHandlerBase::getOffers(
                $arOrder['LOCATION_FROM'],
                $basket
            )) {
                $stockResult = DeliveryHandlerBase::getStocks($basket, $offers, $storesAvailable, $storesDelay);
                if (!$stockResult->getUnavailable()->isEmpty()) {
                    $result = [
                        'RESULT' => 'ERROR',
                        'TEXT'   => 'Присутствуют товары не в наличии',
                    ];

                    return $result;
                }

                /**
                 * Если есть отложенные товары, то добавляем к дате доставки DPD
                 * срок поставки на склад по графику
                 */
                if (!$stockResult->getDelayed()->isEmpty()) {
                    $result['DPD_TARIFF']['DAYS'] += $stockResult->getDeliveryDate()->diff(new \DateTime())->days;
                }
                /**
                 * Получаем пункты самовывоза DPD
                 */
                if ($profileCode === DeliveryService::DPD_PICKUP_CODE) {
                    $shipment = self::makeShipment($arOrder);
                    $terminals = $shipment->getDpdTerminals();

                    /** @var StockResult $item */
                    foreach ($stockResult as $item) {
                        $item->setStores($terminals);
                    }
                }
            }
        }

        $interval = explode('-', Option::get(IPOLH_DPD_MODULE, 'DELIVERY_TIME_PERIOD'));
        /* по ТЗ - дата доставки DPD для зоны 4 рассчитывается как "то, что вернуло DPD" + 1 день */
        if ($profileCode == DeliveryService::DPD_DELIVERY_CODE &&
            $deliveryService->getDeliveryZoneCodeByLocation(
                $arOrder['LOCATION_TO'],
                $deliveryId
            ) === DeliveryService::ZONE_4
        ) {
            $result['DPD_TARIFF']['DAYS']++;
        }

        $intervals = new IntervalCollection();
        $intervals->add(
            (new Interval())->setFrom($interval[0])
                            ->setTo($interval[1])
        );
        /* @todo не хранить эти данные в сессии */
        $_SESSION['DPD_DATA'][$profileCode] = [
            'INTERVALS'    => $intervals,
            'DAYS_FROM'    => $result['DPD_TARIFF']['DAYS'],
            'DAYS_TO'      => $result['DPD_TARIFF']['DAYS'] + 10,
            'STOCK_RESULT' => $stockResult ?? new StockResultCollection(),
        ];

        $result['VALUE'] = floor($result['VALUE']);

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
                ->setSender($arOrder['LOCATION_FROM'])
                ->setReceiver($arOrder['LOCATION_TO'])
                ->setItems($arOrder['ITEMS'], $arOrder['PRICE'], $defaultDimensions);
        }

        return self::$shipment;
    }

    public function Compability($arOrder, $arConfig)
    {
        /** @var StoreService $storeService */
        $storeService = Application::getInstance()->getContainer()->get('store.service');
        /**
         * Если есть склады в данном местоположении, то доставка производится с них,
         * иначе - со складов Мск
         */
        $arOrder['LOCATION_FROM'] = $arOrder['LOCATION_TO'];
        $stores = $storeService->getByLocation($arOrder['LOCATION_TO'], StoreService::TYPE_STORE);
        if ($stores->isEmpty()) {
            $arOrder['LOCATION_FROM'] = LocationService::LOCATION_CODE_MOSCOW;
        }
        $shipment = self::makeShipment($arOrder);

        if ($shipment->isPossibileSelfDelivery()) {
            $profiles = ['COURIER', 'PICKUP'];
        } elseif ($shipment->isPossibileDelivery()) {
            $profiles = ['COURIER'];
        } else {
            $profiles = [];
        }

        $event = new Event(IPOLH_DPD_MODULE, 'onCompabilityBefore', [$profiles, $arOrder, $arConfig]);
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

DPD::$needIncludeComponent = false;
$eventManager = \Bitrix\Main\EventManager::getInstance();
$events = [
    'OnSaleComponentOrderOneStepDelivery',
    'OnSaleComponentOrderOneStepPaySystem',
    'OnSaleComponentOrderOneStepDelivery',
];

foreach ($events as $event) {
    $handlers = $eventManager->findEventHandlers('sale', $event);
    foreach ($handlers as $i => $handler) {
        if (in_array('\\' . DPD::class, $handler['CALLBACK'], true)) {
            $eventManager->removeEventHandler('sale', $event, $i);
        }
    }
}
