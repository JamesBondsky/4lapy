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
use FourPaws\Helpers\WordHelper;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderSplitService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\PaymentService;
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

    /**
     * @var OrderSplitService
     */
    protected $orderSplitService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var PaymentService
     */
    protected $paymentService;

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
        $this->orderSplitService = $serviceContainer->get(OrderSplitService::class);
        $this->deliveryService = $serviceContainer->get('delivery.service');
        $this->paymentService = $serviceContainer->get(PaymentService::class);
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
        if ($pickupDelivery = $this->getPickupResult()) {
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
        return true;
    }

    /**
     * @throws Exception
     * @return array
     */
    public function getStoreInfo(): array
    {
        $result = [];

        if (!$pickup = $this->getPickupResult()) {
            return $result;
        }

        $stores = $pickup->getBestShops();
        if (!$stores->isEmpty()) {
            $avgGpsN = 0;
            $avgGpsS = 0;

            $showTime = $this->deliveryService->isInnerPickup($pickup);
            $metroList = $this->getMetroInfo($stores);

            /** @var Store $store */
            $shopCount = 0;
            $storage = clone $this->orderStorageService->getStorage();
            $storage->setDeliveryPlaceCode('');
            $storage->setDeliveryId($pickup->getDeliveryId());
            $payments = $this->orderStorageService->getAvailablePayments($storage, false, false);
            /** @var array $payment */
            $paymentCodeToName = [];
            foreach ($payments as $payment) {
                $paymentCodeToName[$payment['CODE']] = $payment['NAME'];
            }

            foreach ($stores as $store) {
                $fullResult = (clone $pickup)->setSelectedShop($store);
                if (!$fullResult->isSuccess()) {
                    continue;
                }
                $shopCount++;

                [$available, $delayed] = $this->orderSplitService->splitStockResult($fullResult);
                $canGetPartial = $this->orderSplitService->canGetPartial($fullResult);
                $canSplit = $this->orderSplitService->canSplitOrder($fullResult);

                $partialResult = $canGetPartial
                    ? (clone $fullResult)->setStockResult($available)
                    : $fullResult;

                $metro = $store->getMetro();
                $address = !empty($metro)
                    ? 'м. ' . $metroList[$metro]['UF_NAME'] . ', ' . $store->getAddress()
                    : $store->getAddress();

                $orderType = 'full';
                if ($canGetPartial) {
                    $orderType = 'parts';
                } elseif ($canSplit) {
                    $orderType = 'split';
                } elseif ($available->isEmpty()) {
                    $orderType = 'delay';
                }

                $partsFull = $this->getItemData($fullResult->getStockResult());
                $partsDelayed = $this->getItemData($delayed);

                /**
                 * пересчет корзины для частичного получения
                 */
                if ($canGetPartial) {
                    $available = $this->orderSplitService->recalculateStockResult($available);
                }
                $partsAvailable = $this->getItemData($available);

                $price = $canGetPartial ? $available->getPrice() : $fullResult->getStockResult()->getPrice();

                $storePayments = [[
                    'name' => $paymentCodeToName[OrderService::PAYMENT_ONLINE],
                    'code' => OrderService::PAYMENT_ONLINE,
                ]];

                $paymentCodes = $this->paymentService->getAvailablePaymentsForStore($store);
                foreach ($paymentCodes as $code) {
                    if (isset($paymentCodeToName[$code])) {
                        $storePayments[] = [
                            'name' => $paymentCodeToName[$code],
                            'code' => $code,
                        ];
                    }
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
                    'full' => $partsFull,
                    'price' => WordHelper::numberFormat($price),
                    'full_price' => WordHelper::numberFormat($fullResult->getStockResult()->getPrice()),
                    /* @todo поменять местами gps_s и gps_n */
                    'gps_n' => $store->getLongitude(),
                    'gps_s' => $store->getLatitude(),
                    'payments' => $storePayments,
                ];
                $avgGpsN += $store->getLongitude();
                $avgGpsS += $store->getLatitude();
            }

            if ($shopCount) {
                $result['avg_gps_n'] = $avgGpsN / $shopCount;
                $result['avg_gps_s'] = $avgGpsS / $shopCount;
            }
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
            $metroIds[] = $store->getMetro();
        }

        $metro = [];
        $metroIds = \array_filter($metroIds);
        if (!empty($metroIds)) {
            $metro = $this->storeService->getMetroInfo(['ID' => \array_unique($metroIds)]);
        }

        return $metro;
    }

    /**
     * @return PickupResultInterface
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    protected function getPickupResult(): ?PickupResultInterface
    {
        $pickupDelivery = null;
        $deliveries = $this->orderStorageService->getDeliveries($this->orderStorageService->getStorage());
        foreach ($deliveries as $delivery) {
            if ($this->deliveryService->isPickup($delivery)) {
                $pickupDelivery = $delivery;
                break;
            }
        }

        return $pickupDelivery;
    }

    /**
     * @param StockResultCollection $stockResultCollection
     * @return array
     */
    protected function getItemData(StockResultCollection $stockResultCollection): array
    {
        $result = [];
        /** @var StockResult $item */
        foreach ($stockResultCollection as $item) {
            $result[] = [
                'name' => $item->getOffer()->getName(),
                'quantity' => $item->getAmount(),
                'price' => $item->getPrice(),
                'weight' => $item->getOffer()->getCatalogProduct()->getWeight(),
            ];
        }

        return $result;
    }
}
