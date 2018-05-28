<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Dpd;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Shipment as BitrixShipment;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Factory\CalculationResultFactory;
use FourPaws\DeliveryBundle\Handler\DeliveryHandlerBase;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use Ipolh\DPD\Delivery\DPD;

if (!Loader::includeModule('ipol.dpd')) {
    class Calculator
    {
    }

    return;
}

class Calculator extends DPD
{
    public const LOCATION_RU = '0000028023';

    /** @var BitrixShipment */
    public static $bitrixShipment;

    public static function callback($method)
    {
        return [__CLASS__, $method];
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param bool $arOrder
     *
     * @return Shipment
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws CityNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected static function makeShipment($arOrder = false)
    {
        $defaultDimensions = [
            'WEIGHT' => 1, // 1g
            'WIDTH'  => 100, // 10cm
            'HEIGHT' => 100, // 10cm
            'LENGTH' => 100, // 10cm
        ];
        if (!self::$shipment || $arOrder) {
            self::$shipment = new Shipment(\FourPaws\DeliveryBundle\Dpd\Lib\User::getInstance());
            self::$shipment
                ->setSender($arOrder['LOCATION_FROM'])
                ->setReceiver($arOrder['LOCATION_TO'])
                ->setItems($arOrder['ITEMS'], $arOrder['PRICE'], $defaultDimensions);
        }

        return self::$shipment;
    }

    /**
     * @param string $profile
     * @param array  $config
     * @param array  $order
     * @param int    $STEP
     * @param bool   $TEMP
     *
     * @return array
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws CityNotFoundException
     * @throws ObjectPropertyException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    public function Calculate($profile, $config, $order, $STEP, $TEMP = false):array
    {
        $serviceContainer = Application::getInstance()->getContainer();
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

        $order['LOCATION_FROM'] = $order['LOCATION_TO'];
        $deliveryZone = $deliveryService->getDeliveryZoneByDelivery(
            $order['LOCATION_TO'],
            $deliveryId
        );

        if (!$deliveryZone) {
            $result = [
                'RESULT' => 'ERROR',
                'TEXT'   => 'Не определена зона доставки',
            ];

            return $result;
        }

        /**
         * Если есть склады в данном городе, то доставка DPD выполняется с этих складов. Иначе - с Москвы
         */
        $storesAvailable = $storeService->getStoresByLocation($order['LOCATION_FROM'], StoreService::TYPE_STORE, true);
        if ($storesAvailable->isEmpty()) {
            $order['LOCATION_FROM'] = LocationService::LOCATION_CODE_MOSCOW;
            $storesAvailable = DeliveryHandlerBase::getAvailableStores(
                $profileCode,
                $deliveryZone,
                $order['LOCATION_FROM']
            );
        }

        $result = parent::Calculate($profile, $config, $order, $STEP, $TEMP);
        if ($result['RESULT'] === 'ERROR') {
            return $result;
        }

        $stockResult = null;
        $terminals = new StoreCollection();
        if (!empty($order['ITEMS'])) {
            $basket = static::$bitrixShipment->getParentOrder()->getBasket()->getOrderableItems();
            if ($offers = DeliveryHandlerBase::getOffers(
                $order['LOCATION_FROM'],
                $basket
            )) {
                $stockResult = DeliveryHandlerBase::getStocks($basket, $offers, $storesAvailable);
                if ($stockResult->getOrderable()->isEmpty()) {
                    $result = [
                        'RESULT' => 'ERROR',
                        'TEXT'   => 'Отсутствуют товары в наличии',
                    ];

                    return $result;
                }

                /**
                 * Получаем пункты самовывоза DPD
                 */
                if ($profileCode === DeliveryService::DPD_PICKUP_CODE) {
                    $shipment = self::makeShipment($order);
                    $terminals = $shipment->getDpdTerminals();
                }
            }
        }

        $cacheKey = CalculationResultFactory::getDpdCacheKey($order);
        CalculationResultFactory::$dpdData[$cacheKey][$profileCode] = [
            'TERMINALS'     => $terminals,
            'DAYS_FROM'     => $result['DPD_TARIFF']['DAYS'],
            'STOCK_RESULT'  => $stockResult,
            'DELIVERY_ZONE' => $deliveryZone,
        ];

        $result['VALUE'] = floor($result['VALUE']);

        return $result;
    }

    /**
     * @param array $arOrder
     * @param array $arConfig
     *
     * @return array
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function Compability($arOrder, $arConfig):array
    {
        /** @var StoreService $storeService */
        $storeService = Application::getInstance()->getContainer()->get('store.service');
        /**
         * Если есть склады в данном местоположении, то доставка производится с них,
         * иначе - со складов Мск
         */
        $arOrder['LOCATION_FROM'] = $arOrder['LOCATION_TO'];
        $stores = $storeService->getStoresByLocation($arOrder['LOCATION_TO'], StoreService::TYPE_STORE);
        if ($stores->isEmpty()) {
            $arOrder['LOCATION_FROM'] = LocationService::LOCATION_CODE_MOSCOW;
        }
        try {
            $shipment = self::makeShipment($arOrder);
            if (($arOrder['LOCATION_TO'] === static::LOCATION_RU) || $shipment->isPossibileSelfDelivery()) {
                $profiles = ['COURIER', 'PICKUP'];
            } elseif ($shipment->isPossibileDelivery()) {
                $profiles = ['COURIER'];
            }
        } catch (CityNotFoundException $e) {
            $profiles = [];
        }


        if (!empty($profiles)) {
            $event = new Event(IPOLH_DPD_MODULE, 'onCompabilityBefore', [$profiles, $arOrder, $arConfig]);
            $event->send();

            foreach ($event->getResults() as $eventResult) {
                if ((int)$eventResult->getType() !== EventResult::SUCCESS) {
                    continue;
                }

                $profiles = array_unique($eventResult->getParameters());
            }
        }

        return $profiles;
    }
}

DPD::$needIncludeComponent = false;
$eventManager = EventManager::getInstance();
$events = [
    'OnSaleComponentOrderOneStepDelivery',
    'OnSaleComponentOrderOneStepPaySystem',
    'OnSaleComponentOrderOneStepDelivery',
];

foreach ($events as $event) {
    $handlers = $eventManager->findEventHandlers('sale', $event);
    foreach ($handlers as $i => $handler) {
        if (\in_array('\\' . DPD::class, $handler['CALLBACK'], true)) {
            $eventManager->removeEventHandler('sale', $event, $i);
        }
    }
}
