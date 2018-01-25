<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Object\OrderParameter;
use FourPaws\MobileApiBundle\Dto\Request\PostUserCartRequest;
use FourPaws\MobileApiBundle\Dto\Request\PutUserCartRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserCartCalcRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserCartOrderRequest;
use FourPaws\MobileApiBundle\Dto\Response\PostUserCartResponse;
use FourPaws\MobileApiBundle\Dto\Response\PutUserCartResponse;
use FourPaws\MobileApiBundle\Dto\Response\UserCartCalcResponse;
use FourPaws\MobileApiBundle\Dto\Response\UserCartOrderResponse;
use FourPaws\MobileApiBundle\Dto\Response\UserCartResponse;

class BasketController extends FOSRestController
{
    /**
     * Метод рассчитывает корзину.
     * @Rest\Post(path="/user_cart_calc")
     * @see UserCartCalcRequest
     * @see UserCartCalcResponse
     */
    public function userCartCalcAction()
    {
    }

    /**
     * @Rest\Get(path="/user_cart")
     * @see UserCartResponse
     */
    public function userCartAction()
    {
    }

    /**
     * @Rest\Post(path="/user_cart_info")
     * @see OrderParameter
     */
    public function userCartInfoAction()
    {
    }

    /**
     * Метод добавляет кол-во товара к уже имеющемуся кол-ву.
     * @Rest\Post(path="/user_cart")
     * @see PostUserCartRequest
     * @see PostUserCartResponse
     */
    public function postUserCartAction()
    {
    }

    /**
     * Метод выставляет кол-во товара (без добавления к уже имеющемуся кол-ву).
     * Количество 0 удаляет товариз корзины.
     * @Rest\Put(path="/user_cart")
     * @see PutUserCartRequest
     * @see PutUserCartResponse
     */
    public function putUserCartAction()
    {
    }

    /**
     * Оформление корзины / оформить заказ
     * @Rest\Post(path="/user_cart_order")
     * @see UserCartOrderRequest
     * @see UserCartOrderResponse
     */
    public function postUserCartOrder()
    {
    }
}
