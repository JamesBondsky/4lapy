<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Sale\Location\LocationTable;
use Exception;
use FourPaws\Adapter\DaDataLocationAdapter;
use FourPaws\Adapter\Model\Output\BitrixLocation;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Collection\StoreCollection;
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
                $this->storeService->getStores(
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
                $this->storeService->getStores(
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
                $this->storeService->getStores(
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
                $storeRes = $this->storeService->getStores(
                    [
                        'filter' => $this->storeService->getFilterByRequest($request),
//                        'order'         => ['DISTANCE_'.$lat.'_'.$lon => 'asc'],
//                        'activeStoreId' => 'first',
                    ]
                );
                if (empty($storeRes['items'])) {
                    /** region */
                    $code = $request->get('code');
                    $codeList = json_decode($code, true);
                    if (\is_array($codeList)) {
                        $dadataLocationAdapter = new DaDataLocationAdapter();
                        /** @var BitrixLocation $bitrixLocation */
                        $bitrixLocation = $dadataLocationAdapter->convertFromArray($codeList);
                        $regionId = $bitrixLocation->getRegionId();
                    }
                    else{
                        $regionId = LocationTable::query()->setFilter(['=CODE'=>$code])->setSelect(['REGION_ID'])->setLimit(1)->exec()->fetch()['REGION_ID'];
                    }
                    $storeRes = $this->storeService->getStores(
                        [
                            'filter' => ['REGION_ID' => $regionId],
                        ]
                    );
                    /** moscow */
                    if (empty($storeRes['items'])) {
                        $storeRes = $this->storeService->getStores(
                            [
                                'filter' => ['UF_LOCATION' => LocationService::LOCATION_CODE_MOSCOW],
                            ]
                        );
                    }
                }
                return JsonSuccessResponse::createWithData(
                    'Подгрузка успешна',
                    $storeRes
                );
            }

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
                return JsonSuccessResponse::createWithData(
                    'Подгрузка успешна',
                    $this->storeService->getFormatedStoreByCollection(
                        ['storeCollection' => $this->storeService->getActiveStoresByProduct($offerId)]
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
