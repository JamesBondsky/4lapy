<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
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
    /** @var AjaxMess */
    private $ajaxMess;

    /**@var StoreService */
    protected $storeService;

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
        } catch (FileNotFoundException $e) {
            /** Ошибка не найденного файла возникать не должна */
        } catch (ArgumentException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/checkboxFilter/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
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
        } catch (FileNotFoundException $e) {
            /** Ошибка не найденного файла возникать не должна */
        } catch (ArgumentException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/search/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
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
        } catch (FileNotFoundException $e) {
            /** Ошибка не найденного файла возникать не должна */
        } catch (ArgumentException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/chooseCity/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function chooseCityAction(Request $request): JsonResponse
    {
        try {
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
        } catch (FileNotFoundException $e) {
            /** Ошибка не найденного файла возникать не должна */
        } catch (ArgumentException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }

    /**
     * @Route("/getByItem/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
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
            } catch (FileNotFoundException $e) {
                /** Ошибка не найденного файла возникать не должна */
            } catch (ArgumentException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|\RuntimeException|\Exception $e) {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            }
        }

        return $this->ajaxMess->getNotIdError(' торгового предложения');
    }
}
