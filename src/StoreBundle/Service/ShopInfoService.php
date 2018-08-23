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
     * @return StoreCollection
     * @throws ArgumentException
     * @throws ApplicationCreateException
     */
    public function getShopsByRequest(Request $request): StoreCollection
    {
        return $this->storeService->getStores(
            StoreService::TYPE_SHOP,
            $this->getFilterByRequest($request),
            $this->getOrderByRequest($request)
        );
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws ApplicationCreateException
     */
    protected function getFilterByRequest(Request $request): array
    {
        $result = [];
        $storesSort = $request->get('stores-sort');
        if (\is_array($storesSort) && !empty($storesSort)) {
            $result['UF_SERVICES'] = $storesSort;
        }
        $code = $request->get('code');
        if (!empty($code)) {
            $codeList = json_decode($code, true);
            if (\is_array($codeList)) {
                $dadataLocationAdapter = new DaDataLocationAdapter();
                /** @var BitrixLocation $bitrixLocation */
                $bitrixLocation = $dadataLocationAdapter->convertFromArray($codeList);
                $result['UF_LOCATION'] = $bitrixLocation->getCode();
            } else {
                $result['UF_LOCATION'] = $code;
            }
        }

        $search = $request->get('search');
        if (!empty($search)) {
            $result[] = [
                'LOGIC'          => 'OR',
                '%ADDRESS'       => $search,
                '%METRO.UF_NAME' => $search,
            ];
        }

        return $result;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getOrderByRequest(Request $request): array
    {
        $result = [];
        $sort = $request->get('sort');
        if (!empty($sort)) {
            switch ($sort) {
                case 'city':
                    $result = ['LOCATION.NAME.NAME' => 'asc'];
                    break;
                case 'address':
                    $result = ['ADDRESS' => 'asc'];
                    break;
                case 'metro':
                    $result = ['METRO.UF_NAME' => 'asc'];
                    break;
            }
        }

        return $result;
    }

    /**
     * @param StoreCollection $stores
     * @param Offer           $offer
     *
     * @return ShopList
     * @throws ArgumentException
     * @throws SystemException
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
        $i = 0;
        foreach ($stores as $store) {
            try {
                $shop = $this->getStoreInfo($store, $servicesList, $metroList);

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
            $i++;
        }

        if ($shopCount = $shops->count()) {
            /** @var array $service */
            foreach ($servicesList as $service) {
                $services->add(
                    (new Service())
                        ->setId($service['ID'])
                        ->setXmlId($service['UF_XML_ID'])
                        ->setSort($service['UF_SORT'])
                        ->setName($service['UF_NAME'])
                        ->setLink($service['UF_LINK'])
                        ->setDescription($service['UF_DESCRIPTION'])
                        ->setFullDescription($service['UF_FULL_DESCRIPTION'])
                );
            }
        }

        $shopList = (new ShopList())
            ->setItems(new ArrayCollection($shops))
            ->setAvgLatitude($shopCount ? $avgLatitude / $shopCount : $avgLatitude)
            ->setAvgLongitude($shopCount ? $avgLongitude / $shopCount : $avgLongitude)
            ->setHideTab((bool)$shopCount)
            ->setServices($services);

        return $shopList;
    }

    /**
     * @param Store      $store
     * @param array      $metroList
     * @param array      $servicesList
     *
     * @return Shop
     * @throws EmptyAddressException
     * @throws EmptyCoordinatesException
     */
    public function getStoreInfo(
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
            ->setSchedule($store->getScheduleString());

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
     * @param ShopList $shopList
     *
     * @return array
     */
    public function shopListToArray(ShopList $shopList): array
    {
        return $this->arrayTransformer->toArray($shopList);
    }

    /**
     * @param string $sort
     * @param bool   $haveMetro
     *
     * @return string
     */
    public function getSortHtml(string $sort, bool $haveMetro = false): string
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
            }
        }

        if (false === $results[$offer->getId()]) {
            throw new PickupUnavailableException(\sprintf('Pickup is unavailable for offer #%s', $offer->getId()));
        }

        return $results[$offer->getId()];
    }
}
