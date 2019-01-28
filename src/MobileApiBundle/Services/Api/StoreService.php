<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Basket;
use Doctrine\Common\Collections\Collection;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\Image;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\LocationBundle\LocationService;
use FourPaws\MobileApiBundle\Collection\BasketProductCollection;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\ProductQuantity;
use FourPaws\MobileApiBundle\Dto\Object\Store\Store as ApiStore;
use FourPaws\MobileApiBundle\Dto\Object\Store\StoreService as ApiStoreServiceDto;
use FourPaws\MobileApiBundle\Dto\Request\StoreListRequest;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Repository\StoreRepository;
use FourPaws\StoreBundle\Service\ShopInfoService;
use FourPaws\StoreBundle\Service\StoreService as AppStoreService;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;

class StoreService
{
    /** @var AppStoreService */
    private $appStoreService;

    /** @var ApiProductService */
    private $apiProductService;

    /** @var StoreRepository */
    private $storeRepository;

    /** @var LocationService */
    private $locationService;

    /** @var ShopInfoService */
    protected $shopInfoService;

    /** @var BasketService */
    private $basketService;

    public function __construct(
        AppStoreService $appStoreService,
        ApiProductService $apiProductService,
        StoreRepository $storeRepository,
        LocationService $locationService,
        ShopInfoService $shopInfoService,
        BasketService $basketService
    )
    {
        $this->appStoreService = $appStoreService;
        $this->apiProductService = $apiProductService;
        $this->storeRepository = $storeRepository;
        $this->locationService = $locationService;
        $this->shopInfoService = $shopInfoService;
        $this->basketService = $basketService;
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
            $cloneRequest = clone $storeListRequest;
            $cloneRequest->setMetroStation([]);
            /**
             * @todo nearest city
             */
            $appStoreCollection = $this->appStoreService->getStores(
                $this->appStoreService::TYPE_SHOP,
                ...$this->getParams($cloneRequest)
            );
        }
        $storeInfo = $this->appStoreService->getFullStoreInfo($appStoreCollection);
        return $appStoreCollection->map(function (Store $store) use ($storeInfo) {
            return $this->toApiFormat($store, ...$storeInfo);
        });
    }

    /**
     * @param string $storeCode
     * @return ApiStore
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getOne($storeCode)
    {
        $store = $this->appStoreService->getStoreByXmlId($storeCode);
        return $this->toApiFormat($store);
    }

    /**
     * @param ProductQuantity[] $productsQuantity
     * @return Collection
     * @throws \Exception
     */
    public function getListAvailable(array $productsQuantity): Collection
    {
        $storeCollection = new StoreCollection();
        foreach ($productsQuantity as $productQuantity) {
            $offerId = (int) $productQuantity->getProductId();
            $quantity = (int) $productQuantity->getQuantity();
            if (!$offerId || !$quantity) {
                continue;
            }
            if ($offer = OfferQuery::getById($offerId)) {
                try {
                    $storeCollection = $this->shopInfoService->getShopsByOffer($offer);
                    $storeCollection = $storeCollection->filter(function (Store $store) use ($offer, $quantity) {
                        try {
                            $stockAmount = $this->shopInfoService->getStockAmount($store, $offer);
                        }  catch (\Exception $e) {
                            $stockAmount = 0;
                        }
                        return $stockAmount >= $quantity;
                    });

                    /*
                    if ($locationCode) {
                        $storeCollection = $storeCollection->filter(function (Store $store) use ($locationCode) {
                            return $store->getLocation() === $locationCode;
                        });
                    }
                    */

                    $storeInfo = $this->appStoreService->getFullStoreInfo($storeCollection);

                    $storeCollection = $storeCollection->map(function (Store $store) use ($storeInfo) {
                        $store = $this->toApiFormat($store, ...$storeInfo);
                        $store->setAvailabilityStatus('available');
                        return $store;
                    });

                } catch (\Exception $e) {
                }
            }
        }
        return $storeCollection;
    }

    /**
     * @param ProductQuantity[] $productsQuantity
     * @param string $locationCode
     * @return Collection
     * @throws \Exception
     */
    public function getBasketListAvailable(array $productsQuantity, string $locationCode)
    {
        $offersQuantity = [];
        foreach ($productsQuantity as $productQuantity) {
            $basketItemId = (int) $productQuantity->getProductId();
            $offerId = $this->basketService->getProductIdByBasketItemId($basketItemId);
            $offersQuantity[] = (new ProductQuantity())
                ->setProductId($offerId)
                ->setQuantity($productQuantity->getQuantity());
        }
        return $this->getListAvailable($offersQuantity);
    }

    /**
     * @param $lat
     * @param $lon
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws NotFoundException
     */
    public function getClosestStoreLocation($lat, $lon):array
    {
        /**
         * @var Store $store
         */
        $orderBy = [
            'DISTANCE_' . $lat . '_' . $lon => 'ASC'
        ];
        $store = $this->storeRepository->findBy([], $orderBy,1)->first();

        if (!$store) {
            throw new NotFoundException('не найден ближайший магазин по заданным координатам');
        }

        $location = $this->locationService->findLocationByCode($store->getLocation());

        if (!$location) {
            throw new NotFoundException('не найдена локация ближайшего магазина');
        }

        return $location;
    }

    /**
     * @param ProductQuantity[] $productQuantities
     * @return BasketProductCollection
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getStoreProductAvailable(array $productQuantities)
    {
        $basketProductCollection = new BasketProductCollection();
        foreach ($productQuantities as $productQuantity) {
            $basketItemId = $productQuantity->getProductId();
            $offerId = $this->basketService->getProductIdByBasketItemId($basketItemId);
            if ($offer = OfferQuery::getById($offerId)) {
                $product = $offer->getProduct();
                $shortProduct = $this->apiProductService->convertToShortProduct($product, $offer);
                $basketProductCollection->add(
                    (new Product())
                        ->setBasketItemId($productQuantity->getProductId())
                        ->setQuantity($productQuantity->getQuantity())
                        ->setShortProduct($shortProduct)
                );
            }
        }
        return $basketProductCollection;
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

    protected function toApiFormat(Store $store, array $servicesList = [], array $metroList = []): ApiStore
    {
        $result = new ApiStore();

        $title = str_replace($store->getXmlId() . ' ', '', $store->getTitle());

        $metroId = $store->getMetro();
        $metroName = $metroId > 0 && $metroList[$metroId] ? $metroList[$metroId]['UF_NAME'] : '';
        $metroAddressText = $metroId > 0 && $metroList[$metroId] ? 'м.' . $metroName . ', ' : '';
        $metroColor = $metroId > 0 && $metroList[$metroId] ? '#' . $metroList[$metroId]['BRANCH']['UF_COLOUR_CODE'] : '';

        $services = [];
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

            $services[] = $service;
        }

        $result->setAddress($metroAddressText . $store->getAddress());
        /** @todo для запроса "shops_list_available"
         * рассчет
         * if ($this->pickupDelivery) {
         * $stockResult = $this->getStockResult($this->pickupDelivery);
         * $storeAmount = reset($this->offers)->getStocks()
         * ->filterByStores(
         * $this->storeService->getByCurrentLocation(
         * StoreService::TYPE_STORE
         * )
         * )->getTotalAmount();
         * }
         */

        $result->setCode($store->getXmlId());
        $result->setTitle($title);
        $result->setAvailabilityStatus('');
        $result->setCityId($store->getLocation());
        $result->setDetails($store->getDescription());
        $result->setLatitude($store->getLatitude());
        $result->setLongitude($store->getLongitude());
        $result->setMetroColor($metroColor);
        $result->setMetroName($metroName);
        $result->setPhone($store->getPhone());
        /** @todo добавочного номера нет */
        $result->setPhoneExt('');
        $result->setPicture($store->getSrcImage());
        $result->setService($services);
        /** @todo нет детального магазина - поставлен url на список */
        $uri = new Uri('http://' . SITE_SERVER_NAME . '/shops/');
        $uri->addParams(['city' => $store->getLocation(), 'id' => $store->getId()]);
        $result->setUrl($uri->getUri());
        $result->setWorkTime($store->getScheduleString());
        return $result;
    }
}
