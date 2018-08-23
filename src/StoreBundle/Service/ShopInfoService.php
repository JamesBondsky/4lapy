<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\UserMessageException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Adapter\DaDataLocationAdapter;
use FourPaws\Adapter\Model\Output\BitrixLocation;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\WordHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Dto\ShopList\Service;
use FourPaws\StoreBundle\Dto\ShopList\Shop;
use FourPaws\StoreBundle\Dto\ShopList\ShopList;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Entity\StoreSearchResult;
use FourPaws\StoreBundle\Exception\EmptyAddressException;
use FourPaws\StoreBundle\Exception\EmptyCoordinatesException;
use FourPaws\StoreBundle\Exception\NoStoresAvailableException;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Exception\PickupUnavailableException;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\HttpFoundation\Request;

class ShopInfoService
{
    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * ShopInfoService constructor.
     * @param StoreService              $storeService
     * @param DeliveryService           $deliveryService
     * @param LocationService           $locationService
     * @param ArrayTransformerInterface $arrayTransformer
     */
    public function __construct(
        StoreService $storeService,
        DeliveryService $deliveryService,
        LocationService $locationService,
        ArrayTransformerInterface $arrayTransformer
    )
    {
        $this->storeService = $storeService;
        $this->deliveryService = $deliveryService;
        $this->locationService = $locationService;
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @param Offer $offer
     *
     * @return StoreCollection
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NoStoresAvailableException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws PickupUnavailableException
     * @throws UserMessageException
     */
    public function getShopsByOffer(Offer $offer): StoreCollection
    {
        if ($offer->isAvailable() &&
            ($this->deliveryService->getCurrentDeliveryZone() !== DeliveryService::ZONE_4) &&
            ($pickupResult = $this->getPickupResult($offer))
        ) {
            $result = $pickupResult->getBestShops();
        } else {
            throw new NoStoresAvailableException(sprintf('No available stores for offer #%s', $offer->getId()));
        }

        if ($result->isEmpty()) {
            throw new NoStoresAvailableException(sprintf('No available stores for offer #%s', $offer->getId()));
        }

        return $result;
    }

    /**
     * @param Request $request
     *
     * @return ShopList
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getShopListByRequest(Request $request): ShopList
    {
        $storeSearchResult = $this->getStoresByRequest($request);
        [, $metroList] = $this->storeService->getFullStoreInfo($storeSearchResult->getStores());

        $stores = $this->sortByRequest(
            $this->filterByRequest($storeSearchResult->getStores(), $request, $metroList),
            $request
        );

        $shopList = $this->getShopList($stores);

        $haveMetro = false;
        $activeStoreId = $request->get('active_store_id', 0);
        /** @var Shop $item */
        foreach ($shopList->getItems() as $i => $item) {
            if ($item->getMetro()) {
                $haveMetro = true;
            }

            if (($activeStoreId === 'first' && $i === 0) ||
                ($item->getId() === $activeStoreId)
            ) {
                $item->setActive(true);
            }
        }

        $shopList->setSortHtml(
            $this->getSortHtml(
                $request->get('sort', ''),
                $haveMetro
            )
        );

        $shopList
            ->setLocationName($storeSearchResult->getLocationName())
            ->setHideTab($shopList->getItems()->isEmpty());

        return $shopList;
    }

