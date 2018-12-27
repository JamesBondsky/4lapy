<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\MobileApiBundle\Dto\Request\GoodsListRequest;
use FourPaws\MobileApiBundle\Dto\Request\GoodsSearchBarcodeRequest;
use FourPaws\MobileApiBundle\Dto\Request\SpecialOffersRequest;
use FourPaws\MobileApiBundle\Dto\Response\SpecialOffersResponse;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct\Tag;
use FourPaws\MobileApiBundle\Dto\Request\GoodsItemRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\MobileApiBundle\Services\Api\CatalogService as ApiCatalogService;


class ProductController extends FOSRestController
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
     * @Rest\Get(path="/special_offers/")
     * @Rest\View()
     *
     * @param SpecialOffersRequest $specialOffersRequest
     * @return ApiResponse
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getSpecialOffersAction(SpecialOffersRequest $specialOffersRequest): ApiResponse
    {
        $goods = [];

        $collection = (new OfferQuery())
            ->withFilterParameter('ACTIVE', 'Y')
            ->withFilterParameter('!PROPERTY_IS_SALE_VALUE', false)
            ->withNav([
                'iNumPage' => $specialOffersRequest->getPage(),
                'nPageSize' => $specialOffersRequest->getCount()
            ])
            ->withOrder(['SORT' => 'ASC', 'NAME' => 'ASC'])
            ->withSelect(['ID'])
            ->exec();

        /** @var Offer $offer */
        foreach ($collection->getValues() as $offer) {
            $oProduct = $offer->getProduct();
            $productCategory = $oProduct->getCategory()->toArray();

            // ToDo: имплементировать логику подсчета бонусов
            $bonusCount = $offer->getBonusCount(3);
            $offer = $offer->toArray();

            $product = new ShortProduct();
            $product
                ->setId($offer['ID'])
                ->setTitle($offer['NAME'])
                ->setXmlId($offer['XML_ID'])
                ->setPicture(($offer['PROPERTY_VALUES']['IMG'][0]) ? \CFile::GetPath($offer['PROPERTY_VALUES']['IMG'][0]) : '')
                ->setInPack($offer['PROPERTY_VALUES']['MULTIPLICITY'])
                ->setWebPage($offer['DETAIL_PAGE_URL']);

            $productPrice = (new Price())
                ->setActual($offer['price'])
                ->setOld($offer['oldPrice']);
            $product->setPrice($productPrice);

            $tags = [];
            if ($offer['PROPERTY_IS_HIT_VALUE'] != false) {
                $tags[] = (new Tag())->setImg(MarkHelper::MARK_HIT_IMAGE_SRC);
            }
            if ($offer['PROPERTY_IS_NEW_VALUE'] != false) {
                $tags[] = (new Tag())->setImg(MarkHelper::MARK_NEW_IMAGE_SRC);
            }
            if ($offer['PROPERTY_IS_SALE_VALUE'] != false) {
                $tags[] = (new Tag())->setImg(MarkHelper::MARK_SALE_IMAGE_SRC);
            }
            $product->setTag($tags);

            $product->setBonusAll($bonusCount);
            $product->setBonusUser($bonusCount);

            $goods[] = $product;
        }
        $cdbResult = $collection->getCdbResult();

        $response = new SpecialOffersResponse();
        $response
            ->setGoods($goods)
            ->setTotalItem(intval($cdbResult->NavRecordCount))
            ->setTotalPages(intval($cdbResult->NavPageCount));

        return (new ApiResponse())->setData($response);
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
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getGoodsItemAction(GoodsItemRequest $goodsItemRequest)
    {
        $offer = $this->apiCatalogService->getOffer($goodsItemRequest->getId());
        return (new Response())->setData(['goods' => $offer]);
    }

    /**
     * @Rest\Get("/goods_search_barcode/")
     * @see GoodsSearchBarcodeRequest
     */
    public function getGoodsSearchBarcodeAction()
    {
    }
}
