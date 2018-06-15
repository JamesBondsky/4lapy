<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\WordHelper;
use FourPaws\SaleBundle\Exception\DeliveryNotAvailableException;
use FourPaws\SaleBundle\Exception\OrderStorageSaveException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderSplitService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\PaymentService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
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
     * @var array
     */
    protected $paymentCodeToName;

    /**
     * @var PickupResultInterface
     */
    protected $pickup;

    /**
     * FourPawsOrderShopListComponent constructor.
     *
     * @param null $component
     *
     * @throws \RuntimeException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws SystemException
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
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws OrderStorageSaveException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @return bool
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
    public function getShopsInfo(): array
    {
        $result = [];

        if (!$pickup = $this->getPickupResult()) {
            return $result;
        }

        $stores = $pickup->getBestShops();
        if (!$stores->isEmpty()) {
            $avgGpsN = 0;
            $avgGpsS = 0;

            $metroList = $this->getMetroInfo($stores);

            /** @var Store $store */
            $shopCount = 0;
            foreach ($stores as $store) {
                try {
                    $result['items'][] = $this->getShopData($pickup, $store, $metroList, false);
                    $shopCount++;
                    $avgGpsN += $store->getLongitude();
                    $avgGpsS += $store->getLatitude();
                } catch (DeliveryNotAvailableException $e) {
                }
            }

            if ($shopCount) {
                $result['avg_gps_n'] = $avgGpsN / $shopCount;
                $result['avg_gps_s'] = $avgGpsS / $shopCount;
            }
        }

        return $result;
    }

    /**
     * @param string                     $xmlId
     * @param PickupResultInterface|null $pickup
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws Exception
     * @throws NotFoundException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderStorageSaveException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     * @return array
     */
    public function getShopInfo(string $xmlId, ?PickupResultInterface $pickup = null): array
    {
        $result = [];

        $pickup = $pickup instanceof PickupResultInterface ? $pickup : $this->getPickupResult();
        if ($pickup) {
            $stores = $pickup->getBestShops();
            $metroList = $this->getMetroInfo($stores);
            /** @var Store $store */
            foreach ($stores as $store) {
                try {
                    if ($store->getXmlId() === $xmlId) {
                        $result['items'][] = $this->getShopData($pickup, $store, $metroList, true);
                    }
                } catch (DeliveryNotAvailableException $e) {
                }
            }
        }

        return $result;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param PickupResultInterface $pickup
     * @param Store                 $store
     * @param array                 $metroList
     * @param bool                  $recalculateBasket
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotAvailableException
     * @throws NotFoundException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderStorageSaveException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     * @return array
     */
    protected function getShopData(
        PickupResultInterface $pickup,
        Store $store,
        array $metroList,
        bool $recalculateBasket = false
    ): array
    {
        $fullResult = (clone $pickup)->setSelectedShop($store);
        if (!$fullResult->isSuccess()) {
            throw new DeliveryNotAvailableException(sprintf('Pickup from shop %s is unavailable', $store->getXmlId()));
        }
        $paymentCodeToName = $this->getPaymentInfo();

        $showTime = $this->deliveryService->isInnerPickup($pickup);

        [$available, $delayed] = $this->orderSplitService->splitStockResult($fullResult);
        $canGetPartial = $this->orderSplitService->canGetPartial($fullResult);
        $canSplit = $this->orderSplitService->canSplitOrder($fullResult);

        $partialResult = ($canSplit || $canGetPartial) ? (clone $fullResult)->setStockResult($available) : $fullResult;

        $address = $store->getAddress();
        if ($store->getMetro()) {
            $address = 'м. ' . $metroList[$store->getMetro()]['UF_NAME'] . ', ' . $address;
        }

        $orderType = 'full';
        if ($canGetPartial) {
            $orderType = 'parts';
        } elseif ($canSplit) {
            $orderType = 'split';
        } elseif (!$delayed->isEmpty()) {
            $orderType = 'delay';
        }

        $partsFull = $this->getItemData($fullResult->getStockResult());
        $partsDelayed = $this->getItemData($delayed);

        /**
         * пересчет корзины для частичного получения
         */
        if ($recalculateBasket && $canGetPartial) {
            $available = $this->orderSplitService->recalculateStockResult($available);
        }
        $partsAvailable = $this->getItemData($available);

        $price = $canGetPartial ? $available->getPrice() : $fullResult->getStockResult()->getPrice();

        $storePayments = [];
        $paymentCodes = $this->paymentService->getAvailablePaymentsForStore($store);
        foreach ($paymentCodes as $code) {
            if (isset($paymentCodeToName[$code])) {
                $storePayments[] = [
                    'name' => $paymentCodeToName[$code],
                    'code' => $code,
                ];
            }
        }

        return [
            'id'                => $store->getXmlId(),
            'adress'            => $address,
            'name'              => $address,
            'phone'             => $store->getPhone(),
            'schedule'          => $store->getScheduleString(),
            'pickup'            => DeliveryTimeHelper::showTime(
                $available->isEmpty() ? $fullResult : $partialResult,
                ['SHORT' => false, 'SHOW_TIME' => $showTime]
            ),
            'pickup_full'       => DeliveryTimeHelper::showTime(
                $fullResult,
                ['SHORT' => false, 'SHOW_TIME' => $showTime]
            ),
            'pickup_short'      => DeliveryTimeHelper::showTime(
                $available->isEmpty() ? $fullResult : $partialResult,
                ['SHORT' => true, 'SHOW_TIME' => $showTime]
            ),
            'pickup_short_full' => DeliveryTimeHelper::showTime(
                $fullResult,
                ['SHORT' => true, 'SHOW_TIME' => $showTime]
            ),
            'metroClass'        => $store->getMetro() ? '--' . $metroList[$store->getMetro()]['BRANCH']['UF_CLASS'] : '',
            'order'             => $orderType,
            'parts_available'   => $partsAvailable,
            'parts_delayed'     => $partsDelayed,
            'full'              => $partsFull,
            'price'             => WordHelper::numberFormat($price),
            'full_price'        => WordHelper::numberFormat($fullResult->getStockResult()->getPrice()),
            /* @todo поменять местами gps_s и gps_n */
            'gps_n'             => $store->getLongitude(),
            'gps_s'             => $store->getLatitude(),
            'payments'          => $storePayments,
        ];
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws NotFoundException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderStorageSaveException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     * @return array
     */
    protected function getPaymentInfo(): array
    {
        if (null === $this->paymentCodeToName && ($pickup = $this->getPickupResult())) {
            $storage = clone $this->orderStorageService->getStorage();
            $storage->setDeliveryPlaceCode('');
            $storage->setDeliveryId($pickup->getDeliveryId());
            $payments = $this->orderStorageService->getAvailablePayments($storage, false, false);
            /** @var array $payment */
            foreach ($payments as $payment) {
                $this->paymentCodeToName[$payment['CODE']] = $payment['NAME'];
            }
        }

        return $this->paymentCodeToName;
    }

    /**
     *
     * @param StoreCollection $stores
     *
     * @throws \Exception
     * @return array
     */
    protected function getMetroInfo(StoreCollection $stores): array
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
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     * @throws NotFoundException
     * @throws OrderStorageSaveException
     * @throws StoreNotFoundException
     * @return PickupResultInterface|null
     */
    protected function getPickupResult(): ?PickupResultInterface
    {
        if (null === $this->pickup) {
            $deliveries = $this->orderStorageService->getDeliveries($this->orderStorageService->getStorage());
            foreach ($deliveries as $delivery) {
                if ($this->deliveryService->isPickup($delivery)) {
                    $this->pickup = $delivery;
                    break;
                }
            }
        }

        return $this->pickup;
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
                'name'     => $item->getOffer()->getName(),
                'quantity' => $item->getAmount(),
                'price'    => $item->getPrice(),
                'weight'   => $item->getOffer()->getCatalogProduct()->getWeight(),
            ];
        }

        return $result;
    }
}
