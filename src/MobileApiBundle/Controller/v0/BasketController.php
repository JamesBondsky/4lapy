<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\ElementTable;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Exception\ManzanaPromocodeUnavailableException;
use FourPaws\KkmBundle\Service\KkmService;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\Coupon;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress;
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
use FourPaws\MobileApiBundle\Dto\Response\UserCouponsResponse;
use FourPaws\MobileApiBundle\Dto\Request\UserCartRequest;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\MobileApiBundle\Services\Api\CityService;
use FourPaws\MobileApiBundle\Services\Api\OrderService as ApiOrderService;
use FourPaws\MobileApiBundle\Services\Api\UserDeliveryAddressService as ApiUserDeliveryAddressService;
use FourPaws\MobileApiBundle\Traits\MobileApiLoggerAwareTrait;
use FourPaws\PersonalBundle\Service\CouponService;
use FourPaws\SaleBundle\Dto\OrderSplit\Basket\BasketSplitItem;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SaleBundle\Service\BasketService as AppBasketService;
use FourPaws\SaleBundle\Discount\Manzana;
use FourPaws\MobileApiBundle\Services\Api\BasketService as ApiBasketService;
use FourPaws\SaleBundle\Service\OrderSplitService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\StoreBundle\Service\StoreService as AppStoreService;
use FourPaws\DeliveryBundle\Service\DeliveryService as AppDeliveryService;
use FourPaws\UserBundle\Enum\UserLocationEnum;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\SaleBundle\Enum\OrderStorage as OrderStorageEnum;
use FourPaws\UserBundle\Service\UserService as AppUserService;

/**
 * Class BasketController
 * @package FourPaws\MobileApiBundle\Controller
 */
class BasketController extends BaseController
{
    use MobileApiLoggerAwareTrait;
    
    /** @var AppBasketService */
    private $appBasketService;
    
    /** @var ApiBasketService */
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
    
