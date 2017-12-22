<?php /*
* @copyright Copyright (c) ADV/web-engineering co
*/

namespace FourPaws\StoreBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
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
     * @return \FourPaws\App\Response\JsonResponse
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     * @throws \Exception
     */
    public function orderAction(Request $request) : JsonResponse
    {
        \CBitrixComponent::includeComponentClass('fourpaws:shop.list');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $shopListClass = new \FourPawsShopListComponent();
        return JsonSuccessResponse::createWithData('', $shopListClass->getStores($shopListClass->getFilterByRequest($request), $shopListClass->getOrderByRequest($request)));
        return JsonErrorResponse::create('Неизвестная ошибка');
    }
    
    /**
     * @Route("/checkboxFilter/", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     * @throws \Exception
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     */
    public function checkboxFilterAction(Request $request) : JsonResponse
    {
        \CBitrixComponent::includeComponentClass('fourpaws:shop.list');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $shopListClass = new \FourPawsShopListComponent();
        return JsonSuccessResponse::createWithData('', $shopListClass->getStores($shopListClass->getFilterByRequest($request), $shopListClass->getOrderByRequest($request)));
        return JsonErrorResponse::create('Неизвестная ошибка');
    }
    
    /**
     * @Route("/search/", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     * @throws \Exception
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     */
    public function searchAction(Request $request) : JsonResponse
    {
        \CBitrixComponent::includeComponentClass('fourpaws:shop.list');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $shopListClass = new \FourPawsShopListComponent();
        return JsonSuccessResponse::createWithData('', $shopListClass->getStores($shopListClass->getFilterByRequest($request), $shopListClass->getOrderByRequest($request)));
        return JsonErrorResponse::create('Неизвестная ошибка');
    }
    
    /**
     * @Route("/chooseCity/", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     * @throws \Exception
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     */
    public function chooseCityAction(Request $request) : JsonResponse
    {
        \CBitrixComponent::includeComponentClass('fourpaws:shop.list');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $shopListClass = new \FourPawsShopListComponent();
        return JsonSuccessResponse::createWithData('', $shopListClass->getStores($shopListClass->getFilterByRequest($request), $shopListClass->getOrderByRequest($request)));
        return JsonErrorResponse::create('Неизвестная ошибка');
    }
}