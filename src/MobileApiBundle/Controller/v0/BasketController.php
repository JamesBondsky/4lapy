<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\AppBundle\Exception\NotFoundException;
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
use Bitrix\Sale\BasketItem;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\MobileApiBundle\Dto\Object\OrderCalculate;
use FourPaws\MobileApiBundle\Dto\Request\UserCartRequest;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Services\Api\OrderService as OrderServiceForApi;
use FourPaws\SaleBundle\Service\BasketService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\SaleBundle\Discount\Manzana;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;

/**
 * Class BasketController
 * @package FourPaws\MobileApiBundle\Controller
 */
class BasketController extends FOSRestController
{
    /**
     * @var BasketService
     */
    private $basketService;
    /**
     * @var Manzana
     */
    private $manzana;
    /**
     * @var CouponStorageInterface
     */
    private $couponStorage;

    private $orderServiceForApi;

    public function __construct(
        BasketService $basketService,
        Manzana $manzana,
        CouponStorageInterface $couponStorage,
        OrderServiceForApi $orderServiceForApi
    )
    {
        $this->basketService = $basketService;
        $this->manzana = $manzana;
        $this->couponStorage = $couponStorage;
        $this->orderServiceForApi = $orderServiceForApi;
    }

    /**
     * Метод рассчитывает корзину.
     * @Rest\Post(path="/user_cart_calc/")
     * @Rest\View()
     *
     * @see UserCartCalcRequest
     * @see UserCartCalcResponse
     */
    public function userCartCalcAction(UserCartCalcRequest $userCartCalcRequest)
    {
        return (new UserCartCalcResponse());
    }

    /**
     * @Rest\Get("/user_cart/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param UserCartRequest $userCartRequest
     * @return UserCartResponse
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaPromocodeUnavailableException
     */
    public function getUserCartAction(UserCartRequest $userCartRequest)
    {
        $basket = $this->basketService->getBasket();
        $products = [];
        foreach ($basket->getOrderableItems() as $basketItem) {
            /** @var $basketItem BasketItem */
            $products[] = $this->orderServiceForApi->getProduct($basketItem->getId(), $basketItem->getProductId(), $basketItem->getQuantity());
        }

        $orderParameter = (new OrderParameter())
            ->setProducts($products);

        $totalPrice = (new Price())
            ->setOld($this->orderServiceForApi->calculateProductsPrice($products))
            ->setActual($this->orderServiceForApi->calculateProductsDiscountPrice($products));

        $orderCalculate = (new OrderCalculate())
            ->setTotalPrice($totalPrice);

        if ($promoCode = $userCartRequest->getPromoCode()) {
            // toDo проверить как работают промо-коды
            $this->manzana->setPromocode($promoCode);
            $this->manzana->calculate();
            $this->couponStorage->clear();
            $this->couponStorage->save($promoCode);
            $orderCalculate->setPromoCodeResult($promoCode);
        }

        return (new UserCartResponse())
            ->setCartCalc($orderCalculate)
            ->setCartParam($orderParameter);
    }


    /**
     * @Rest\Post(path="/user_cart_info")
     */
    public function userCartInfoAction()
    {
    }

    /**
     * Метод добавляет кол-во товара к уже имеющемуся кол-ву.
     * @Rest\Post(path="/user_cart/")
     * @Rest\View()
     * @param UserCartRequest $postUserCartRequest
     * @return UserCartResponse
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaPromocodeUnavailableException
     */
    public function postUserCartAction(PostUserCartRequest $postUserCartRequest)
    {
        $userCarRequest = new UserCartRequest();
        return $this->getUserCartAction($userCarRequest);
    }

    /**
     * Метод выставляет кол-во товара (без добавления к уже имеющемуся кол-ву).
     * Количество 0 удаляет товариз корзины.
     * @Rest\Put(path="/user_cart/")
     * @Rest\View()
     * @param PutUserCartRequest $putUserCartRequest
     * @return UserCartResponse
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaPromocodeUnavailableException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     */
    public function putUserCartAction(PutUserCartRequest $putUserCartRequest)
    {
        foreach ($putUserCartRequest->getGoods() as $productQuantity) {
            try {
                $this->basketService->updateBasketQuantity($productQuantity->getProductId(), $productQuantity->getQuantity());
            }
            catch (\FourPaws\SaleBundle\Exception\NotFoundException $e) {
                throw new RuntimeException('Товар не найден');
            }
        }
        return $this->getUserCartAction(new UserCartRequest());
    }

    /**
     * Оформление корзины / оформить заказ
     * @Rest\Post(path="/user_cart_order/")
     * @Rest\View()
     *
     * @see UserCartOrderRequest
     * @see UserCartOrderResponse
     */
    public function postUserCartOrderAction(UserCartOrderRequest $userCartOrderRequest)
    {
        return (new UserCartOrderResponse());
    }
}
