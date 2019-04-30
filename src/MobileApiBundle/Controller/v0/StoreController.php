<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\App\Application;
use FourPaws\MobileApiBundle\Dto\Request\ShopsForCheckoutRequest;
use FourPaws\MobileApiBundle\Dto\Request\ShopsForProductCardRequest;
use FourPaws\MobileApiBundle\Dto\Request\StoreListRequest;
use FourPaws\MobileApiBundle\Dto\Response\StoreListAvailableResponse;
use FourPaws\MobileApiBundle\Dto\Response\StoreListResponse;
use FourPaws\MobileApiBundle\Services\Api\StoreService as ApiStoreService;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;
use FourPaws\StoreBundle\Service\ShopInfoService;

class StoreController extends FOSRestController
{
    /**
     * @var ApiStoreService
     */
    private $apiStoreService;

    /**
     * @var ApiUserService
     */
    private $apiUserService;


    public function __construct(
        ApiStoreService $apiStoreService,
        ApiUserService $apiUserService
    )
    {
        $this->apiStoreService = $apiStoreService;
        $this->apiUserService = $apiUserService;
    }

    /**
     *
     *
     * @Rest\Get(path="/shop_list/")
     * @Rest\View(serializerGroups={"Default", "withShopDetails"})
     * @param StoreListRequest $storeListRequest
     *
     * @throws \Exception
     * @return StoreListResponse
     */
    public function getStoreListAction(StoreListRequest $storeListRequest): StoreListResponse
    {
        return new StoreListResponse($this->apiStoreService->getList($storeListRequest));
    }

    /**
     * Используется для вывода магазинов в карточке товара с указанием кол-ва товара, сроком поставки и т.д.
     *
     * @Rest\Get(path="/get_shops_available/")
     * @Rest\View(serializerGroups={"Default", "withProductInfo"})
     * @param ShopsForProductCardRequest $storeAvailableRequest
     *
     * @throws \Exception
     * @return StoreListResponse
     */
    public function getShopsForProductCardAction(ShopsForProductCardRequest $storeAvailableRequest): StoreListResponse
    {
        $storeCollection = $this->apiStoreService->getListWithProductAvailability(
            $storeAvailableRequest->getProductId(),
            $storeAvailableRequest->getCityId()
        );

        /** @var ShopInfoService $shopInfoService */
        $shopInfoService = Application::getInstance()->getContainer()->get(ShopInfoService::class);

        $stores = $storeCollection->getValues();
        array_walk($stores, [$shopInfoService, 'locationTypeSortDecorate']);
        usort($stores, [$shopInfoService, 'shopCompareByLocationType']);
        array_walk($stores, [$shopInfoService, 'locationTypeSortUndecorate']);

        $storeCollection = new ArrayCollection($stores);

        return new StoreListResponse($storeCollection);
    }

    /**
     * Используется в корзине для вывода возможных магазинов для самовывоза
     *
     * @Rest\Get(path="/shops_list_availableV2/")
     * @Rest\View(serializerGroups={"Default", "withPickupInfo", "basket"})
     *
     * @param ShopsForCheckoutRequest $shopsForCheckoutRequest
     * @return StoreListAvailableResponse
     * @throws \Exception
     */
    public function getStoreListAvailableAction(ShopsForCheckoutRequest $shopsForCheckoutRequest): StoreListAvailableResponse
    {
        $metroStations = $shopsForCheckoutRequest->getMetroStations();
        return new StoreListAvailableResponse(
            $this->apiStoreService->getListWithProductsInBasketAvailability($metroStations)
        );
    }
}
