<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\FilterListRequest;
use FourPaws\MobileApiBundle\Dto\Response\FilterListResponse;
use FourPaws\MobileApiBundle\Services\Api\CatalogService as ApiCatalogService;

class CatalogController extends FOSRestController
{

    /**
     * @var ApiCatalogService
     */
    private $apiCatalogService;

    public function __construct(ApiCatalogService $apiCatalogService)
    {
        $this->apiCatalogService = $apiCatalogService;
    }

    /**
     * @Rest\Get("/filter_list/")
     * @Rest\View(serializerGroups={"Default", "response"})
     *
     * @param FilterListRequest $filterListRequest
     *
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @throws \FourPaws\MobileApiBundle\Exception\CategoryNotFoundException
     * @return FilterListResponse
     */
    public function getFilterListAction(FilterListRequest $filterListRequest): FilterListResponse
    {
        return new FilterListResponse(
            $this->apiCatalogService->getFilters($filterListRequest->getId())
        );
    }
}