    /**
     * @param StoreCollection $stores
     * @param Offer|null      $offer
     *
     * @return ShopList
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     * @throws \Exception
     */
    public function getShopList(StoreCollection $stores, Offer $offer = null): ShopList
    {
        [
            $servicesList,
            $metroList,
        ] = $this->storeService->getFullStoreInfo($stores);

        $avgLatitude = 0;
        $avgLongitude = 0;
        $shops = new ArrayCollection();
        $services = new ArrayCollection();

        /** @var Store $store */
        foreach ($stores as $store) {
            try {
                $shop = $this->getStoreInfo($store, $metroList, $servicesList);

                if ($offer) {
                    $pickupResult = $this->getPickupResultByStore($store, $offer);

                    /** @var StockResult $stockResultByStore */
                    $stockResultByStore = $pickupResult->getStockResult()->first();
                    $amount = $stockResultByStore->getOffer()
                        ->getStocks()
                        ->filterByStore($store)
                        ->getTotalAmount();

                    $amountString = 'под заказ';
                    if ($amount) {
                        $amountString = $amount > 5 ? 'много' : 'мало';
                    }

                    $shop
                        ->setPickupDate(
                            DeliveryTimeHelper::showTime(
                                $pickupResult,
                                [
                                    'SHOW_TIME' => true,
                                    'SHORT'     => true,
                                ]
                            )
                        )
                        ->setAvailableAmount(str_replace(' ', '&nbsp;', $amountString));
                }
            } catch (PickupUnavailableException|EmptyCoordinatesException|EmptyAddressException $e) {
                continue;
            }

            $avgLatitude += $store->getLatitude();
            $avgLongitude += $store->getLongitude();

            $shops->add($shop);
        }

        if ($shopCount = $shops->count()) {
            /** @var array $service */
            foreach ($servicesList as $service) {
                $services->add(
                    (new Service())
                        ->setId($service['ID'])
                        ->setXmlId($service['UF_XML_ID'])
                        ->setSort((int)$service['UF_SORT'])
                        ->setName((string)$service['UF_NAME'])
                        ->setLink((string)$service['UF_LINK'])
                        ->setDescription((string)$service['UF_DESCRIPTION'])
                        ->setFullDescription((string)$service['UF_FULL_DESCRIPTION'])
                );
            }
        }

        $shopList = (new ShopList())
            ->setItems($shops)
            ->setAvgLatitude($shopCount ? $avgLatitude / $shopCount : $avgLatitude)
            ->setAvgLongitude($shopCount ? $avgLongitude / $shopCount : $avgLongitude)
            ->setHideTab((bool)$shopCount)
            ->setServices($services);

        return $shopList;
    }

    /**
     * @param ShopList $shopList
     *
     * @return array
     */
    public function shopListToArray(ShopList $shopList): array
    {
        return $this->arrayTransformer->toArray($shopList);
    }

    /**
     * @param Request $request
     *
     * @return StoreSearchResult
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws SystemException
     */
    protected function getStoresByRequest(Request $request): StoreSearchResult
    {
        $locationCode = $request->get('code', '');
        if (!empty($locationCode)) {
            $codeList = json_decode($locationCode, true);
            if (\is_array($codeList)) {
                $dadataLocationAdapter = new DaDataLocationAdapter();
                /** @var BitrixLocation $bitrixLocation */
                $bitrixLocation = $dadataLocationAdapter->convertFromArray($codeList);
                $locationCode = $bitrixLocation->getCode();
            }
        }

        if ($locationCode) {
            $storeSearchResult = $this->storeService->getStoresByLocation($locationCode);
        }

        /**
         * если не задано местоположение или не нашлось ни одного магазина в городе/районе/регионе
         * возвращаем все магазины
         */
        if (!$locationCode || $storeSearchResult->getStores()->isEmpty()) {
            $storeSearchResult = $this->storeService->getAllStores(StoreService::TYPE_SHOP);
        }

        return $storeSearchResult;
    }

    /**
     * @param StoreCollection $stores
     * @param Request         $request
     * @param array           $metroList
     *
     * @return StoreCollection
     */
    protected function filterByRequest(StoreCollection $stores, Request $request, array $metroList = []): StoreCollection
    {
        $services = (array)$request->get('stores-sort');
        $name = $request->get('search', '');
        $result = $stores->filter(function (Store $store) use ($services, $name, $metroList) {
            if ($services && empty(\array_intersect($services, $store->getServices()))) {
                return false;
            }

            if ($name) {
                $result = false;
                $result |= mb_stripos($store->getAddress(), $name) !== false;
                if ($metroList && $store->getMetro()) {
                    $result |= mb_stripos($metroList[$store->getMetro()]['UF_NAME'], $name) !== false;
                }
                return $result;
            }

            return true;
        });

        return $result;
    }

    /**
     * @param StoreCollection $stores
     * @param Request         $request
     * @param array           $metroList
     *
     * @return StoreCollection
     */
    protected function sortByRequest(StoreCollection $stores, Request $request, array $metroList = []): StoreCollection
    {
        if ($sortField = $request->get('sort', '')) {
            $iterator = $stores->getIterator();
            $iterator->uasort(function (Store $store1, Store $store2) use ($sortField, $metroList) {
                $result = 0;
                switch ($sortField) {
                    case 'address':
                        $result = $store1->getAddress() <=> $store2->getAddress();
                        break;
                    case 'metro':
                        $result = $metroList[$store1->getMetro()] <=> $store2->getMetro();
                        break;
                }

                return $result;
            });
            $stores = new StoreCollection(iterator_to_array($iterator));
        }

        return $stores;
    }

