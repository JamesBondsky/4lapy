<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\MobileApiBundle\Dto\Request\PostUserCartRequest;
use FourPaws\MobileApiBundle\Dto\Request\PutUserCartRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserCartCalcRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserCartOrderRequest;
use FourPaws\MobileApiBundle\Dto\Response\UserCartCalcResponse;
use FourPaws\MobileApiBundle\Dto\Response\UserCartOrderResponse;
use FourPaws\MobileApiBundle\Dto\Response\UserCartResponse;
use FourPaws\MobileApiBundle\Dto\Request\UserCartRequest;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Services\Api\OrderService as ApiOrderService;
use FourPaws\SaleBundle\Service\BasketService as AppBasketService;
use FourPaws\StoreBundle\Exception\NotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Rest\View()
     *
     * @param UserCartRequest $userCartRequest
     * @return UserCartResponse
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaPromocodeUnavailableException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     */
    public function getUserCartAction(UserCartRequest $userCartRequest)
    {

        $basketProducts = $this->apiBasketService->getBasketProducts();
        $orderParameter = $this->apiOrderService->getOrderParameter($basketProducts);
        $orderCalculate = $this->apiOrderService->getOrderCalculate($basketProducts);

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
     * Добавление товаров в корзину (принимает массив id товаров и количество каждого товара)
     * @Rest\Post(path="/user_cart/")
     * @Rest\View()
     * @param PostUserCartRequest $postUserCartRequest
     * @return UserCartResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaPromocodeUnavailableException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     */
    public function postUserCartAction(PostUserCartRequest $postUserCartRequest)
    {
        foreach ($postUserCartRequest->getGoods() as $productQuantity) {
            $this->appBasketService->addOfferToBasket(
                $productQuantity->getProductId(),
                $productQuantity->getQuantity()
            );
        }
        return $this->getUserCartAction(new UserCartRequest());
    }

    /**
     * обновление количества товаров в корзине, 0 - удаление (принимает id товара из корзины (basketItemId) и количество)
     * @Rest\Put(path="/user_cart/")
     * @Rest\View()
     * @param PutUserCartRequest $putUserCartRequest
     * @return UserCartResponse
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaPromocodeUnavailableException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
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
     * Метод рассчитывает корзину.
     * @Rest\Post(path="/user_cart_calc/")
     * @Rest\View()
     * @param UserCartCalcRequest $userCartCalcRequest
     * @return UserCartCalcResponse
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function postUserCartCalcAction(UserCartCalcRequest $userCartCalcRequest)
    {
        // если самовывоз
        $storeCode = $userCartCalcRequest->getCartParam()->getPickupPlace();


        if (empty($storeCode)) {
            // если доставка курьером
            $locationCode = $userCartCalcRequest->getCartParam()->getDeliveryPlace()->getCity()->getId();
            // определяем базовый склад для зоны
            $storeCode = $this->appStoreService->getBaseShops($locationCode)->current();
        }

        if (!$storeCode) {
            // central main warehouse
            // toDo it should be a global constant somewhere
            $storeCode = 'DC01';
        }

        $basketProducts = $this->apiBasketService->getBasketProducts();
        $orderCalculate = $this->apiOrderService->getOrderCalculate($basketProducts, $storeCode);
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
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\SaleBundle\Exception\DeliveryNotAvailableException
     * @throws \FourPaws\SaleBundle\Exception\OrderCreateException
     * @throws \FourPaws\SaleBundle\Exception\OrderSplitException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function postUserCartOrderAction(UserCartOrderRequest $userCartOrderRequest)
    {
        switch ($userCartOrderRequest->getCartParam()->getDeliveryType()) {
            case 1:
                $deliveryType = DeliveryService::INNER_DELIVERY_CODE;
                break;
            case 3:
                $deliveryType = DeliveryService::DPD_PICKUP_CODE;
                break;
            case 5:
                $deliveryType = DeliveryService::INNER_PICKUP_CODE;
                break;
        }
        $cartOrder = $this->apiOrderService->createOrder($userCartOrderRequest, $deliveryType);
        return (new UserCartOrderResponse())
            ->setCartOrder($cartOrder);
    }
}
