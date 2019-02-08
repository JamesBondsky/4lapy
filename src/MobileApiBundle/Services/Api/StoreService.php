<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\Web\Uri;
use Doctrine\Common\Collections\Collection;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\Image;
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
use FourPaws\StoreBundle\Dto\ShopList\Shop;
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
            // если магазинов в городе / метро не найдено - запрашиваем все магазины подряд
            $appStoreCollection = $this->appStoreService->getStores(
                $this->appStoreService::TYPE_SHOP,
                [],
                $this->getOrder($storeListRequest)
            );
        }
        [$services, $metro] = $this->appStoreService->getFullStoreInfo($appStoreCollection);
        return $appStoreCollection->map(function (Store $store) use ($services, $metro) {
            return $this->toApiFormat($store, $services, $metro);
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
                    $rawStoreCollection = $this->shopInfoService->getShopsByOffer($offer);
                    [$servicesList, $metroList] = $this->appStoreService->getFullStoreInfo($rawStoreCollection);
                    /** @var Store $store */
                    foreach ($rawStoreCollection as $store) {
                        try {
                            $stockAmount = $this->shopInfoService->getStockAmount($store, $offer);
                        }  catch (\Exception $e) {
                            $stockAmount = 0;
                        }

                        $shop = $this->shopInfoService->getStoreInfo(
                            $store,
                            $metroList,
                            $servicesList,
                            $offer
                        );

                        if ($stockAmount >= $quantity) {
                            $storeCollection->add($this->toApiFormat($store, $servicesList, $metroList, $stockAmount, $shop));
                        }
                    }

                } catch (\Exception $e) {
                }
            }
        }
        return $storeCollection;
    }

    /**
     * @param ProductQuantity[] $basketQuantity
     * @return ProductQuantity[]
     * @throws \Exception
     */
    public function convertBasketQuantityToOfferQuantity(array $basketQuantity): array
    {
        $offersQuantity = [];
        foreach ($basketQuantity as $productQuantity) {
            $basketItemId = (int) $productQuantity->getProductId();
            $offerId = $this->basketService->getProductIdByBasketItemId($basketItemId);
            $offersQuantity[] = (new ProductQuantity())
                ->setProductId($offerId)
                ->setQuantity($productQuantity->getQuantity());
        }
        return $offersQuantity;
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
     * @throws \ImagickException
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

    /**
     * @param Store $store
     * @param array $servicesList
     * @param array $metroList
     * @param int|null $stockAmount
     * @param Shop|null $shop
     * @return ApiStore
     */
    protected function toApiFormat(Store $store, array $servicesList = [], array $metroList = [], int $stockAmount = -1, Shop $shop = null): ApiStore
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
        $result->setCode($store->getXmlId());
        $result->setTitle($title);
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

        if ($stockAmount >= 0) {
            $result->setIsByRequest($stockAmount === 0);
        }

        if ($shop) {
            $result->setProductQuantityString($shop->getAvailableAmount());
            $result->setPickupDate($shop->getPickupDate());
        }

        return $result;
    }
}
