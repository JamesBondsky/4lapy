<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\FilterListRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\FilterListResponse;
use FourPaws\MobileApiBundle\Services\Api\CatalogService;

class CatalogController extends FOSRestController
{
    /**
     * @Rest\Get("/filter_list/")
     * @Rest\View(serializerGroups={"Default", "response"})
     * @see  FilterListRequest
     * @see  FilterListResponse
     *
     * @param CatalogService    $catalogService
     * @param FilterListRequest $filterListRequest
     *
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @throws \FourPaws\MobileApiBundle\Exception\CategoryNotFoundException
     * @return Response
     */
    public function getFilterListAction(CatalogService $catalogService, FilterListRequest $filterListRequest): Response
    {
        return new Response(
            new FilterListResponse(
                $catalogService->getFilters($filterListRequest->getId())
            )
        );
    }
}
