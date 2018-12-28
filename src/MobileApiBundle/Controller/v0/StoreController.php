<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\StoreListAvailableRequest;
use FourPaws\MobileApiBundle\Dto\Request\StoreListRequest;
use FourPaws\MobileApiBundle\Dto\Request\StoreProductAvailableRequest;
use FourPaws\MobileApiBundle\Dto\Response\StoreListResponse;
use FourPaws\MobileApiBundle\Dto\Response\StoreProductAvailableResponse;
use FourPaws\MobileApiBundle\Services\Api\StoreService as ApiStoreService;

class StoreController extends FOSRestController
{
    /**
     * @var ApiStoreService
     */
    private $apiStoreService;

    public function __construct(
        ApiStoreService $apiStoreService
    )
    {
        $this->apiStoreService = $apiStoreService;
    }

    /**
     * @Rest\Get(path="/shop_list/")
     * @Rest\View()
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
     * @Rest\Get(path="/shops_list_available/")
     * @Rest\View()
     * @param StoreListAvailableRequest $storeListAvailableRequest
     *
     * @throws \Exception
     * @return StoreListResponse
     */
    public function getStoreListAvailableAction(StoreListAvailableRequest $storeListAvailableRequest): StoreListResponse
    {
        return new StoreListResponse($this->apiStoreService->getListAvailable($storeListAvailableRequest));
    }

    /**
     * @Rest\Post(path="/shop_goods_available/")
     * @Rest\View()
     * @param StoreProductAvailableRequest $storeProductAvailableRequest
     *
     * @throws \Exception
     * @return `
     */
    public function getStoreProductAvailableAction(StoreProductAvailableRequest $storeProductAvailableRequest): StoreProductAvailableResponse
    {
        $shop = $this->apiStoreService->getOne($storeProductAvailableRequest->getShopId());
        $products = $this->apiStoreService->getShopProductAvailable($storeProductAvailableRequest);
        return (new StoreProductAvailableResponse())
            ->setAvailableGoods($products['available'])
            ->setNotAvailableGoods($products['unAvailable'])
            ->setShop($shop);
    }
}
