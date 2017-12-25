<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\AjaxController;

use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Route("/order/", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     * @throws \Exception
     * @return \FourPaws\App\Response\JsonResponse
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
     * @Route("/checkboxFilter/", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Exception
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     * @return \FourPaws\App\Response\JsonResponse
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Exception
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     * @return \FourPaws\App\Response\JsonResponse
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
     * @Route("/chooseCity/", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Exception
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     * @return \FourPaws\App\Response\JsonResponse
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
                $shopListClass->getOrderByRequest($request)
            )
        );
    }
}
