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
use FourPaws\StoreBundle\Dto\ShopList\Shop;
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
            $shops = $this->shopInfoService->getShopList(
                $this->shopInfoService->getShopsByRequest($request)
            );

            $result = JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->shopInfoService->shopListToArray($shops)
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
            $shops = $this->shopInfoService->getShopList(
                $this->shopInfoService->getShopsByRequest($request)
            );

            $result = JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->shopInfoService->shopListToArray($shops)
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
            $shops = $this->shopInfoService->getShopList(
                $this->shopInfoService->getShopsByRequest($request)
            );

            $result = JsonSuccessResponse::createWithData(
                'Подгрузка успешна',
                $this->shopInfoService->shopListToArray($shops)
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

            $shops = $this->shopInfoService->getShopsByRequest($request);
            $shopList = $this->shopInfoService->getShopList($shops);

            $haveMetro = false;
            $activeStoreId = $request->get('active_store_id', 0);
            /** @var Shop $item */
            foreach ($shopList->getItems() as $i => $item) {
                if ($item->getMetro()) {
                    $haveMetro = true;
                }

                if (($activeStoreId === 'first' && $i === 0) ||
                    ($item->getId() === $activeStoreId)
                ) {
                    $item->setActive(true);
                }
            }

            $shopList->setSortHtml(
                $this->shopInfoService->getSortHtml(
                    $request->get('sort', ''),
                    $haveMetro
                )
            );

            /* @todo */
            $locationName = 'Все города';
            if (!empty($params['region_id']) || !empty($params['city_code'])) {
                $result['location_name'] = '';//если пустое что-то пошло не так
                $loc = null;
                if (!empty($params['region_id'])) {
                    $loc = LocationTable::query()->setFilter(['ID' => $params['region_id']])->setCacheTtl(360000)->setSelect(['LOC_NAME' => 'NAME.NAME'])->exec()->fetch();
                } elseif (!empty($params['city_code'])) {
                    $loc = LocationTable::query()->setFilter(['=CODE' => $params['city_code']])->setCacheTtl(360000)->setSelect(['LOC_NAME' => 'NAME.NAME'])->exec()->fetch();
                }
                if ($loc !== null && empty($result['location_name'])) {
                    $locationName = $loc['LOC_NAME'];
                }
            }

            $shopList->setLocationName($locationName);

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
                    $this->shopInfoService->getShopList($shops, OfferQuery::getById($offerId))
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
