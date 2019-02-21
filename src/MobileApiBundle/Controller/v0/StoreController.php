<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\ShopsForProductCardRequest;
use FourPaws\MobileApiBundle\Dto\Request\StoreListRequest;
use FourPaws\MobileApiBundle\Dto\Response\StoreListResponse;
use FourPaws\MobileApiBundle\Services\Api\StoreService as ApiStoreService;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;

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
        return new StoreListResponse(
            $this->apiStoreService->getListWithProductAvailability(
                $storeAvailableRequest->getProductId()
            )
        );
    }

    /**
     * Используется в корзине для вывода возможных магазинов для самовывоза
     *
     * @Rest\Get(path="/shops_list_availableV2/")
     * @Rest\View(serializerGroups={"Default", "withPickupInfo"})
     *
     * @throws \Exception
     * @return StoreListResponse
     */
    public function getStoreListAvailableAction(): StoreListResponse
    {
        return new StoreListResponse(
            $this->apiStoreService->getListWithProductsInBasketAvailability()
        );
    }
}
