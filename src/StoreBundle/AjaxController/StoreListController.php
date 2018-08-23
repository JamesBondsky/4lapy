<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Exception\NoStoresAvailableException;
use FourPaws\StoreBundle\Service\ShopInfoService;
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

    /**
     * @var ShopInfoService
     */
    protected $shopInfoService;

    /**
     * @var AjaxMess
     */
    protected $ajaxMess;

    /**
     * StoreListController constructor.
     *
     * @param ShopInfoService $shopInfoService
     * @param AjaxMess        $ajaxMess
     */
    public function __construct(ShopInfoService $shopInfoService, AjaxMess $ajaxMess)
    {
        $this->shopInfoService = $shopInfoService;
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * @Route("/order/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws RuntimeException
     * @throws ApplicationCreateException
     */
    public function orderAction(Request $request): JsonResponse
    {
        try {
            $result = JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->shopInfoService->shopListToArray(
                    $this->shopInfoService->getShopListByRequest($request)
                )
            );
        } catch (Exception $e) {
            $this->log()->error($e->getMessage());
            $result = $this->ajaxMess->getSystemError();
        }

        return $result;
    }

    /**
     * @Route("/checkboxFilter/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     */
    public function checkboxFilterAction(Request $request): JsonResponse
    {
        try {
            $result = JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->shopInfoService->shopListToArray(
                    $this->shopInfoService->getShopListByRequest($request)
                )
            );
        } catch (Exception $e) {
            $this->log()->error($e->getMessage());
            $result = $this->ajaxMess->getSystemError();
        }

        return $result;
    }

    /**
     * @Route("/search/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     */
    public function searchAction(Request $request): JsonResponse
    {
        try {
            $result = JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->shopInfoService->shopListToArray(
                    $this->shopInfoService->getShopListByRequest($request)
                )
            );
        } catch (Exception $e) {
            $this->log()->error($e->getMessage());
            $result = $this->ajaxMess->getSystemError();
        }

        return $result;
    }

    /**
     * @Route("/chooseCity/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     */
    public function chooseCityAction(Request $request): JsonResponse
    {
        try {
            if ((string)$request->get('findNearest') === 'Y') {
                $request->query->set('code', $request->get('codeNearest'));
            }

            $shopList = $this->shopInfoService->getShopListByRequest($request);
            $result = JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->shopInfoService->shopListToArray($shopList)
            );
        } catch (Exception $e) {
            $this->log()->error($e->getMessage());
            $result = $this->ajaxMess->getSystemError();
        }

        return $result;
    }

    /**
     * @Route("/getByItem/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     */
    public function getByItemAction(Request $request): JsonResponse
    {
        $offerId = $request->get('offer', 0);

        if ($offer = OfferQuery::getById((int)$offerId)) {
            try {
                try {
                    $shops = $this->shopInfoService->getShopsByOffer($offer);
                } catch (NoStoresAvailableException $e) {
                    $shops = new StoreCollection();
                }

                $result = $this->shopInfoService->shopListToArray(
                    $this->shopInfoService->getShopList($shops, $offer)
                );

                $result = JsonSuccessResponse::createWithData(
                    'Подгрузка успешна', $result
                );
            } catch (Exception $e) {
                $this->log()->error($e->getMessage());

                $result = $this->ajaxMess->getSystemError();
            }
        } else {
            $result = $this->ajaxMess->getNotIdError(' торгового предложения');
        }

        return $result;
    }
}
