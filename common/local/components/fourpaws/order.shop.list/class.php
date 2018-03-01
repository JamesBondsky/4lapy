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
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Service\OrderService;
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
     *
     * @throws \RuntimeException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    protected function prepareResult(array $city = [])
    {
        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return;
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
            list($servicesList, $metroList) = $this->getFullStoreInfo($stores);

            $avgGpsN = 0;
            $avgGpsS = 0;

            $resultByStore = [];

            $stores = $stores->toArray();
            /** @var Store $store */
            foreach ($stores as $store) {
                $resultByStore[$store->getXmlId()] = $this->getStoreData(
                    $pickupDelivery,
                    $store
                );
            }

            /**
             * 1) По убыванию % от суммы товаров заказа в наличии в магазине или на складе
             * 2) По возрастанию даты готовности заказа к выдаче
             * 3) По адресу магазина в алфавитном порядке
             */
            $sortFunc = function (Store $shop1, Store $shop2) use ($resultByStore) {
                $shopData1 = $resultByStore[$shop1->getXmlId()];
                $shopData2 = $resultByStore[$shop2->getXmlId()];

                if ($shopData1['AVAILABLE_AMOUNT'] !== $shopData2['AVAILABLE_AMOUNT']) {
                    return ($shopData1['AVAILABLE_AMOUNT'] > $shopData2['AVAILABLE_AMOUNT']) ? -1 : 1;
                }

                /** @var BaseResult $result1 */
                $result1 = $shopData1['FULL_RESULT'];
                /** @var BaseResult $result2 */
                $result2 = $shopData2['FULL_RESULT'];
                $deliveryDate1 = $result1->getDeliveryDate();
                $deliveryDate2 = $result2->getDeliveryDate();

                if ($deliveryDate1->getTimestamp() !== $deliveryDate2->getTimestamp()) {
                    return ($deliveryDate1->getTimestamp() > $deliveryDate2->getTimestamp()) ? 1 : -1;
                }

                return $shop1->getAddress() > $shop2->getAddress() ? 1 : -1;
            };

            uasort($stores, $sortFunc);

            $showTime = $this->deliveryService->isInnerPickup($pickupDelivery);
            /** @var Store $store */
            foreach ($stores as $store) {
                $metro = $store->getMetro();

                $services = [];
                if (\is_array($servicesList) && !empty($servicesList)) {
                    foreach ($servicesList as $service) {
                        $services[] = $service['UF_NAME'];
                    }
                }

                $address = !empty($metro)
                    ? 'м. ' . $metroList[$metro]['UF_NAME'] . ', ' . $store->getAddress()
                    : $store->getAddress();

                /** @var BaseResult $partialResult */
                $partialResult = $resultByStore[$store->getXmlId()]['PARTIAL_RESULT'];
                /** @var BaseResult $fullResult */
                $fullResult = $resultByStore[$store->getXmlId()]['FULL_RESULT'];
                /** @var StockResultCollection $available */
                $available = $partialResult->getStockResult();
                $delayed = $fullResult->getStockResult()->getDelayed();

                $partsDelayed = [];
                /** @var StockResult $item */
                foreach ($delayed as $item) {
                    $partsDelayed[] = [
                        'name'     => $item->getOffer()->getName(),
                        'quantity' => $item->getAmount(),
                        'price'    => $item->getPrice(),
                        'weight'   => $item->getOffer()->getCatalogProduct()->getWeight(),
                    ];
                }

                $partsAvailable = [];
                /** @var StockResult $item */
                foreach ($available as $item) {
                    $partsAvailable[] = [
                        'name'     => $item->getOffer()->getName(),
                        'quantity' => $item->getAmount(),
                        'price'    => $item->getPrice(),
                        'weight'   => $item->getOffer()->getCatalogProduct()->getWeight(),
                    ];
                }

                $orderType = 'parts';
                if ($delayed->isEmpty()) {
                    $orderType = 'full';
                } elseif ($available->isEmpty()) {
                    $orderType = 'delay';
                }

                $result['items'][] = [
                    'id'                => $store->getXmlId(),
                    'adress'            => $address,
                    'phone'             => $store->getPhone(),
                    'schedule'          => $store->getSchedule(),
                    'pickup'            => DeliveryTimeHelper::showTime(
                        $resultByStore[$store->getXmlId()]['PARTIAL_RESULT'],
                        ['SHORT' => false, 'SHOW_TIME' => $showTime]
                    ),
                    'pickup_full'       => DeliveryTimeHelper::showTime(
                        $resultByStore[$store->getXmlId()]['FULL_RESULT'],
                        ['SHORT' => false, 'SHOW_TIME' => $showTime]
                    ),
                    'pickup_short'      => DeliveryTimeHelper::showTime(
                        $resultByStore[$store->getXmlId()]['PARTIAL_RESULT'],
                        ['SHORT' => true, 'SHOW_TIME' => $showTime]
                    ),
                    'pickup_short_full' => DeliveryTimeHelper::showTime(
                        $resultByStore[$store->getXmlId()]['FULL_RESULT'],
                        ['SHORT' => true, 'SHOW_TIME' => $showTime]
                    ),
                    'metroClass'        => !empty($metro) ? '--' . $metroList[$metro]['BRANCH']['UF_CLASS'] : '',
                    'order'             => $orderType,
                    'parts_available'   => $partsAvailable,
                    'parts_delayed'     => $partsDelayed,
                    'services'          => $services,
                    'price'             => $available->isEmpty() ?
                        $fullResult->getStockResult()->getPrice() :
                        $available->getPrice(),
                    'full_price'        => $fullResult->getStockResult()->getPrice(),
                    /* @todo поменять местами gps_s и gps_n */
                    'gps_n'             => $store->getLongitude(),
                    'gps_s'             => $store->getLatitude(),
                ];
                $avgGpsN += $store->getLongitude();
                $avgGpsS += $store->getLatitude();
            }
            $countStores = count($stores);

            $result['avg_gps_n'] = $avgGpsN / $countStores;
            $result['avg_gps_s'] = $avgGpsS / $countStores;
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
    public function getFullStoreInfo(StoreCollection $stores): array
    {
        $servicesIds = [];
        $metroIds = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $servicesIds = array_merge($servicesIds, $store->getServices());
            $metro = $store->getMetro();
            if ($metro > 0) {
                $metroIds[] = $metro;
            }
        }
        $services = [];
        if (!empty($servicesIds)) {
            $services = $this->storeService->getServicesInfo(['ID' => array_unique($servicesIds)]);
        }

        $metro = [];
        if (!empty($metroIds)) {
            $metro = $this->storeService->getMetroInfo(['ID' => array_unique($metroIds)]);
        }

        return [
            $services,
            $metro,
        ];
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
        } catch (\FourPaws\StoreBundle\Exception\NotFoundException $e) {
            return new StoreCollection();
        }
    }

    /**
     * @return null|BaseResult
     */
    protected function getPickupDelivery()
    {
        $pickupDelivery = null;
        $deliveries = $this->orderService->getDeliveries();
        foreach ($deliveries as $delivery) {
            if ($this->deliveryService->isPickup($delivery)) {
                $pickupDelivery = $delivery;
                break;
            }
        }

        return $pickupDelivery;
    }

    /**
     * @param BaseResult $delivery
     * @param Store $store
     *
     * @return array
     */
    protected function getStoreData(
        BaseResult $delivery,
        Store $store
    ): array {
        $result = [];
        $stockResultByStore = $delivery->getStockResult()->filterByStore($store);
        $delayed = $stockResultByStore->getDelayed();
        $available = $stockResultByStore->getAvailable();

        $result['AVAILABLE_AMOUNT'] = $available->isEmpty() ? 0 : $available->getAmount();
        $result['DELAYED_AMOUNT'] = $delayed->isEmpty() ? 0 : $delayed->getAmount();
        $result['FULL_RESULT'] = (clone $delivery)->setStockResult($stockResultByStore);
        $result['PARTIAL_RESULT'] = (clone $delivery)->setStockResult($available);

        return $result;
    }
}
