<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\StoreBundle\Service\StoreService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class StoreListController
 *
 * @package FourPaws\UserBundle\Controller
 * @Route("/list")
 */
class StoreListController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**@var StoreService */
    protected $storeService;
    /** @var AjaxMess */
    private $ajaxMess;

    /**
     * StoreListController constructor.
     *
     * @param StoreService $storeService
     * @param AjaxMess     $ajaxMess
     */
    public function __construct(StoreService $storeService, AjaxMess $ajaxMess)
    {
        $this->storeService = $storeService;
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * @Route("/order/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws RuntimeException
     */
    public function orderAction(Request $request): JsonResponse
    {
        try {
            return JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->storeService->getStoresInfo(
                    [
                        'filter' => $this->storeService->getFilterByRequest($request),
                        'order'  => $this->storeService->getOrderByRequest($request),
                        'storesAlways' => true, // отвечает за логику показа магазинов если нет в городе
                    ]
                )
            );
        } catch (Exception $e) {
            $this->log()->error($e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/checkboxFilter/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     */
    public function checkboxFilterAction(Request $request): JsonResponse
    {
        try {
            return JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->storeService->getStoresInfo(
                    [
                        'filter' => $this->storeService->getFilterByRequest($request),
                        'order'  => $this->storeService->getOrderByRequest($request),
                        'storesAlways' => true, // отвечает за логику показа магазинов если нет в городе
                    ]
                )
            );
        } catch (Exception $e) {
            $this->log()->error($e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/search/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     */
    public function searchAction(Request $request): JsonResponse
    {
        try {
            return JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->storeService->getStoresInfo(
                    [
                        'filter' => $this->storeService->getFilterByRequest($request),
                        'order'  => $this->storeService->getOrderByRequest($request),
                    ]
                )
            );
        } catch (Exception $e) {
            $this->log()->error($e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/chooseCity/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     */
    public function chooseCityAction(Request $request): JsonResponse
    {
        try {
            if ((string)$request->get('findNearest') === 'Y') {
                $request->query->set('code', $request->get('codeNearest'));
                /** @todo если передать координаты пользователя - то можно подсветить ближайший магазин, либо удаляем комменты */
                return JsonSuccessResponse::createWithData(
                    'Подгрузка успешна',
                    $this->storeService->getStoresInfo(
                        [
                            'filter'       => $this->storeService->getFilterByRequest($request),
                            'storesAlways' => true, // отвечает за логику показа магазинов если нет в городе
//                        'order'         => ['DISTANCE_'.$lat.'_'.$lon => 'asc'],
//                        'activeStoreId' => 'first',
                        ]
                    )
                );
            }

            return JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->storeService->getStoresInfo(
                    [
                        'filter'               => $this->storeService->getFilterByRequest($request),
                        'order'                => $this->storeService->getOrderByRequest($request),
                        'activeStoreId'        => $request->get('active_store_id', 0),
                        'returnActiveServices' => true,
                        'returnSort'           => true,
                        'sortVal'              => $request->get('sort'),
                        'storesAlways'         => true, // отвечает за логику показа магазинов если нет в городе
                    ]
                )
            );
        } catch (Exception $e) {
            $this->log()->error($e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/getByItem/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     */
    public function getByItemAction(Request $request): JsonResponse
    {
        $offerId = $request->get('offer', 0);

        if ((int)$offerId > 0) {
            try {
                [$storeCollection, $hideTab] = $this->storeService->getActiveStoresByProduct($offerId);
                return JsonSuccessResponse::createWithData(
                    'Подгрузка успешна',
                    $this->storeService->getFormatedStoreByCollection(
                        ['storeCollection' => $storeCollection, 'hideTab' => $hideTab]
                    )
                );
            } catch (Exception $e) {
                $this->log()->error($e->getMessage());

                return $this->ajaxMess->getSystemError();
            }
        }

        return $this->ajaxMess->getNotIdError(' торгового предложения');
    }
}
