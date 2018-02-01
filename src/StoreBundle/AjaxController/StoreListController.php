<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\AjaxController;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
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
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
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
            'Подгрузка успешна',
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
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
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
            'Подгрузка успешна',
            $shopListClass->getStores(
                $shopListClass->getFilterByRequest($request),
                $shopListClass->getOrderByRequest($request)
            )
        );
    }
    
    /**
     * @Route("/search/", methods={"GET"})
     * @param Request $request
     *
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
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
            'Подгрузка успешна',
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
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
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
            'Подгрузка успешна',
            $shopListClass->getStores(
                $shopListClass->getFilterByRequest($request),
                $shopListClass->getOrderByRequest($request),
                true
            )
        );
    }
    
    /**
     * @Route("/getByItem/", methods={"GET"})
     * @param Request $request
     *
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws FileNotFoundException
     * @return JsonResponse
     */
    public function getByItemAction(Request $request) : JsonResponse
    {
        $offerId = $request->get('offer', 0);
        
        if($offerId > 0) {
            \CBitrixComponent::includeComponentClass('fourpaws:shop.list');
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $shopListClass = new \FourPawsShopListComponent();
            return JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $shopListClass->getFormatedStoreByCollection($shopListClass->getActiveStoresByProduct($offerId))
            );
        }
        return JsonErrorResponse::create('Не указан id торгового предложения');
    }
}