    /** @var AppUserService */
    private $appUserService;
    
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
        ApiUserDeliveryAddressService $apiUserDeliveryAddressService,
        AppUserService $appUserService
    ) {
        $this->manzana                       = $manzana;
        $this->appBasketService              = $appBasketService;
        $this->apiBasketService              = $apiBasketService;
        $this->apiOrderService               = $apiOrderService;
        $this->appStoreService               = $appStoreService;
        $this->appDeliveryService            = $appDeliveryService;
        $this->orderStorageService           = $orderStorageService;
        $this->apiUserDeliveryAddressService = $apiUserDeliveryAddressService;
        $this->appUserService        = $appUserService;
        $this->setLogger(LoggerFactory::create('BasketController', 'mobileApi'));
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
        $promocodeForOldSupport = '';
        
        $couponStorage = Application::getInstance()->getContainer()->get(CouponStorageInterface::class);
        
        $storage = $this->orderStorageService->getStorage();
        
        $promoCode = $userCartRequest->getPromoCode();
        
        if (!$promoCode) {
            $promoCode              = $couponStorage->getApplicableCoupon() ?: $storage->getPromoCode();
            $promocodeForOldSupport = $promoCode;
        }
        
        if ($promoCode) {
            try {
                /** @see \FourPaws\SaleBundle\AjaxController\BasketController::applyPromoCodeAction */
                $this->manzana->setPromocode($promoCode);
                $this->manzana->calculate();
                
                $storage->setPromoCode($promoCode);
                $this->orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
            } catch (ManzanaPromocodeUnavailableException $e) {
                $promoCode = '';
            }
        }
        
        $basketProducts = $this->apiBasketService->getBasketProducts(false, $promocodeForOldSupport);
        $orderParameter = $this->apiOrderService->getOrderParameter($basketProducts);
        $orderCalculate = $this->apiOrderService->getOrderCalculate($basketProducts);
        
        if ($storage->getUserId()) {
            $personalOffers = Application::getInstance()->getContainer()->get(PersonalOffersService::class);
            $coupons        = $personalOffers->getActiveUserCoupons($storage->getUserId())['coupons'];
        }

        if ($coupons) {
            $orderCalculate->setHasCoupons(true);
        }
        
        if ($promoCode && $coupons && $promocodeForOldSupport) {
            foreach ($coupons as $coupon) {
                
                if ($promoCode == $coupon['UF_PROMO_CODE']) {
                    $orderCalculate->setCoupon(
                        (new Coupon())->setId($coupon['ID'])
                            ->setPromocode($promoCode)
                            ->setText($coupon['text'])
                            ->setDiscount($coupon['custom_title'])
                            ->setDateActive($coupon['custom_date_to'])
                            ->setActionType(Coupon::DISABLE)
                    );
                }
            }
        }
    
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
                    'offerId'  => $productQuantity->getProductId(),
                    'actionId' => $productQuantity->getDiscountId(),
                    'count'    => $productQuantity->getQuantity(),
                ];
            } else {
                $productXmlId = ElementTable::getByPrimary($productQuantity->getProductId(), ['select' => ['XML_ID']])->fetch()['XML_ID'];
                
                if (!$this->appBasketService->isGiftProductByXmlId($productXmlId, true)) {
                    // regular product
                    $this->appBasketService->addOfferToBasket(
                        $productQuantity->getProductId(),
                        $productQuantity->getQuantity(),
                        [],
                        true,
                        null,
                        true
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
    public function putUserCartAction(PutUserCartRequest $putUserCartRequest
    ) //TODO при указании флага useStamps передать это в Manzana и в зависимости от ответа изменить ответ в запросе (и снять флаг USE_STAMPS у товара в корзине, если манзана ответила, что обмен применить нельзя)
    {
        foreach ($putUserCartRequest->getGoods() as $productQuantity) {
            $quantity = $productQuantity->getQuantity();
            try {
                if ($quantity > 0) {
                    $this->appBasketService->updateBasketQuantity($productQuantity->getProductId(), $productQuantity->getQuantity(), $productQuantity->isUseStamps());
                } else {
                    $this->appBasketService->deleteOfferFromBasket($productQuantity->getProductId());
                }
            } catch (\FourPaws\SaleBundle\Exception\NotFoundException $e) {
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
        $storage = $this->orderStorageService->getStorage();
        $promoCode = $storage->getPromoCode();
    
        if ($promoCode) {
            $couponStorage       = Application::getInstance()->getContainer()->get(CouponStorageInterface::class);
            $couponStorage->clear();
            $couponStorage->save($promoCode);
        }
        
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
        //@todo отрефакторить дублирование
        $storage = $this->orderStorageService->getStorage();
        $promoCode = $storage->getPromoCode();
    
        if ($promoCode) {
            $couponStorage = Application::getInstance()->getContainer()->get(CouponStorageInterface::class);
            $couponStorage->clear();
            $couponStorage->save($promoCode);
        }
        
        $this->mobileApiLog()->info('Request: POST postUserCartCalcAction: ' . print_r($userCartCalcRequest, true));
        if ($userCartCalcRequest->getDeliveryType() === 'courier') {
            $basketProducts = $this->apiOrderService->getBasketWithCurrentDelivery();
        } else {
            $basketProducts = $this->apiBasketService->getBasketProducts(true);
        }
        
        // Если выбрано "Товары из наличия"
        if ($userCartCalcRequest->onlyAvailableGoods) {
            $basketProducts    = $this->apiOrderService->filterPartialBasketItems($basketProducts);
            $orderSplitService = Application::getInstance()
                ->getContainer()
                ->get(OrderSplitService::class);
            
            $items = new ArrayCollection();
            /** @var Product $basketProduct */
            foreach ($basketProducts as $basketProduct) {
                if ($shortProduct = $basketProduct->getShortProduct()) {
                    
                    $price     = $shortProduct->getPrice()->getActual();
                    $oldPrice  = $shortProduct->getPrice()->getOld();
                    $oldPrice  = $oldPrice ?: $price;
                    $splitItem = (new BasketSplitItem())
                        ->setProductId($shortProduct->getId())
                        ->setAmount($basketProduct->getQuantity())
                        ->setPrice($price)
                        ->setBasePrice($oldPrice);
                    $items->add($splitItem);
                }
            }
            
            $this->manzana->calculate(null, $orderSplitService->generateBasket($items));
        }
        
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
        
        [$courierDelivery, $pickupDelivery, $dostavistaDelivery] = $this->apiOrderService->getDeliveryVariants();
        
        $orderCalculate = $this->apiOrderService->getOrderCalculate(
            $basketProducts,
            $userCartCalcRequest->getDeliveryType() === 'courier',
            $bonusSubtractAmount,
            null,
            $userCartCalcRequest->getDeliveryType(),
            $dostavistaDelivery->getPrice()
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
        $city      = $dostavistaRequest->getCity();
        $street    = $dostavistaRequest->getStreet();
        $house     = $dostavistaRequest->getHouse();
        $building  = $dostavistaRequest->getBuilding();
        $addressId = $dostavistaRequest->getAddressId();
        
        $queryAddress = null;
        
        $results = [
            'dostavista' => new DeliveryVariant(),
        ];
        
        $container = Application::getInstance()->getContainer();
        /** @var KkmService $kkmService */
        $kkmService = $container->get('kkm.service');
        
        $cityService = $container->get(CityService::class);
        
        if ($addressId) {
            $user = $this->getUser();
            
            if ($user) {
                /** @var DeliveryAddress $listDelivery */
                $listDelivery = $this->apiUserDeliveryAddressService->getList($user->getId(), $city, $addressId)->first();
                
                if ($listDelivery) {
                    $cityInfo = $listDelivery->getCity();
                    $street   = $listDelivery->getStreetName();
                    $house    = $listDelivery->getHouse();
                }
            }
        }
        
        if (!$queryAddress) {
            if (!$addressId) {
                $cityInfo = $cityService->getCityByCode($city);
            }
            
            $queryAddress = implode(', ', array_filter([$cityInfo ? $cityInfo->getTitle() : '', $street, $house, $building], function ($item) {
                if (!empty($item)) {
                    return $item;
                }
            }));
        }
        
        $_COOKIE[UserLocationEnum::DEFAULT_LOCATION_COOKIE_CODE] = $city;
        
        $address = $kkmService->geocode($queryAddress);
        
        $pos = explode(' ', $address['address']['pos']);
        
        $isMKAD = $this->apiOrderService->isMKAD($pos[1], $pos[0]);
        
        if (!$isMKAD) {
            return new Response($results);
        }
        
        
        $deliveryPrice = 0;
        $deliveries    = $this->orderStorageService->getDeliveries($this->orderStorageService->getStorage());
        foreach ($deliveries as $calculationResult) {
            if (DeliveryService::INNER_DELIVERY_CODE == $calculationResult->getDeliveryCode()) {
                if ($this->appDeliveryService->isDelivery($calculationResult)) {
                    $deliveryPrice = $calculationResult->getPrice();
                }
            }
        }
        
        [$courierDelivery, $pickupDelivery, $dostavistaDelivery, $dobrolapDelivery] = $this->apiOrderService->getDeliveryVariants();
        
        $dostavistaDelivery->setCourierPrice($deliveryPrice);
        
        $results['dostavista'] = $dostavistaDelivery;
        
        return new Response($results);
    }
    
    /**
     * @Rest\Get("/user_coupons/")
     * @Rest\View(serializerGroups={"Default", "basket"})
     *
     * @return UserCouponsResponse
     * @throws \Exception
     */
    public function getUserCouponsAction()
    {
        $couponService = Application::getInstance()->getContainer()->get('coupon.service');
        $result        = $couponService->getUserCouponsAction();
        
        return (new UserCouponsResponse())->setUserCoupons($result);
    }
    
    /**
     * @Rest\Put("/user_cart_coupon/")
     * @Rest\View(serializerGroups={"Default", "basket"})
     *
     * @param Request $request
     * @return UserCouponsResponse
     * @throws \Exception
     */
    public function putUserCartCouponAction(Request $request)
    {
        $promoCode = $request->get('promoCode');
        $use       = $request->get('use');
        
        $storage = $this->orderStorageService->getStorage();
        
        $couponStorage       = Application::getInstance()->getContainer()->get(CouponStorageInterface::class);
        $orderStorageService = Application::getInstance()->getContainer()->get(OrderStorageService::class);
        
        switch ($use) {
            case true:
                $this->manzana->setPromocode($promoCode);
                $this->manzana->calculate();
                
                $storage->setPromoCode($promoCode);
                $this->orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
                $fUserId = $this->appUserService->getCurrentFUserId() ?: 0;
                $this->appBasketService->getBasket(true, $fUserId);
                $couponStorage->clear();
                $couponStorage->save($promoCode);
                break;
            case false:
                $couponStorage->delete($promoCode);
                $storage->setPromoCode('');
                $orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
                break;
        }
        
        $couponService = Application::getInstance()->getContainer()->get('coupon.service');
        $result        = $couponService->getUserCouponsAction();
        
        return (new UserCouponsResponse())->setUserCoupons($result);
    }
}
