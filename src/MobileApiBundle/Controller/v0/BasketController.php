<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Bitrix\Iblock\ElementTable;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Exception\ManzanaPromocodeUnavailableException;
use FourPaws\Helpers\DateHelper;
use FourPaws\KkmBundle\Service\KkmService;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryVariant;
use FourPaws\MobileApiBundle\Dto\Request\DostavistaRequest;
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
use FourPaws\MobileApiBundle\Services\Api\CityService;
use FourPaws\MobileApiBundle\Services\Api\OrderService as ApiOrderService;
use FourPaws\MobileApiBundle\Services\Api\UserDeliveryAddressService;
use FourPaws\MobileApiBundle\Services\Api\UserDeliveryAddressService as ApiUserDeliveryAddressService;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\BasketService as AppBasketService;
use FourPaws\SaleBundle\Discount\Manzana;
use FourPaws\MobileApiBundle\Services\Api\BasketService as ApiBasketService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\StoreBundle\Service\StoreService as AppStoreService;
use FourPaws\DeliveryBundle\Service\DeliveryService as AppDeliveryService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BasketController
 * @package FourPaws\MobileApiBundle\Controller
 */
class BasketController extends FOSRestController
{
    /** @var AppBasketService*/
    private $appBasketService;

    /** @var ApiBasketService*/
    private $apiBasketService;

    /**@var Manzana */
    private $manzana;

    /** @var ApiOrderService */
    private $apiOrderService;

    /** @var AppStoreService */
    private $appStoreService;

    /** @var AppDeliveryService */
    private $appDeliveryService;

    /** @var OrderStorageService */
    private $orderStorageService;

    /**
     * @var ApiUserDeliveryAddressService
     */
    private $apiUserDeliveryAddressService;

    public function __construct(
        Manzana $manzana,
        AppBasketService $appBasketService,
        ApiBasketService $apiBasketService,
        ApiOrderService $apiOrderService,
        AppStoreService $appStoreService,
        AppDeliveryService $appDeliveryService,
        OrderStorageService $orderStorageService,
        ApiUserDeliveryAddressService $apiUserDeliveryAddressService
    )
    {
        $this->manzana = $manzana;
        $this->appBasketService = $appBasketService;
        $this->apiBasketService = $apiBasketService;
        $this->apiOrderService = $apiOrderService;
        $this->appStoreService = $appStoreService;
        $this->appDeliveryService = $appDeliveryService;
        $this->orderStorageService = $orderStorageService;
        $this->apiUserDeliveryAddressService = $apiUserDeliveryAddressService;
    }

    /**
     * @Rest\Get("/user_cart/")
     * @Rest\View(serializerGroups={"Default", "basket"})
     *
     * @param UserCartRequest $userCartRequest
     * @return UserCartResponse
     * @throws \Exception
     */
    public function getUserCartAction(UserCartRequest $userCartRequest)
    {
        $storage = $this->orderStorageService->getStorage();
        $promoCode = $userCartRequest->getPromoCode() ?: $storage->getPromoCode();
        if ($promoCode) {
            try {
                /** @see \FourPaws\SaleBundle\AjaxController\BasketController::applyPromoCodeAction */
                $this->manzana->setPromocode($promoCode);
                $this->manzana->calculate();

                $storage->setPromoCode($promoCode);
                $this->orderStorageService->updateStorage($storage);
            } catch (ManzanaPromocodeUnavailableException $e) {
                $promoCode = '';
            }
        }

        $basketProducts = $this->apiBasketService->getBasketProducts(false);
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
     * @throws \Exception
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
                $productXmlId = ElementTable::getByPrimary($productQuantity->getProductId(), ['select' => ['XML_ID']])->fetch()['XML_ID'];

                if (!$this->appBasketService->isGiftProductByXmlId($productXmlId, true)) {
                    // regular product
                    $this->appBasketService->addOfferToBasket(
                        $productQuantity->getProductId(),
                        $productQuantity->getQuantity()
                    );
                }
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
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function postUserCartCalcAction(UserCartCalcRequest $userCartCalcRequest)
    {
        if ($promoCode = $this->orderStorageService->getStorage()->getPromoCode()) {
            try {
                /** @see \FourPaws\SaleBundle\AjaxController\BasketController::applyPromoCodeAction */
                $this->manzana->setPromocode($promoCode);
                $this->manzana->calculate();
            } catch (ManzanaPromocodeUnavailableException $e) {
                // do nothing
            }
        }

        $bonusSubtractAmount = $userCartCalcRequest->getBonusSubtractAmount();
        $basketProducts = $this->apiBasketService->getBasketProducts(false);

        $orderCalculate = $this->apiOrderService->getOrderCalculate(
            $basketProducts,
            $userCartCalcRequest->getDeliveryType() === 'courier',
            $bonusSubtractAmount,
            null,
            $userCartCalcRequest->getDeliveryType()
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
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postUserCartOrderAction(UserCartOrderRequest $userCartOrderRequest)
    {
        $cartOrder = $this->apiOrderService->createOrder($userCartOrderRequest);
        return (new UserCartOrderResponse())
            ->setCartOrder($cartOrder);
    }

    /**
     * @Rest\Post(path="/delivery_dostavista/")
     * @Rest\View()
     * @param DostavistaRequest $dostavistaRequest
     * @return Response
     * @throws \Exception
     */
    public function getDostavistaAction(DostavistaRequest $dostavistaRequest)
    {
        $city = $dostavistaRequest->getCity();
        $street = $dostavistaRequest->getStreet();
        $house = $dostavistaRequest->getHouse();
        $building = $dostavistaRequest->getBuilding();
        $addressId = $dostavistaRequest->getAddressId();

        $queryAddress = null;

        $results = [
            'dostavista' => new DeliveryVariant()
        ];

        $container = Application::getInstance()->getContainer();
        /** @var KkmService $kkmService */
        $kkmService = $container->get('kkm.service');

        $cityService = $container->get(CityService::class);

        if ($addressId) {
            $user = $this->getUser();

            if ($user) {
                $listDelivery = $this->apiUserDeliveryAddressService->getList($user->getId(), $city, $addressId)->first();

                if ($listDelivery) {
                    $queryAddress = $listDelivery->getTitle();
                }
            }
        }

        if (!$queryAddress) {
            $cityInfo = $cityService->getCityByCode($city);

            $queryAddress = implode(', ', array_filter([$cityInfo->getTitle(), $street, $house, $building], function ($item) {
                if (!empty($item)) {
                    return $item;
                }
            }));
        }

        $address = $kkmService->geocode($queryAddress);

        $pos = explode(' ', $address['address']['pos']);

        $isMKAD = $this->apiOrderService->isMKAD($pos[1], $pos[0]);

        if (!$isMKAD) {
            return new Response($results);
        }

        [$courierDelivery, $pickupDelivery, $dostavistaDelivery] = $this->apiOrderService->getDeliveryVariants();

        $dostavistaDelivery->setCourierPrice($courierDelivery->getPrice());

        $results['dostavista'] = $dostavistaDelivery;

        return new Response($results);
    }
}
