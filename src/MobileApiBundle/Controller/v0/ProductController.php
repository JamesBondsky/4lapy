<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\FilterListRequest;
use FourPaws\MobileApiBundle\Dto\Request\GoodsListRequest;
use FourPaws\MobileApiBundle\Dto\Request\GoodsSearchBarcodeRequest;
use FourPaws\MobileApiBundle\Dto\Request\SpecialOffersRequest;
use FourPaws\MobileApiBundle\Dto\Response\FilterListResponse;
use FourPaws\MobileApiBundle\Dto\Response\GoodsListResponse;
use FourPaws\MobileApiBundle\Dto\Response\SpecialOffersResponse;

class ProductController extends FOSRestController
{
    /**
     * @Rest\Get(path="/special_offers")
     * @see SpecialOffersRequest
     * @see SpecialOffersResponse
     */
    public function getSpecialOffersAction()
    {
        /**
         * @todo кеширование
         */
    }

    /**
     * @Rest\Get("/goods_list")
     * @see GoodsListRequest
     * @see GoodsListResponse
     */
    public function getGoodsListAction()
    {
    }

    /**
     * @Rest\Get("/filter_list")
     * @see FilterListRequest
     * @see FilterListResponse
     */
    public function getFilterListAction()
    {
    }

    /**
     * @Rest\Get("/goods_search_barcode")
     * @see GoodsSearchBarcodeRequest
     */
    public function getGoodsSearchBarcodeAction()
    {
    }
}
