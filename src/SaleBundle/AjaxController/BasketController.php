<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
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
    
    public function __construct(BasketService $basketService)
    {
        $this->basketService = $basketService;
    }
    
    /**
     * @Route("/add/", methods={"GET", "POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \RuntimeException
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function addAction(Request $request) : JsonResponse
    {
        $offerId  = $request->get('id', 0);
        $quantity = $request->get('quantity', 1);
        
        try {
            $this->basketService->addOfferToBasket($offerId, $quantity);
            
            $html = 'Тут малая корзина';
            
            return JsonSuccessResponse::createWithData('Товар добавлен в корзину', ['html' => $html]);
        } catch (\RuntimeException $e) {
            /**
             * Заменить exception на боевые. Ну, и сообщения.
             */
            return JsonErrorResponse::create($e->getMessage());
        }
    }
}
