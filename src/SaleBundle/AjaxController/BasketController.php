<?php
declare(strict_types=1);
/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\SaleBundle\Exception\BaseExceptionInterface;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\BasketService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BasketController
 *
 * @package FourPaws\SaleBundle\Controller
 * @Route("/basket")
 */
class BasketController extends Controller
{
    private $basketService;

    /**
     * BasketController constructor.
     *
     * @param BasketService $basketService
     */
    public function __construct(BasketService $basketService)
    {
        $this->basketService = $basketService;
    }

    /**
     * @Route("/add/", methods={"GET", "POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \RuntimeException
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function addAction(Request $request): JsonResponse
    {
        $offerId = (int)$request->get('offerId', 0);
        $quantity = (int)$request->get('quantity', 1);

        try {

            $this->basketService->addOfferToBasket($offerId, $quantity);
            $data = [
                'remainQuantity' => 10,
                'miniBasket' => $this->basketService::getMiniBasketHtml(),
                'disableAdd' => false
            ];
            $response = JsonSuccessResponse::createWithData(
                'Товар добавлен в корзину',
                $data,
                200,
                ['reload' => false]
            );

        } catch (BaseExceptionInterface $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
                ['reload' => true]
            );
        }

        return $response;
    }

    /**
     * @Route("/delete/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws \Exception
     * @throws \Bitrix\Main\ObjectNotFoundException
     *
     * @return JsonErrorResponse|JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $basketId = (int)$request->get('basketId', 0);
        try {
            $this->basketService->deleteOfferFromBasket($basketId);
            $data = [
                'basket' => $this->basketService::getBasketHtml()
            ];
            $response = JsonSuccessResponse::createWithData(
                '',
                $data,
                200,
                ['reload' => false]
            );
        } catch (NotFoundException $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage() . ' is not found.',
                200,
                [],
                ['reload' => true]
            );
        } catch (BaseExceptionInterface $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
                ['reload' => true]
            );
        }
        return $response;
    }

    /**
     * @Route("/update/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Exception
     *
     * @return JsonErrorResponse|JsonResponse
     */
    public function updateAction(Request $request)
    {
        $basketId = (int)$request->get('basketId', 0);
        $quantity = (int)$request->get('quantity', 1);

        try {
            $this->basketService->updateBasketQuantity($basketId, $quantity);
            $data = [
                'basket' => $this->basketService::getBasketHtml()
            ];
            $response = JsonSuccessResponse::createWithData(
                '',
                $data,
                200,
                ['reload' => false]
            );

        } catch (BaseExceptionInterface $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
                ['reload' => true]
            );
        }
        return $response;
    }
}
