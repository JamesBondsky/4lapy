<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\AjaxController;

use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthController
 *
 * @package FourPaws\UserBundle\Controller
 * @Route("/list")
 */
class StoreListController extends Controller
{
    /**
     * @Route("/order/", methods={"GET"})
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @throws FileNotFoundException
     * @throws \Exception
     * @return JsonResponse
     */
    public function orderAction(Request $request) : JsonResponse
    {
        \CBitrixComponent::includeComponentClass('fourpaws:shop.list');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $shopListClass = new \FourPawsShopListComponent();
        
        return JsonSuccessResponse::createWithData(
            '',
            $shopListClass->getStores(
                $shopListClass->getFilterByRequest($request),
                $shopListClass->getOrderByRequest($request)
            )
        );
    }
    
    /**
     * @Route("/checkboxFilter/", methods={"GET"})
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws FileNotFoundException
     * @return JsonResponse
     */
    public function checkboxFilterAction(Request $request) : JsonResponse
    {
        \CBitrixComponent::includeComponentClass('fourpaws:shop.list');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $shopListClass = new \FourPawsShopListComponent();
        
        return JsonSuccessResponse::createWithData(
            '',
            $shopListClass->getStores(
                $shopListClass->getFilterByRequest($request),
                $shopListClass->getOrderByRequest($request)
            )
        );
    }
    
    /**
     * @Route("/search/", methods={"POST"})
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws FileNotFoundException
     * @return JsonResponse
     */
    public function searchAction(Request $request) : JsonResponse
    {
        \CBitrixComponent::includeComponentClass('fourpaws:shop.list');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $shopListClass = new \FourPawsShopListComponent();
        
        return JsonSuccessResponse::createWithData(
            '',
            $shopListClass->getStores(
                $shopListClass->getFilterByRequest($request),
                $shopListClass->getOrderByRequest($request)
            )
        );
    }
    
    /**
     * @Route("/chooseCity/", methods={"GET"})
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws FileNotFoundException
     * @return JsonResponse
     */
    public function chooseCityAction(Request $request) : JsonResponse
    {
        \CBitrixComponent::includeComponentClass('fourpaws:shop.list');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $shopListClass = new \FourPawsShopListComponent();
        
        return JsonSuccessResponse::createWithData(
            '',
            $shopListClass->getStores(
                $shopListClass->getFilterByRequest($request),
                $shopListClass->getOrderByRequest($request),
                true
            )
        );
    }
}
