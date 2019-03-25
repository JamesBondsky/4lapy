<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\External\Exception\ManzanaPromocodeUnavailableException;
use FourPaws\MobileApiBundle\Dto\Request\PostUserCartRequest;
use FourPaws\MobileApiBundle\Dto\Request\PutUserCartRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserCartCalcRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserCartOrderRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\UserCartCalcResponse;
use FourPaws\MobileApiBundle\Dto\Response\UserCartOrderResponse;
use FourPaws\MobileApiBundle\Dto\Response\UserCartResponse;
use FourPaws\MobileApiBundle\Dto\Request\UserCartRequest;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Services\Api\OrderService as ApiOrderService;
use FourPaws\SaleBundle\Service\BasketService as AppBasketService;
use FourPaws\SaleBundle\Discount\Manzana;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\MobileApiBundle\Services\Api\BasketService as ApiBasketService;
use FourPaws\StoreBundle\Service\StoreService as AppStoreService;
use FourPaws\DeliveryBundle\Service\DeliveryService as AppDeliveryService;

/**
 * Class BasketController
 * @package FourPaws\MobileApiBundle\Controller
 */
class BasketController extends FOSRestController
{
    /**
     * @var AppBasketService
     */
    private $appBasketService;
    /**
     * @var ApiBasketService
     */
    private $apiBasketService;
    /**
     * @var Manzana
     */
    private $manzana;
    /**
     * @var CouponStorageInterface
     */
    private $couponStorage;

    /**
     * @var ApiOrderService
     */
    private $apiOrderService;

    /**
     * @var AppStoreService
     */
    private $appStoreService;

    /**
     * @var AppDeliveryService
     */
    private $appDeliveryService;

    public function __construct(
        Manzana $manzana,
        CouponStorageInterface $couponStorage,
        AppBasketService $appBasketService,
        ApiBasketService $apiBasketService,
        ApiOrderService $apiOrderService,
        AppStoreService $appStoreService,
        AppDeliveryService $appDeliveryService
    )
    {
        $this->manzana = $manzana;
        $this->couponStorage = $couponStorage;
        $this->appBasketService = $appBasketService;
        $this->apiBasketService = $apiBasketService;
        $this->apiOrderService = $apiOrderService;
        $this->appStoreService = $appStoreService;
        $this->appDeliveryService = $appDeliveryService;
    }

    /**
     * @Rest\Get("/user_cart/")
     * @Rest\View(serializerGroups={"Default", "basket"})
     *
     * @param UserCartRequest $userCartRequest
     * @return UserCartResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    public function getUserCartAction(UserCartRequest $userCartRequest)
    {
        if ($promoCode = $userCartRequest->getPromoCode()) {
            try {
                /** @see \FourPaws\SaleBundle\AjaxController\BasketController::applyPromoCodeAction */
                $this->manzana->setPromocode($promoCode);
                $this->manzana->calculate();


                $this->couponStorage->clear();
                $this->couponStorage->save($promoCode);
            } catch (ManzanaPromocodeUnavailableException $e) {
                $promoCode = '';
            }
        }
        $basketProducts = $this->apiBasketService->getBasketProducts();
        $orderParameter = $this->apiOrderService->getOrderParameter($basketProducts);
        $orderCalculate = $this->apiOrderService->getOrderCalculate($basketProducts);
        if ($promoCode) {
            $orderCalculate->setPromoCodeResult($promoCode);
        }
        return (new UserCartResponse())
            ->setCartCalc($orderCalculate)
            ->setCartParam($orderParameter);
    }


    /**
     * Добавление товаров в корзину (принимает массив id товаров и количество каждого товара)
     * @Rest\Post(path="/user_cart/")
     * @Rest\View(serializerGroups={"Default", "basket"})
     * @param PostUserCartRequest $postUserCartRequest
     * @return UserCartResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     */
    public function postUserCartAction(PostUserCartRequest $postUserCartRequest)
    {
        $gifts = [];
        foreach ($postUserCartRequest->getGoods() as $productQuantity) {
            if ($productQuantity->getDiscountId()) {
                // gift
                $gifts[] = [
                    'offerId' =>  $productQuantity->getProductId(),
                    'actionId' => $productQuantity->getDiscountId(),
                    'count' => $productQuantity->getQuantity(),
                ];
            } else {
                // regular product
                $this->appBasketService->addOfferToBasket(
                    $productQuantity->getProductId(),
                    $productQuantity->getQuantity()
                );
            }
        }
        if (!empty($gifts)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->appBasketService->getAdder('gift')->selectGifts($gifts);
        }
        return $this->getUserCartAction(new UserCartRequest());
    }

    /**
     * обновление количества товаров в корзине, 0 - удаление (принимает id товара из корзины (basketItemId) и количество)
     * @Rest\Put(path="/user_cart/")
     * @Rest\View(serializerGroups={"Default", "basket"})
     * @param PutUserCartRequest $putUserCartRequest
     * @return UserCartResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     */
    public function putUserCartAction(PutUserCartRequest $putUserCartRequest)
    {
        foreach ($putUserCartRequest->getGoods() as $productQuantity) {
            $quantity = $productQuantity->getQuantity();
            try {
                if ($quantity > 0) {
                    $this->appBasketService->updateBasketQuantity($productQuantity->getProductId(), $productQuantity->getQuantity());
                } else {
                    $this->appBasketService->deleteOfferFromBasket($productQuantity->getProductId());
                }
            }
            catch (\FourPaws\SaleBundle\Exception\NotFoundException $e) {
                throw new RuntimeException('Товар не найден');
            }
        }
        return $this->getUserCartAction(new UserCartRequest());
    }

    /**
     * @Rest\Get(path="/user_cart_delivery/")
     * @Rest\View()
     * @return Response
     */
    public function getUserCartDeliveryAction()
    {
        return new Response(
            $this->apiOrderService->getDeliveryDetails()
        );
    }

    /**
     * Метод рассчитывает корзину.
     * @Rest\Post(path="/user_cart_calc/")
     * @Rest\View()
     * @param UserCartCalcRequest $userCartCalcRequest
     * @return UserCartCalcResponse
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function postUserCartCalcAction(UserCartCalcRequest $userCartCalcRequest)
    {
        $bonusSubtractAmount = $userCartCalcRequest->getBonusSubtractAmount();
        $deliveryType = $userCartCalcRequest->getDeliveryType();
        $basketProducts = $this->apiBasketService->getBasketProducts();

        $orderCalculate = $this->apiOrderService->getOrderCalculate(
            $basketProducts,
            $deliveryType,
            $bonusSubtractAmount
        );
        return (new UserCartCalcResponse())
            ->setCartCalc($orderCalculate);
    }

    /**
     * Оформление корзины / оформить заказ
     * @Rest\Post(path="/user_cart_order/")
     * @Rest\View()
     * @param UserCartOrderRequest $userCartOrderRequest
     * @return UserCartOrderResponse
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Sale\UserMessageException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\SaleBundle\Exception\DeliveryNotAvailableException
     * @throws \FourPaws\SaleBundle\Exception\OrderCreateException
     * @throws \FourPaws\SaleBundle\Exception\OrderSplitException
     * @throws \FourPaws\SaleBundle\Exception\OrderStorageSaveException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function postUserCartOrderAction(UserCartOrderRequest $userCartOrderRequest)
    {
        $cartOrder = $this->apiOrderService->createOrder($userCartOrderRequest);
        return (new UserCartOrderResponse())
            ->setCartOrder($cartOrder);
    }
}
