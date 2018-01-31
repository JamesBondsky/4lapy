<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Entity\Store;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\CalculationResult;

CBitrixComponent::includeComponentClass('fourpaws:shop.list');

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderShopListComponent extends FourPawsShopListComponent
{
    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    public function __construct($component = null)
    {
        $this->orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        $this->deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        parent::__construct($component);
    }

    public function onPrepareComponentParams($params)
    {
        $params['CACHE_TYPE'] = 'N';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws \RuntimeException
     */
    protected function prepareResult()
    {
        parent::prepareResult();

        $serviceContainer = Application::getInstance()->getContainer();

        /** @var BasketService $basketService */
        $basketService = $serviceContainer->get(BasketService::class);

        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return;
        }

        /** @var StockResultCollection $stockResult */
        if (!$stockResult = $this->getStockResult()) {
            return;
        }

        $offerIds = [];
        $basket = $basketService->getBasket()->getOrderableItems();
        /** @var BasketItem $item */
        foreach ($basket as $item) {
            if (!$item->getProductId()) {
                continue;
            }

            $offerIds[] = $item->getProductId();
        }

        if ($basket->isEmpty() || empty($offerIds)) {
            throw new \RuntimeException('Basket is empty');
        }

        $offers = (new OfferQuery())->withFilterParameter('ID', $offerIds)->exec();
        if ($offers->isEmpty()) {
            throw new \RuntimeException('Offers not found');
        }

        $shops = $stockResult->getStores();

        $resultByShop = [];
        $shopsPartial = [];
        $shopsFull = [];

        /** @var Store $shop */
        foreach ($shops as $shop) {
            $resultByShop[$shop->getXmlId()] = $this->getStoreData($pickupDelivery, $stockResult, $shop);
            if ($resultByShop[$shop->getXmlId()]['DELAYED_AMOUNT'] > 0) {
                $shopsPartial[] = $shop;
            } else {
                $shopsFull[] = $shop;
            }
        }

        /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router $router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routeCollection */
        $storeListUrlRoute = null;
        if ($routeCollection = $router->getRouteCollection()) {
            $storeListUrlRoute = $routeCollection->get('fourpaws_sale_ajax_order_storesearch');
        }
        $this->arResult['STORE_LIST_URL'] = $storeListUrlRoute ? $storeListUrlRoute->getPath() : '';
        $this->arResult['SHOPS'] = $shops;
        $this->arResult['SHOPS_PARTIAL'] = $shopsPartial;
        $this->arResult['SHOPS_FULL'] = $shopsFull;
        $this->arResult['BASKET'] = $basket;
        $this->arResult['STOCK_RESULT_BY_SHOP'] = $resultByShop;
        $this->arResult['OFFERS'] = $offers;
    }

    /**
     * @param array $filter
     * @param array $order
     *
     * @return array
     */
    public function getStores(array $filter = [], array $order = [], $returnActiveServices = false): array
    {
        $result = [];

        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return $result;
        }

        $stores = $this->getStoreList($filter, $order);
        if (!empty($stores)) {
            $stockResult = $this->getStockResult();
            list($servicesList, $metroList) = $this->getFullStoreInfo($stores);

            $avgGpsN = 0;
            $avgGpsS = 0;

            /** @var Store $store */
            foreach ($stores as $store) {
                $resultByStore = $this->getStoreData($pickupDelivery, $stockResult, $store);

                $metro = $store->getMetro();

                $services = [];
                if (\is_array($servicesList) && !empty($servicesList)) {
                    foreach ($servicesList as $service) {
                        $services[] = $service['UF_NAME'];
                    }
                }

                $avgGpsN += $store->getLongitude();
                $avgGpsS += $store->getLatitude();

                $address = !empty($metro)
                    ? 'м. ' . $metroList[$metro]['UF_NAME'] . ', ' . $store->getAddress()
                    : $store->getAddress();

                $stockResultByStore = $resultByStore['STOCK_RESULT'];
                $delayed = $stockResultByStore->getDelayed();

                /** @var StockResultCollection $stockResultByStore */
                $stockResultByStore = $resultByStore['STOCK_RESULT'];
                $available = $stockResultByStore->getAvailable();
                $delayed = $stockResultByStore->getDelayed();

                $partsDelayed = [];
                /** @var \FourPaws\DeliveryBundle\Entity\StockResult $item */
                foreach ($delayed as $item) {
                    $partsDelayed[] = [
                        'name'     => $item->getOffer()->getName(),
                        'quantity' => $item->getAmount(),
                        'price'    => $item->getPrice(),
                    ];
                }

                $partsAvailable = [];
                /** @var \FourPaws\DeliveryBundle\Entity\StockResult $item */
                foreach ($available as $item) {
                    $partsAvailable[] = [
                        'name'     => $item->getOffer()->getName(),
                        'quantity' => $item->getAmount(),
                        'price'    => $item->getPrice(),
                    ];
                }

                if ($available->isEmpty()) {
                    $orderType = 'full';
                } else {
                    if ($delayed->isEmpty()) {
                        $orderType = 'delay';
                    } else {
                        $orderType = 'parts';
                    }
                }

                $result['items'][] = [
                    'id'              => $store->getXmlId(),
                    'adress'          => $address,
                    'phone'           => $store->getPhone(),
                    'schedule'        => $store->getSchedule(),
                    'pickup'          => DeliveryTimeHelper::showTime(
                        $resultByStore['PARTIAL_RESULT'],
                        $delayed->isEmpty() ? $stockResultByStore->getDeliveryDate() : $delayed->getDeliveryDate(),
                        true,
                        false
                    ),
                    'pickup_full'     => DeliveryTimeHelper::showTime(
                        $resultByStore['FULL_RESULT'],
                        $stockResultByStore->getDeliveryDate(),
                        true,
                        false
                    ),
                    'metroClass'      => !empty($metro) ? '--' . $metroList[$metro]['UF_CLASS'] : '',
                    'order'           => $orderType,
                    'parts_available' => $partsAvailable,
                    'parts_delayed'   => $partsDelayed,
                    'services'        => $services,
                    'price'           => $delayed->isEmpty() ? $stockResultByStore->getPrice() : $delayed->getPrice(),
                    'full_price'      => $stockResultByStore->getPrice(),
                    /* @todo поменять местами gps_s и gps_n */
                    'gps_n'           => $store->getLongitude(),
                    'gps_s'           => $store->getLatitude(),
                ];
                $avgGpsN += $store->getLongitude();
                $avgGpsS += $store->getLatitude();
            }
            $countStores = count($stores);
            /* @todo поменять местами gps_s и gps_n */
            $result['avg_gps_s'] = $avgGpsN / $countStores;
            $result['avg_gps_n'] = $avgGpsS / $countStores;
            if ($returnActiveServices) {
                $result['services'] = $servicesList;
            }
        }

        return $result;
    }

    protected function getStoreList(array $filter, array $order): array
    {
        /** @var StockResultCollection $stockResult */
        if (!$stockResult = $this->getStockResult()) {
            return [];
        }

        $defaultFilter = [];
        /** @var Store $store */
        $idFilter = [];
        foreach ($stockResult->getStores() as $store) {
            $idFilter[] = $store->getId();
        }
        if (!empty($idFilter)) {
            $defaultFilter['ID'] = $idFilter;
        }

        return parent::getStoreList(array_merge($filter, $defaultFilter), $order);
    }

    /**
     * @return CalculationResult|null
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
     * @return bool|StockResultCollection
     */
    protected function getStockResult()
    {
        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return false;
        }

        return $this->deliveryService->getStockResultByDelivery($pickupDelivery);
    }

    /**
     * @param CalculationResult $delivery
     * @param StockResultCollection $stockResult
     * @param Store $store
     *
     * @return array
     */
    protected function getStoreData(
        CalculationResult $delivery,
        StockResultCollection $stockResult,
        Store $store
    ): array {
        $result = [];
        $stockResultByStore = $stockResult->filterByStore($store);
        $delayed = $stockResultByStore->getDelayed();
        $available = $stockResultByStore->getAvailable();

        $result['AVAILABLE_AMOUNT'] = $available->isEmpty() ? 0 : $available->getAmount();
        $result['DELAYED_AMOUNT'] = $delayed->isEmpty() ? 0 : $delayed->getAmount();

        $fullResult = clone $delivery;
        $partialResult = clone $delivery;

        DeliveryTimeHelper::updateDeliveryDate($partialResult, $available->getDeliveryDate());
        DeliveryTimeHelper::updateDeliveryDate($fullResult, $stockResultByStore->getDeliveryDate());

        $result['PARTIAL_RESULT'] = $partialResult;
        $result['FULL_RESULT'] = $fullResult;
        $result['STOCK_RESULT'] = $stockResultByStore;

        return $result;
    }
}
