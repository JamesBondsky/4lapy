<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\StoreListRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Dto\Response\StoreListResponse;
use FourPaws\MobileApiBundle\Services\Api\StoreService;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;

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
     * @Parameter(
     *     name="token",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="identifier token from /start request"
     * )
     * @Response(
     *     response="200"
     * )
     * @Rest\View()
     * @param StoreListRequest $storeListRequest
     *
     * @throws \Exception
     * @return ApiResponse
     */
    public function getListAction(StoreListRequest $storeListRequest)
    {
        return new ApiResponse(new StoreListResponse($this->storeService->getList($storeListRequest)));
    }
}
