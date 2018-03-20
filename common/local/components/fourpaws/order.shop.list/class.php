<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

CBitrixComponent::includeComponentClass('fourpaws:shop.list');

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderShopListComponent extends FourPawsShopListComponent
{
    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /** @var DeliveryService $deliveryService */
    protected $deliveryService;

    /**
     * FourPawsOrderShopListComponent constructor.
     *
     * @param null $component
     *
     * @throws \RuntimeException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \Bitrix\Main\SystemException
     * @throws ApplicationCreateException
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
        $serviceContainer = Application::getInstance()->getContainer();
        $this->orderService = $serviceContainer->get(OrderService::class);
        $this->orderStorageService = $serviceContainer->get(OrderStorageService::class);
        $this->deliveryService = $serviceContainer->get('delivery.service');
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    public function onPrepareComponentParams($params)
    {
        $params['CACHE_TYPE'] = 'N';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @param array $city
     * @return bool
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    protected function prepareResult(array $city = [])
    {
        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return false;
        }

        /* @todo поправить метро у магазинов */
        /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router $router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routeCollection */
        $storeListUrlRoute = null;
        if ($routeCollection = $router->getRouteCollection()) {
            $storeListUrlRoute = $routeCollection->get('fourpaws_sale_ajax_order_storesearch');
        }
        $this->arResult['DELIVERY'] = $pickupDelivery;
        $this->arResult['DELIVERY_CODE'] = $pickupDelivery->getDeliveryCode();
        $this->arResult['STORE_LIST_URL'] = $storeListUrlRoute ? $storeListUrlRoute->getPath() : '';

        return true;
    }

    /**
     * @param array $params
     *
     * @throws Exception
     * @return array
     */
    public function getStores(array $params = []): array
    {
        $result = [];

        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return $result;
        }

        $stores = $this->getStoreList($params['filter'] ?? [], $params['order'] ?? []);
        if (!$stores->isEmpty()) {
            $metroList = $this->getMetroInfo($stores);

            $avgGpsN = 0;
            $avgGpsS = 0;

            $showTime = $this->deliveryService->isInnerPickup($pickupDelivery);
            $bestShops = $pickupDelivery->getBestShops();

            if (!empty($params['filter'])) {
                /**
                 * @var string $xmlId
                 * @var Store $store
                 */
                foreach ($bestShops as $xmlId => $store) {
                    if (!$stores->exists(function (
                        /** @noinspection PhpUnusedParameterInspection */
                        $key, Store $store2) use ($store) {
                        return $store2->getXmlId() === $store->getXmlId();
                    })) {
                        unset($bestShops[$xmlId]);
                    }
                }
            }

            /** @var Store $store */
            $shopCount = 0;
            foreach ($bestShops as $store) {
                $fullResult = (clone $pickupDelivery)->setSelectedStore($store);
                if ($this->deliveryService->isInnerPickup($fullResult)) {
                    $fullResult->setStockResult($pickupDelivery->getStockResult()->filterByStore($store));
                }
                if (!$fullResult->isSuccess()) {
                    continue;
                }
                $shopCount++;
                $partialResult = (clone $fullResult)->setStockResult($fullResult->getStockResult()->getAvailable());

                $metro = $store->getMetro();
                $address = !empty($metro)
                    ? 'м. ' . $metroList[$metro]['UF_NAME'] . ', ' . $store->getAddress()
                    : $store->getAddress();

                /** @var StockResultCollection $available */
                $available = $partialResult->getStockResult();
                $delayed = $fullResult->getStockResult()->getDelayed();

                $partsDelayed = [];
                /** @var StockResult $item */
                foreach ($delayed as $item) {
                    $partsDelayed[] = [
                        'name' => $item->getOffer()->getName(),
                        'quantity' => $item->getAmount(),
                        'price' => $item->getPrice(),
                        'weight' => $item->getOffer()->getCatalogProduct()->getWeight(),
                    ];
                }

                $partsAvailable = [];
                /** @var StockResult $item */
                foreach ($available as $item) {
                    $partsAvailable[] = [
                        'name' => $item->getOffer()->getName(),
                        'quantity' => $item->getAmount(),
                        'price' => $item->getPrice(),
                        'weight' => $item->getOffer()->getCatalogProduct()->getWeight(),
                    ];
                }

                $orderType = 'parts';
                if ($delayed->isEmpty()) {
                    $orderType = 'full';
                } elseif ($available->isEmpty()) {
                    $orderType = 'delay';
                }

                $result['items'][] = [
                    'id' => $store->getXmlId(),
                    'adress' => $address,
                    'phone' => $store->getPhone(),
                    'schedule' => $store->getScheduleString(),
                    'pickup' => DeliveryTimeHelper::showTime(
                        $available->isEmpty() ? $fullResult : $partialResult,
                        ['SHORT' => false, 'SHOW_TIME' => $showTime]
                    ),
                    'pickup_full' => DeliveryTimeHelper::showTime(
                        $fullResult,
                        ['SHORT' => false, 'SHOW_TIME' => $showTime]
                    ),
                    'pickup_short' => DeliveryTimeHelper::showTime(
                        $available->isEmpty() ? $fullResult : $partialResult,
                        ['SHORT' => true, 'SHOW_TIME' => $showTime]
                    ),
                    'pickup_short_full' => DeliveryTimeHelper::showTime(
                        $fullResult,
                        ['SHORT' => true, 'SHOW_TIME' => $showTime]
                    ),
                    'metroClass' => !empty($metro) ? '--' . $metroList[$metro]['BRANCH']['UF_CLASS'] : '',
                    'order' => $orderType,
                    'parts_available' => $partsAvailable,
                    'parts_delayed' => $partsDelayed,
                    'price' => $available->isEmpty() ?
                        $fullResult->getStockResult()->getPrice() :
                        $available->getPrice(),
                    'full_price' => $fullResult->getStockResult()->getPrice(),
                    /* @todo поменять местами gps_s и gps_n */
                    'gps_n' => $store->getLongitude(),
                    'gps_s' => $store->getLatitude(),
                ];
                $avgGpsN += $store->getLongitude();
                $avgGpsS += $store->getLatitude();
            }

            $result['avg_gps_n'] = $avgGpsN / $shopCount;
            $result['avg_gps_s'] = $avgGpsS / $shopCount;
        }

        return $result;
    }

    /**
     *
     * @param StoreCollection $stores
     *
     * @throws \Exception
     * @return array
     */
    public function getMetroInfo(StoreCollection $stores): array
    {
        $metroIds = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $metro = $store->getMetro();
            if ($metro > 0) {
                $metroIds[] = $metro;
            }
        }
        $metro = [];
        if (!empty($metroIds)) {
            $metro = $this->storeService->getMetroInfo(['ID' => array_unique($metroIds)]);
        }

        return $metro;
    }

    /**
     * @param array $filter
     * @param array $order
     *
     * @throws Exception
     * @return StoreCollection
     */
    protected function getStoreList(array $filter, array $order): StoreCollection
    {
        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return new StoreCollection();
        }

        try {

            if ($this->deliveryService->isDpdPickup($pickupDelivery)) {
                return $pickupDelivery->getStockResult()->getStores();
            }

            $defaultFilter = [];
            /** @var Store $store */
            $idFilter = [];
            foreach ($pickupDelivery->getStockResult()->getStores() as $store) {
                $idFilter[] = $store->getId();
            }
            if (!empty($idFilter)) {
                $defaultFilter['ID'] = $idFilter;
            }

            return $this->storeService->getRepository()->findBy(array_merge($filter, $defaultFilter), $order);
        } catch (\FourPaws\DeliveryBundle\Exception\NotFoundException $e) {
            return new StoreCollection();
        }
    }

    /**
     * @return PickupResultInterface
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    protected function getPickupDelivery(): ?PickupResultInterface
    {
        $pickupDelivery = null;
        $deliveries = $this->orderService->getDeliveries($this->orderStorageService->getStorage());
        foreach ($deliveries as $delivery) {
            if ($this->deliveryService->isPickup($delivery)) {
                $pickupDelivery = $delivery;
                break;
            }
        }

        return $pickupDelivery;
    }
}
