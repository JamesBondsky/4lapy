<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\StoreListAvailableRequest;
use FourPaws\MobileApiBundle\Dto\Request\StoreListRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Dto\Response\StoreListResponse;
use FourPaws\MobileApiBundle\Services\Api\StoreService;

class StoreController extends FOSRestController
{
    /**
     * @var StoreService
     */
    private $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    /**
     * @Rest\Get(path="/shop_list/")
     * @Rest\View()
     * @param StoreListRequest $storeListRequest
     *
     * @throws \Exception
     * @return StoreListResponse
     */
    public function getStoreListAction(StoreListRequest $storeListRequest)
    {
        return new StoreListResponse($this->storeService->getList($storeListRequest));
    }

    /**
     * @Rest\Get(path="/shops_list_available/")
     * @Rest\View()
     * @param StoreListAvailableRequest $storeListAvailableRequest
     *
     * @throws \Exception
     * @return StoreListResponse
     */
    public function getStoreListAvailableAction(StoreListAvailableRequest $storeListAvailableRequest)
    {
        return new StoreListResponse($this->storeService->getListAvailable($storeListAvailableRequest));
    }
}
