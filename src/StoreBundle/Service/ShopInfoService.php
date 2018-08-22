<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\UserMessageException;
use Doctrine\Common\Collections\ArrayCollection;
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
     * ShopInfoService constructor.
     * @param StoreService    $storeService
     * @param DeliveryService $deliveryService
     * @param LocationService $locationService
     */
    public function __construct(
        StoreService $storeService,
        DeliveryService $deliveryService,
        LocationService $locationService
    )
    {
        $this->storeService = $storeService;
        $this->deliveryService = $deliveryService;
        $this->locationService = $locationService;
    }

    /**
     * @param Offer $offer
     * @param array $params
     * @return ShopList
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NoStoresAvailableException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     * @throws \Exception
     */
    public function getShopListByOffer(Offer $offer, array $params): ShopList
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

        return $this->getShopList($result, $params);
    }

    /**
     * @param StoreCollection $stores
     * @param                 $params
     * @param Offer           $offer
     * @return ShopList
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     */
    public function getShopList(StoreCollection $stores, $params, Offer $offer = null): ShopList
    {
        [
            $servicesList,
            $metroList,
        ] = $this->storeService->getFullStoreInfo($stores);

        $avgLatitude = 0;
        $avgLongitude = 0;
        $haveMetro = false;
        $shops = new ArrayCollection();
        /** @var Store $store */
        $i = 0;
        foreach ($stores as $store) {
            try {
                $shop = $this->getStoreInfo($store, $servicesList, $metroList, $offer);

                $activeStoreId = $params['activeStoreId'];
                if (($activeStoreId === 'first' && $i === 0) ||
                    ($store->getId() === $activeStoreId)
                ) {
                    $shop->setActive(true);
                }
            } catch (PickupUnavailableException|EmptyCoordinatesException|EmptyAddressException $e) {
                continue;
            }

            if ($store->getMetro()) {
                $haveMetro = true;
            }

            $avgLatitude += $store->getLatitude();
            $avgLongitude += $store->getLongitude();

            $shops->add($shop);
            $i++;
        }
        $shopCount = $shops->count();

        /* @todo */
        $locationName = 'Все города';
        if (!empty($params['region_id']) || !empty($params['city_code'])) {
            $result['location_name'] = '';//если пустое что-то пошло не так
            $loc = null;
            if (!empty($params['region_id'])) {
                $loc = LocationTable::query()->setFilter(['ID' => $params['region_id']])->setCacheTtl(360000)->setSelect(['LOC_NAME' => 'NAME.NAME'])->exec()->fetch();
            } elseif (!empty($params['city_code'])) {
                $loc = LocationTable::query()->setFilter(['=CODE' => $params['city_code']])->setCacheTtl(360000)->setSelect(['LOC_NAME' => 'NAME.NAME'])->exec()->fetch();
            }
            if ($loc !== null && empty($result['location_name'])) {
                $locationName = $loc['LOC_NAME'];
            }
        }

        /** @var array $service */
        $services = new ArrayCollection();
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

        $shopList = (new ShopList())
            ->setItems(new ArrayCollection($shops))
            ->setSortHtml($this->getSortHtml($params, $haveMetro))
            ->setLocationName($locationName)
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
     * @param Offer|null $offer
     *
     * @return Shop
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws EmptyAddressException
     * @throws EmptyCoordinatesException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws PickupUnavailableException
     * @throws UserMessageException
     */
    public function getStoreInfo(
        Store $store,
        array $metroList,
        array $servicesList,
        Offer $offer = null
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
            ->setXmlId($store->getXmlId())
            ->setAddress($store->getAddress())
            ->setDescription(WordHelper::clear($store->getDescription()))
            ->setPhone($store->getPhone())
            ->setSchedule($store->getScheduleString());

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
     * @param array $params
     * @param bool  $haveMetro
     *
     * @return string
     */
    protected function getSortHtml(array $params, bool $haveMetro = false): string
    {
        $result = '<option value="" disabled="disabled">выберите</option>';
        $result .= '<option value="address" ' . ($params['sortVal']
            === 'address' ? ' selected="selected" ' : '')
            . '>по адресу</option>';

        if ($haveMetro && $params['returnSort']) {
            $result .= '<option value="metro" ' . ($params['sortVal']
                === 'metro' ? ' selected="selected" ' : '')
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
            throw new PickupUnavailableException(\sprintf('Pickup is unavailable for offer #%s', $offer->getId());
        }

        return $results[$offer->getId()];
    }
}
