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
use FourPaws\StoreBundle\Service\StoreService;
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

    /**@var StoreService */
    protected $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }
    
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
    public function orderAction(Request $request): JsonResponse
    {
        return JsonSuccessResponse::createWithData(
            'Подгрузка успешна',
            $this->storeService->getStores(
                [
                    'filter' => $this->storeService->getFilterByRequest($request),
                    'order'  => $this->storeService->getOrderByRequest($request),
                ]
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
    public function checkboxFilterAction(Request $request): JsonResponse
    {
        return JsonSuccessResponse::createWithData(
            'Подгрузка успешна',
            $this->storeService->getStores(
                [
                    'filter' => $this->storeService->getFilterByRequest($request),
                    'order'  => $this->storeService->getOrderByRequest($request),
                ]
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
    public function searchAction(Request $request): JsonResponse
    {
        return JsonSuccessResponse::createWithData(
            'Подгрузка успешна',
            $this->storeService->getStores(
                [
                    'filter' => $this->storeService->getFilterByRequest($request),
                    'order'  => $this->storeService->getOrderByRequest($request),
                ]
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
    public function chooseCityAction(Request $request): JsonResponse
    {
        return JsonSuccessResponse::createWithData(
            'Подгрузка успешна',
            $this->storeService->getStores(
                [
                    'filter'               => $this->storeService->getFilterByRequest($request),
                    'order'                => $this->storeService->getOrderByRequest($request),
                    'activeStoreId'        => $request->get('active_store_id', 0),
                    'returnActiveServices' => true,
                    'returnSort'           => true,
                    'sortVal'              => $request->get('sort'),
                ]
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
    public function getByItemAction(Request $request): JsonResponse
    {
        $offerId = $request->get('offer', 0);

        if ((int)$offerId > 0) {
            return JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->storeService->getFormatedStoreByCollection(
                    ['storeCollection' => $this->storeService->getActiveStoresByProduct($offerId)]
                )
            );
        }

        return JsonErrorResponse::create('Не указан id торгового предложения');
    }
}
