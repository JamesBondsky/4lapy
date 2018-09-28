<?php

namespace FourPaws\SaleBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\WordHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\SaleBundle\Dto\OrderSplit\SplitStockResult;
use FourPaws\SaleBundle\Dto\ShopList\Offer as ShopOffer;
use FourPaws\SaleBundle\Dto\ShopList\OfferInfo;
use FourPaws\SaleBundle\Dto\ShopList\Payment;
use FourPaws\SaleBundle\Dto\ShopList\Shop;
use FourPaws\SaleBundle\Dto\ShopList\ShopList;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Enum\OrderAvailability;
use FourPaws\SaleBundle\Exception\DeliveryNotAvailableException;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Enum\StoreLocationType;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use JMS\Serializer\ArrayTransformerInterface;

class ShopInfoService
{
    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * @var OrderSplitService
     */
    protected $orderSplitService;

    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * ShopInfoService constructor.
     *
     * @param LocationService           $locationService
     * @param StoreService              $storeService
     * @param OrderStorageService       $orderStorageService
     * @param DeliveryService           $deliveryService
     * @param PaymentService            $paymentService
     * @param OrderSplitService         $orderSplitService
     * @param ArrayTransformerInterface $arrayTransformer
     */
    public function __construct(
        LocationService $locationService,
        StoreService $storeService,
        OrderStorageService $orderStorageService,
        DeliveryService $deliveryService,
        PaymentService $paymentService,
        OrderSplitService $orderSplitService,
        ArrayTransformerInterface $arrayTransformer
    )
    {
        $this->locationService = $locationService;
        $this->storeService = $storeService;
        $this->orderStorageService = $orderStorageService;
        $this->deliveryService = $deliveryService;
        $this->paymentService = $paymentService;
        $this->orderSplitService = $orderSplitService;
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @param OrderStorage          $orderStorage
     * @param PickupResultInterface $pickupResult
     *
     * @return ShopList
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws NotSupportedException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     * @throws \RuntimeException
     */
    public function getShopInfo(OrderStorage $orderStorage, PickupResultInterface $pickupResult = null): ShopList
    {
        if (null !== $pickupResult) {
            $result = $this->generateShopList($pickupResult->getBestShops(), $orderStorage, $pickupResult);
        } else {
            $result = new ShopList();
        }

        return $result;
    }


    /**
     * @param string                $storeXmlId
     * @param OrderStorage          $orderStorage
     * @param PickupResultInterface $pickupResult
     *
     * @return ShopList
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotFoundException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     * @throws \RuntimeException
     */
    public function getOneShopInfo(string $storeXmlId, OrderStorage $orderStorage, PickupResultInterface $pickupResult): ShopList
    {
        $result = new ShopList();

        if (null !== $pickupResult) {
            try {
                $stores = new StoreCollection([$this->storeService->getStoreByXmlId($storeXmlId)]);

                $result = $this->generateShopList($stores, $orderStorage, $pickupResult);
            } catch (StoreNotFoundException $e) {
            }
        }

        return $result;
    }

    /**
     * @param StoreCollection       $storeCollection
     * @param OrderStorage          $orderStorage
     * @param PickupResultInterface $pickupResult
     *
     * @return ShopList
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotFoundException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws \Exception
     * @throws \RuntimeException
     */
    public function generateShopList(
        StoreCollection $storeCollection,
        OrderStorage $orderStorage,
        PickupResultInterface $pickupResult
    ): ShopList
    {
        $result = new ShopList();

        if ($pickupResult->isSuccess()) {
            $avgLatitude = 0;
            $avgLongitude = 0;
            $locationCode = $this->locationService->getCurrentLocation();
            $subregionCode = $this->locationService->findLocationSubRegion($locationCode)['CODE'] ?? '';
            $metroList = $this->getMetroInfo($storeCollection);
            $paymentInfo = $this->getPaymentInfo($orderStorage, $pickupResult);

            $shops = new ArrayCollection();

            /** @var Store $store */
            foreach ($storeCollection as $store) {
                try {
                    $item = $this->getShopData($pickupResult, $store, $metroList, $paymentInfo, false);
                    $locationType = (($store->getLocation() === $locationCode) || ($store->getSubRegion() && $store->getSubRegion() === $subregionCode))
                        ? StoreLocationType::SUBREGIONAL
                        : StoreLocationType::REGIONAL;

                    $item->setLocationType($locationType);
                    $avgLongitude += $store->getLongitude();
                    $avgLatitude += $store->getLatitude();
                    $shops->add($item);
                } catch (DeliveryNotAvailableException $e) {
                }
            }

            if ($shops->count()) {
                $result->setAvgLongitude($avgLongitude / $shops->count())
                       ->setAvgLatitude($avgLatitude / $shops->count());
            }

            $result->setOffers($this->getOfferInfo($pickupResult->getFullStockResult()))
                   ->setShops($shops);

        }

        return $result;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @todo слишком мало параметров, нужно больше
     *
     * @param PickupResultInterface $pickup
     * @param Store                 $store
     * @param array                 $metroList
     * @param array                 $paymentInfo
     * @param bool                  $recalculateBasket
     *
     * @return Shop
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotAvailableException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws NotSupportedException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     * @throws \RuntimeException
     */
    protected function getShopData(
        PickupResultInterface $pickup,
        Store $store,
        array $metroList,
        array $paymentInfo,
        bool $recalculateBasket = false
    ): Shop
    {
        $fullResult = (clone $pickup)->setSelectedShop($store);
        if (!$fullResult->isSuccess()) {
            throw new DeliveryNotAvailableException(sprintf('Pickup from shop %s is unavailable', $store->getXmlId()));
        }

        $splitStockResult = $this->orderSplitService->splitStockResult($fullResult);
        $available = $splitStockResult->getAvailable();
        $delayed = $splitStockResult->getDelayed();
        $canGetPartial = $this->orderSplitService->canGetPartial($fullResult);
        $canSplit = $this->orderSplitService->canSplitOrder($fullResult);
        $partialResult = ($canSplit || $canGetPartial) ? (clone $fullResult)->setStockResult($available) : $fullResult;
        /**
         * пересчет скидок корзины для частичного получения заказа
         */
        if ($recalculateBasket && $canGetPartial) {
            $available = $this->orderSplitService->recalculateStockResult($available);
        }

        $price = $canGetPartial ? $available->getPrice() : $fullResult->getStockResult()->getPrice();
        $address = $this->getShopAddress($store, $metroList);
        $showTime = $this->deliveryService->isInnerPickup($pickup);

        $shop = (new Shop())->setId($store->getId())
                            ->setXmlId($store->getXmlId())
                            ->setPhone($store->getPhone())
                            ->setSchedule($store->getScheduleString())
                            ->setLatitude($store->getLatitude())
                            ->setLongitude($store->getLongitude())
                            ->setName($address)
                            ->setAddress($address)
                            ->setMetroCssClass($store->getMetro() ? '--' . $metroList[$store->getMetro()]['BRANCH']['UF_CLASS'] : '')
                            ->setAvailability(
                                $this->getShopAvailabilityType($splitStockResult, $canSplit, $canGetPartial)
                            )
                            ->setPayments($this->getShopPayments($store, $price, $paymentInfo))
                            ->setAvailableItems($this->getShopItems($available))
                            ->setDelayedItems($this->getShopItems($delayed))
                            ->setPrice(WordHelper::numberFormat($price))
                            ->setFullPrice(WordHelper::numberFormat($fullResult->getStockResult()->getPrice()))
                            ->setPickupDate(
                                DeliveryTimeHelper::showTime(
                                    $available->isEmpty() ? $fullResult : $partialResult,
                                    [
                                        'SHORT'     => false,
                                        'SHOW_TIME' => $showTime,
                                    ]
                                )
                            )
                            ->setFullPickupDate(
                                DeliveryTimeHelper::showTime(
                                    $fullResult,
                                    [
                                        'SHORT'     => false,
                                        'SHOW_TIME' => $showTime,
                                    ]
                                )
                            )
                            ->setPickupDateShortFormat(
                                DeliveryTimeHelper::showTime(
                                    $available->isEmpty() ? $fullResult : $partialResult,
                                    [
                                        'SHORT'     => true,
                                        'SHOW_TIME' => $showTime,
                                    ]
                                )
                            )
                            ->setFullPickupDateShortFormat(
                                DeliveryTimeHelper::showTime(
                                    $fullResult,
                                    [
                                        'SHORT'     => true,
                                        'SHOW_TIME' => $showTime,
                                    ]
                                )
                            );

        return $shop;
    }

    /**
     * @param ShopList $shopList
     *
     * @return array
     */
    public function toArray(ShopList $shopList): array
    {
        return $this->arrayTransformer->toArray($shopList);
    }

    /**
     * @param OrderStorage          $storage
     * @param PickupResultInterface $pickupResult
     *
     * @return array
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws NotFoundException
     */
    protected function getPaymentInfo(OrderStorage $storage, PickupResultInterface $pickupResult): array
    {
        $result = [];
        $storage->setDeliveryPlaceCode('');
        $storage->setDeliveryId($pickupResult->getDeliveryId());
        $payments = $this->orderStorageService->getAvailablePayments($storage, false, false);
        /** @var array $payment */
        foreach ($payments as $payment) {
            $result[$payment['CODE']] = $payment['NAME'];
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
     * @param StockResultCollection $stockResultCollection
     *
     * @return ArrayCollection
     */
    protected function getOfferInfo(StockResultCollection $stockResultCollection): ArrayCollection
    {
        $result = new ArrayCollection();
        /** @var Offer $offer */
        foreach ($stockResultCollection->getOffers(false) as $offer) {
            $result[$offer->getId()] = (new OfferInfo())->setName($offer->getName())
                                                        ->setWeight($offer->getCatalogProduct()
                                                                          ->getWeight()
                                                        );
        }

        return $result;
    }

    /**
     * @param Store $shop
     * @param array $metroList
     *
     * @return string
     */
    protected function getShopAddress(Store $shop, array $metroList): string
    {
        $result = $shop->getAddress();
        if ($shop->getMetro()) {
            $result = 'м. ' . $metroList[$shop->getMetro()]['UF_NAME'] . ', ' . $result;
        }

        return $result;
    }

    /**
     * @param SplitStockResult $splitStockResult
     * @param bool             $canSplit
     * @param bool             $canGetPartial
     *
     * @return string
     */
    protected function getShopAvailabilityType(
        SplitStockResult $splitStockResult,
        bool $canSplit,
        bool $canGetPartial
    ): string
    {
        $result = OrderAvailability::AVAILABLE;
        if ($canGetPartial) {
            $result = OrderAvailability::PARTIAL;
        } elseif ($canSplit) {
            $result = OrderAvailability::SPLIT;
        } elseif (!$splitStockResult->getDelayed()->isEmpty()) {
            $result = OrderAvailability::DELAYED;
        }

        return $result;
    }

    /**
     * @param Store $shop
     * @param float $orderTotal
     * @param array $paymentInfo
     *
     * @return ArrayCollection
     */
    protected function getShopPayments(Store $shop, float $orderTotal, array $paymentInfo): ArrayCollection
    {
        $result = new ArrayCollection();
        $paymentCodes = $this->paymentService->getAvailablePaymentsForStore($shop, $orderTotal);

        foreach ($paymentCodes as $code) {
            if (!isset($paymentInfo[$code])) {
                continue;
            }
            $result->add(
                (new Payment())->setName($paymentInfo[$code])
                               ->setCode($code)
            );
        }

        return $result;
    }

    /**
     * @param StockResultCollection $stockResultCollection
     *
     * @return ArrayCollection
     */
    protected function getShopItems(StockResultCollection $stockResultCollection): ArrayCollection
    {
        $result = new ArrayCollection();
        /** @var StockResult $item */
        foreach ($stockResultCollection as $item) {
            $result->add(
                (new ShopOffer())->setId($item->getOffer()->getId())
                                 ->setPrice($item->getPrice())
                                 ->setQuantity($item->getAmount())
            );
        }

        return $result;
    }
}
