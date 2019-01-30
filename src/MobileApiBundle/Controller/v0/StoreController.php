<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\StoreAvailableRequest;
use FourPaws\MobileApiBundle\Dto\Request\StoreListAvailableRequest;
use FourPaws\MobileApiBundle\Dto\Request\StoreListRequest;
use FourPaws\MobileApiBundle\Dto\Request\StoreProductAvailableRequest;
use FourPaws\MobileApiBundle\Dto\Response\StoreListResponse;
use FourPaws\MobileApiBundle\Dto\Response\StoreProductAvailableResponse;
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
     * @Rest\Get(path="/shops_list_availableV2/")
     * @Rest\View()
     * @param StoreListAvailableRequest $storeListAvailableRequest
     *
     * @throws \Exception
     * @return StoreListResponse
     */
    public function getStoreListAvailableAction(StoreListAvailableRequest $storeListAvailableRequest): StoreListResponse
    {
        if ($storeListAvailableRequest->getCityId()) {
            $this->apiUserService->updateLocationId($storeListAvailableRequest->getCityId());
        }
        $productQuantity = $this->apiStoreService->convertBasketQuantityToOfferQuantity($storeListAvailableRequest->getGoods());

        return new StoreListResponse(
            $this->apiStoreService->getListAvailable($productQuantity)
        );
    }

    /**
     * @Rest\Post(path="/shop_goods_available/")
     * @Rest\View()
     * @param StoreProductAvailableRequest $storeProductAvailableRequest
     *
     * @throws \Exception
     * @return StoreProductAvailableResponse
     */
    public function getStoreProductAvailableAction(StoreProductAvailableRequest $storeProductAvailableRequest): StoreProductAvailableResponse
    {
        $storeCode = $storeProductAvailableRequest->getStoreCode();
        $shop = $this->apiStoreService->getOne($storeCode);
        $basketProductCollection = $this->apiStoreService->getStoreProductAvailable($storeProductAvailableRequest->getGoods());
        $availableProducts = $basketProductCollection->getAvailableInStore($storeCode);
        $unAvailableProducts = $basketProductCollection->getUnAvailableInStore($storeCode);

        if (!empty($availableProducts) && !empty($unAvailableProducts)) {
            $shop->setAvailabilityStatus('available_part');
        } else if (!empty($availableProducts)) {
            $shop->setAvailabilityStatus('available');
        } else if (!empty($unAvailableProducts)) {
            $shop->setAvailabilityStatus('not_available');
        }

        return (new StoreProductAvailableResponse())
            ->setAvailableGoods($availableProducts)
            ->setNotAvailableGoods($unAvailableProducts)
            ->setShop($shop);
    }

    /**
     * Метод используется в карточке товара для отображения магазинов, в которых доступен товар в нужном кол-ве
     *
     * @Rest\Get(path="/get_shops_available/")
     * @Rest\View()
     * @param StoreAvailableRequest $storeAvailableRequest
     *
     * @throws \Exception
     * @return StoreListResponse
     */
    public function getShopsAvailableAction(StoreAvailableRequest $storeAvailableRequest): StoreListResponse
    {
        return new StoreListResponse(
            $this->apiStoreService->getListAvailable(
                $storeAvailableRequest->getGoods()
            )
        );
    }
}