    /**
     * @param Store $store
     * @param array $metroList
     * @param array $servicesList
     *
     * @return Shop
     * @throws EmptyAddressException
     * @throws EmptyCoordinatesException
     */
    protected function getStoreInfo(
        Store $store,
        array $metroList,
        array $servicesList
    ): Shop
    {
        if (!$store->getAddress()) {
            throw new EmptyAddressException(\sprintf('Store #%s address is empty', $store->getId()));
        }

        /**
         * Будем надеяться, что магазины на экваторе в ближайшее время не откроются
         */
        if (!$store->getLatitude() || !$store->getLongitude()) {
            throw new EmptyCoordinatesException(\sprintf('Store #%s coordinates are not defined', $store->getId()));
        }

        $shop = (new Shop())
            ->setId($store->getId())
            ->setXmlId($store->getXmlId())
            ->setAddress($store->getAddress())
            ->setDescription(WordHelper::clear($store->getDescription()))
            ->setPhone($store->getPhone())
            ->setSchedule($store->getScheduleString())
            ->setLatitude($store->getLatitude())
            ->setLongitude($store->getLongitude());

        if ($metroId = $store->getMetro()) {
            $shop
                ->setMetro('м. ' . $metroList[$metroId]['UF_NAME'])
                ->setMetroCssClass('--' . $metroList[$metroId]['BRANCH']['UF_CLASS']);
        }

        try {
            if ($store->getImageId() > 0) {
                $shop->setPhotoUrl(
                    CropImageDecorator::createFromPrimary($store->getImageId())
                        ->setCropWidth(630)
                        ->setCropHeight(360)
                        ->getSrc()
                );

            }
        } catch (FileNotFoundException $e) {
        }

        $services = [];
        foreach ($servicesList as $service) {
            if (\in_array((int)$service['ID'], $store->getServices(), true)) {
                $services[] = $service['UF_NAME'];
            }
        }
        $shop->setServices($services);

        return $shop;
    }

    /**
     * @param string $sort
     * @param bool   $haveMetro
     *
     * @return string
     */
    protected function getSortHtml(string $sort, bool $haveMetro = false): string
    {
        $result = '<option value="" disabled="disabled">выберите</option>';
        $result .= '<option value="address" ' . ($sort === 'address' ? ' selected="selected" ' : '')
            . '>по адресу</option>';

        if ($haveMetro) {
            $result .= '<option value="metro" ' . ($sort === 'metro' ? ' selected="selected" ' : '')
                . '>по метро</option>';
        }

        return $result;
    }

    /**
     * @param Store $store
     * @param Offer $offer
     *
     * @return PickupResultInterface
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws PickupUnavailableException
     * @throws UserMessageException
     */
    protected function getPickupResultByStore(Store $store, Offer $offer): PickupResultInterface
    {
        $pickupResult = clone $this->getPickupResult($offer);
        $pickupResult->setSelectedShop($store);

        if (!$pickupResult->isSuccess()) {
            throw new PickupUnavailableException(
                \sprintf(
                    'Pickup is unavailable for offer #%s and store #%s',
                    $offer->getId(),
                    $store->getId()
                )
            );
        }

        return $pickupResult;
    }

    /**
     * @param Offer $offer
     *
     * @return PickupResultInterface
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     * @throws DeliveryNotFoundException
     * @throws PickupUnavailableException
     */
    protected function getPickupResult(Offer $offer): PickupResultInterface
    {
        static $results;
        if (null === $results) {
            $results = [];
        }

        if (null === $results[$offer->getId()]) {
            $availableDeliveries = $this->deliveryService->getByProduct($offer);
            $pickup = null;
            foreach ($availableDeliveries as $availableDelivery) {
                if ($this->deliveryService->isInnerPickup($availableDelivery)) {
                    $pickup = $availableDelivery;
                    break;
                }
            }

            if (null === $pickup) {
                $results[$offer->getId()] = false;
            } else {
                $results[$offer->getId()] = $pickup;
            }
        }

        if (false === $results[$offer->getId()]) {
            throw new PickupUnavailableException(\sprintf('Pickup is unavailable for offer #%s', $offer->getId()));
        }

        return $results[$offer->getId()];
    }
}
