<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Sale\BasketItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\Image;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\MobileApiBundle\Collection\BasketProductCollection;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\Store\Store as ApiStore;
use FourPaws\MobileApiBundle\Dto\Object\Store\StoreService as ApiStoreServiceDto;
use FourPaws\MobileApiBundle\Dto\Request\StoreListRequest;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Dto\ShopList\Shop as StoreBundleShop;
use FourPaws\SaleBundle\Dto\ShopList\Shop as SaleBundleShop;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\ShopInfoService as StoreShopInfoService;
use FourPaws\SaleBundle\Service\ShopInfoService as SaleShopInfoService;
use FourPaws\StoreBundle\Service\StoreService as AppStoreService;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;
use FourPaws\SaleBundle\Service\OrderSplitService;

class StoreService
{
    /** @var AppStoreService */
    private $appStoreService;

    /** @var ApiProductService */
    private $apiProductService;

    /** @var StoreShopInfoService */
    protected $storeShopInfoService;

    /** @var SaleShopInfoService */
    protected $saleShopInfoService;

    /** @var BasketService */
    private $basketService;

    /** @var OrderStorageService */
    private $orderStorageService;

    /** @var OrderSplitService */
    private $orderSplitService;

    /** @var DeliveryService */
    private $deliveryService;

    public function __construct(
        AppStoreService $appStoreService,
        ApiProductService $apiProductService,
        StoreShopInfoService $storeShopInfoService,
        SaleShopInfoService $saleShopInfoService,
        BasketService $basketService,
        OrderStorageService $orderStorageService,
        OrderSplitService $orderSplitService,
        DeliveryService $deliveryService
    )
    {
        $this->appStoreService = $appStoreService;
        $this->apiProductService = $apiProductService;
        $this->storeShopInfoService = $storeShopInfoService;
        $this->saleShopInfoService = $saleShopInfoService;
        $this->basketService = $basketService;
        $this->orderStorageService = $orderStorageService;
        $this->orderSplitService = $orderSplitService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * @param StoreListRequest $storeListRequest
     *
     * @throws \Exception
     * @throws \Bitrix\Main\ArgumentException
     * @return ApiStore[]|\Doctrine\Common\Collections\Collection
     */
    public function getList(StoreListRequest $storeListRequest): Collection
    {
        $appStoreCollection = $this->appStoreService->getStores(
            $this->appStoreService::TYPE_SHOP,
            ...$this->getParams($storeListRequest)
        );
        if (0 === $appStoreCollection->count()) {
            // если магазинов в городе / метро не найдено - запрашиваем все магазины подряд
            $appStoreCollection = $this->appStoreService->getStores(
                $this->appStoreService::TYPE_SHOP,
                [],
                $this->getOrder($storeListRequest)
            );
        }
        [$services, $metro] = $this->appStoreService->getFullStoreInfo($appStoreCollection);
        return $appStoreCollection->map(function (Store $store) use ($services, $metro) {
            return $this->storeToApiFormat($store, $services, $metro);
        });
    }

    /**
     * @param int $offerId
     * @return Collection
     * @throws NotFoundException
     * @throws \Exception
     */
    public function getListWithProductAvailability(int $offerId): Collection
    {
        $storeCollection = new StoreCollection();
        if (!$offer = OfferQuery::getById($offerId)) {
            throw new NotFoundException("Offer with ID $offerId is not found");
        }
        try {
            $rawStoreCollection = $this->storeShopInfoService->getShopsByOffer($offer);
            [$servicesList, $metroList] = $this->appStoreService->getFullStoreInfo($rawStoreCollection);
        } catch (\Exception $exception) {
            $rawStoreCollection = [];
            $servicesList = [];
            $metroList = [];
        }
        /** @var Store $store */
        foreach ($rawStoreCollection as $store) {
            try {
                $stockAmount = $this->storeShopInfoService->getStockAmount($store, $offer);
            }  catch (\Exception $e) {
                $stockAmount = 0;
            }

            try {
                $shop = $this->storeShopInfoService->getStoreInfo(
                    $store,
                    $metroList,
                    $servicesList,
                    $offer
                );
                $storeCollection->add($this->storeBundleShopToApiFormat($shop, $stockAmount, $servicesList));
            } catch (\Exception $exception) {
            }
        }

        return $storeCollection;
    }

    /**
     * @param array $metroStationIds
     * @return Collection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Sale\UserMessageException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\SaleBundle\Exception\OrderStorageSaveException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getListWithProductsInBasketAvailability(array $metroStationIds = []): Collection
    {
        $this->checkBasketEmptiness();
        $storage = $this->orderStorageService->getStorage();
        $shopInfo = $this->saleShopInfoService->getShopInfo($storage, $this->orderStorageService->getPickupDelivery($storage));
        $shops = $shopInfo->getShops();
        if (!empty($metroStationIds)) {
            $shops = $shops->filter(function(SaleBundleShop $shop) use ($metroStationIds) {
                return in_array($shop->getMetroId(), $metroStationIds);
            });
        }
        return $shops->map(function (SaleBundleShop $shop) {
            return $this->saleBundleShopToApiFormat($shop);
        });
    }

    protected function getParams(StoreListRequest $storeListRequest)
    {
        return [
            $this->getFilter($storeListRequest),
            $this->getOrder($storeListRequest),
        ];
    }

    protected function getFilter(StoreListRequest $storeListRequest)
    {
        $result = [];
        if (!empty($storeListRequest->getMetroStation())) {
            $result['UF_METRO'] = $storeListRequest->getMetroStation();
        }
        if (!empty($storeListRequest->getCityId())) {
            $result['UF_LOCATION'] = $storeListRequest->getCityId();
        }

        return $result;
    }

    protected function getOrder(StoreListRequest $storeListRequest)
    {
        $result = [];
        //Сортировка по приближенности к текущему местоположению
        $longitude = $storeListRequest->getLongitude();
        $latitude = $storeListRequest->getLatitude();
        if ($longitude > 0 && $latitude > 0) {
            $result['DISTANCE_' . (string)$latitude . '_' . (string)$longitude] = 'ASC';
        }

        return $result;
    }

    /**
     * Форматирует магазин для списка на главной
     * @param Store $store
     * @param array $servicesList
     * @param array $metroList
     * @return ApiStore
     */
    protected function storeToApiFormat(Store $store, array $servicesList = [], array $metroList = []): ApiStore
    {
        $metroId = $store->getMetro();
        $metroName = $metroId > 0 && $metroList[$metroId] ? $metroList[$metroId]['UF_NAME'] : '';
        $metroAddressText = $metroId > 0 && $metroList[$metroId] ? 'м.' . $metroName . ', ' : '';
        $metroColor = $metroId > 0 && $metroList[$metroId] ? '#' . $metroList[$metroId]['BRANCH']['UF_COLOUR_CODE'] : '';
        return (new ApiStore())
            ->setAddress($metroAddressText . $store->getAddress())
            ->setCode($store->getXmlId())
            ->setTitle($this->formatStoreTitle($store->getXmlId(), $store->getTitle()))
            ->setDetails($store->getDescription())
            ->setLatitude($store->getLatitude())
            ->setLongitude($store->getLongitude())
            ->setMetroColor($metroColor)
            ->setMetroName($metroName)
            ->setPhone($store->getPhone())
            ->setPicture($store->getSrcImage())
            ->setService($this->prepareServicesList($servicesList))
            ->setWorkTime($store->getScheduleString())
            ;
    }

    /**
     * Форматирует магазин для списка в карточке товара
     * @param StoreBundleShop $shop
     * @param int $stockAmount
     * @param array $servicesList
     * @return ApiStore
     */
    protected function storeBundleShopToApiFormat(StoreBundleShop $shop, int $stockAmount = 0, array $servicesList)
    {
        return (new ApiStore())
            ->setCode($shop->getXmlId())
            ->setTitle($shop->getAddress())
            ->setPicture($shop->getPhotoUrl())
            ->setDetails($shop->getDescription())
            ->setAddress($shop->getAddress())
            ->setPhone($shop->getPhone())
            ->setLatitude($shop->getLatitude())
            ->setLongitude($shop->getLongitude())
            ->setWorkTime($shop->getSchedule())
            ->setIsByRequest($stockAmount === 0)
            ->setMetroName($shop->getMetro())
            ->setMetroColor($this->formatMetroColor($shop->getMetroColor()))
            ->setService($this->prepareServicesList($servicesList))
            ->setPickupDate($shop->getPickupDate())
            ->setPickupFewGoodsFullDate($shop->getPickupDate())
            ->setProductQuantityString($shop->getAvailableAmount())
            ;
    }

    /**
     * Форматирует магазин для списка в чекауте
     * @param SaleBundleShop $shop
     * @return ApiStore
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function saleBundleShopToApiFormat(SaleBundleShop $shop)
    {
        $apiStore = (new ApiStore())
            ->setCode($shop->getXmlId())
            ->setTitle($shop->getName())
            ->setAddress($shop->getAddress())
            ->setPhone($shop->getPhone())
            ->setLatitude($shop->getLatitude())
            ->setLongitude($shop->getLongitude())
            ->setWorkTime($shop->getSchedule())
            ->setMetroName($shop->getMetroName())
            ->setMetroColor($this->formatMetroColor($shop->getMetroColor()))
            ->setPickupDate($shop->getPickupDate())
            ->setPickupAllGoodsShortDate($shop->getFullPickupDateShortFormat())
            ->setPickupAllGoodsFullDate($shop->getFullPickupDate())
            ->setPickupFewGoodsShortDate($shop->getPickupDateShortFormat())
            ->setPickupFewGoodsFullDate($shop->getPickupDate())
            ->setAvailability($shop->getAvailability())
            ->setAvailableGoods($this->convertToBasketProductCollection($shop->getAvailableItems()))
            ->setDelayedGoods($this->convertToBasketProductCollection($shop->getDelayedItems()));
        return $apiStore;
    }

    /**
     * Если корзина пустая - кидает RuntimeException
     */
    protected function checkBasketEmptiness()
    {
        if (empty($this->basketService->getBasketProducts())) {
            throw new RuntimeException('Корзина пуста');
        }
    }

    /**
     * Выпиливает код магазина из названия магазина
     * @param string $xmlId
     * @param string $title
     * @return string
     */
    protected function formatStoreTitle(string $xmlId, string $title): string
    {
        return str_replace($xmlId . ' ', '', $title);
    }

    /**
     * Добавляет решетку к hex цвету ветки метро
     * @param string $metroColor
     * @return string
     */
    protected function formatMetroColor(string $metroColor)
    {
        if (!$metroColor) {
            return $metroColor;
        }
        $metroColor = ltrim($metroColor,'#');
        return '#' . $metroColor;
    }

    /**
     * @param ArrayCollection<FourPaws\SaleBundle\Dto\ShopList\Offer> $shopListOffers
     * @return BasketProductCollection
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function convertToBasketProductCollection(ArrayCollection $shopListOffers)
    {
        $basketProductCollection = new BasketProductCollection();
        foreach ($this->basketService->getBasket()->getOrderableItems() as $basketItem) {
            /** @var BasketItem $basketItem */

            foreach ($shopListOffers as $shopListOffer) {
                /** @var \FourPaws\SaleBundle\Dto\ShopList\Offer $shopListOffer */
                if ((int) $shopListOffer->getId() !== (int) $basketItem->getProductId()) {
                    continue;
                }
                $offer = OfferQuery::getById($shopListOffer->getId());
                $product = $offer->getProduct();
                $quantity = $shopListOffer->getQuantity();
                $shortProduct = $this->apiProductService->convertToShortProduct($product, $offer, $quantity, true);
                $basketProductCollection->add(
                    (new Product())
                        ->setBasketItemId($basketItem->getId())
                        ->setShortProduct($shortProduct)
                        ->setQuantity($quantity)
                );
            }
        }
        return $basketProductCollection;
    }

    /**
     * @param $servicesList
     * @return array
     */
    protected function prepareServicesList($servicesList)
    {
        $result = [];
        foreach ($servicesList as $serviceItem) {
            $service = new ApiStoreServiceDto();
            $service->setTitle($serviceItem['UF_NAME']);

            $image = '';
            if ($serviceItem['UF_FILE'] > 0) {
                try {
                    $image = Image::createFromPrimary($serviceItem['UF_FILE'])->getSrc();
                } catch (FileNotFoundException $e) {
                }
            }
            $service->setImage($image);

            $result[] = $service;
        }
        return $result;
    }
}
