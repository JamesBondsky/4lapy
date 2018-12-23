<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\FilterListRequest;
use FourPaws\MobileApiBundle\Dto\Request\GoodsItemRequest;
use FourPaws\MobileApiBundle\Dto\Request\GoodsListRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\FilterListResponse;
use FourPaws\MobileApiBundle\Services\Api\CatalogService as ApiCatalogService;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @Rest\Get("/goods_list/")
     * @Rest\View()
     * @param Request $request
     * @param GoodsListRequest $goodsListRequest
     * @return Response\ProductListResponse
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\Catalog\Exception\CategoryNotFoundException
     */
    public function getGoodsListAction(Request $request, GoodsListRequest $goodsListRequest)
    {
        $categoryId = $goodsListRequest->getCategoryId();
        $sort = $goodsListRequest->getSort();
        $page = $goodsListRequest->getPage();
        $count = $goodsListRequest->getCount();

        $productsList = $this->apiCatalogService->getProductsList($request, $categoryId, $sort, $count, $page);
        /** @var \CIBlockResult $cdbResult */
        $cdbResult = $productsList->get('cdbResult');
        return (new Response\ProductListResponse())
            ->setProductList($productsList->get('products'))
            ->setTotalPages($cdbResult->NavPageCount)
            ->setTotalItems($cdbResult->NavRecordCount);
    }

    /**
     * @Rest\Get("/goods_item/")
     * @Rest\View()
     * @param GoodsItemRequest $goodsItemRequest
     * @return Response
     * @throws \Bitrix\Main\SystemException
     */
    public function getGoodsItemAction(GoodsItemRequest $goodsItemRequest)
    {
        $offer = $this->apiCatalogService->getOffer($goodsItemRequest->getId());
        return (new Response())->setData(['goods' => $offer]);
    }
}
