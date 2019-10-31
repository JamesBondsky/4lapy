<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\EntityCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Components\BasketComponent;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\MobileApiBundle\Collection\BasketProductCollection;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct\StampLevel;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\MobileApiBundle\Dto\Object\PriceWithQuantity;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SaleBundle\Service\BasketService as AppBasketService;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FourPaws\UserBundle\Service\UserService as AppUserService;
use Bitrix\Main\Application as BitrixApplication;

class BasketService
{
    /**
     * @var AppBasketService
     */
    private $appBasketService;
    
    /**
     * @var ApiProductService;
     */
    private $apiProductService;
    
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    
    /** @var DeliveryService */
    private $deliveryService;
    
    /** @var AppUserService */
    private $appUserService;
    
    /** @var OrderSubscribeService */
    private $orderSubscribeService;
    
    /** @var StampService */
    private $stampService;
    
    public function __construct(
        AppBasketService $appBasketService,
        ApiProductService $apiProductService,
        TokenStorageInterface $tokenStorage,
        DeliveryService $deliveryService,
        AppUserService $appUserService,
        OrderSubscribeService $orderSubscribeService,
        StampService $stampService
    ) {
        $this->appBasketService      = $appBasketService;
        $this->apiProductService     = $apiProductService;
        $this->tokenStorage          = $tokenStorage;
        $this->deliveryService       = $deliveryService;
        $this->appUserService        = $appUserService;
        $this->orderSubscribeService = $orderSubscribeService;
        $this->stampService          = $stampService;
    }
    
    
    /**
     * @param bool $onlyOrderable флаг запрашивать ли товары доступные для покупки или все товары (в том числе и недоступные для покупки)
     * @return BasketProductCollection
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws LoaderException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws BitrixProxyException
     */
    public function getBasketProducts(bool $onlyOrderable = false, $promoCode = ''): BasketProductCollection
    {
        $this->sqlHeartbeat();
        $deliveries = $this->deliveryService->getByLocation();
        $delivery   = null;
        foreach ($deliveries as $calculationResult) {
            if ($this->deliveryService->isDelivery($calculationResult)) {
                $delivery = $calculationResult;
                break;
            }
        }
        
        $fUserId = $this->appUserService->getCurrentFUserId() ?: 0;
        $basket  = $this->appBasketService->getBasket(true, $fUserId);
        
        /**
         * Непонятный код для того чтобы корреткно работали подарки (бесплатные товары) в рамках акций "берешь n товаров, 1 бесплатно"
         * @see BasketComponent::executeComponent()
         */
        if (null === $order = $basket->getOrder()) {
            try {
                $userId = $this->appUserService->getCurrentUserId();
            } /** @noinspection BadExceptionsProcessingInspection */
            catch (NotAuthorizedException $e) {
                $userId = null;
            }
            
            if ($userId && count($basket->getOrderableItems()) > 0) {
                $user                  = $this->appUserService->getCurrentUser();
                $needAddDobrolapMagnet = $user->getGiftDobrolap();
                /** Если пользователю должны магнит */
                if ($needAddDobrolapMagnet == BaseEntity::BITRIX_TRUE || $needAddDobrolapMagnet == true || $needAddDobrolapMagnet == 1) {
                    $magnetID = $this->appBasketService->getDobrolapMagnet()['ID'];
                    /** если магнит найден как оффер */
                    if ($magnetID) {
                        $basketItem = $this->appBasketService->addOfferToBasket(
                            (int)$magnetID,
                            1,
                            [],
                            true,
                            $basket
                        );
                        $result     = $basket->save();
                        /** если магнит успешно добавлен в корзину */
                        if ($basketItem->getId() && $result->isSuccess()) {
                            $userDB = new \CUser;
                            $fields = [
                                'UF_GIFT_DOBROLAP' => false,
                            ];
                            $userDB->Update($userId, $fields);
                        }
                    }
                }
            }
            
            $this->sqlHeartbeat();
            $order = \Bitrix\Sale\Order::create(SITE_ID, $userId);
            
            $this->sqlHeartbeat();
            $order->setBasket($basket);
            
            $this->sqlHeartbeat();
            // но иногда он так просто не запускается
            if (!\FourPaws\SaleBundle\Discount\Utils\Manager::isExtendCalculated()) {
                $order->doFinalAction(true);
                $this->sqlHeartbeat();
            }
        }
        
        $products    = new BasketProductCollection();
        $basketItems = $onlyOrderable ? $basket->getOrderableItems()->getBasketItems() : $basket->getBasketItems();
        // В этом массиве будут храниться детализация цены для каждого товара в случае акций "берешь n товаров, 1 бесплатно", "50% скидка на второй товар" и т.д.
        
        foreach ($basketItems as $basketItem) {
            $offer = OfferQuery::getById($basketItem->getProductId());
            
            if (!$offer) {
                continue;
            }
            
            if ($this->isSubProduct($basketItem) && !in_array($offer->getXmlId(), [AppBasketService::GIFT_DOBROLAP_XML_ID, AppBasketService::GIFT_DOBROLAP_XML_ID_ALT], true)) {
                continue;
            }
            
            /** @var $basketItem BasketItem */
            $useStamps          = false;
            $canUseStamps       = false;
            $canUseStampsAmount = 0;
            if ($this->stampService::IS_STAMPS_OFFER_ACTIVE) {
                if (isset($basketItem->getPropertyCollection()->getPropertyValues()['USE_STAMPS'])) {
                    $useStamps = (bool)$basketItem->getPropertyCollection()->getPropertyValues()['USE_STAMPS']['VALUE'];
                }
                
                if ($useStamps) {
                    $canUseStamps = true;
                }
                
                if (isset($basketItem->getPropertyCollection()->getPropertyValues()['MAX_STAMPS_LEVEL'])) {
                    $maxStampsLevelValue = $basketItem->getPropertyCollection()->getPropertyValues()['MAX_STAMPS_LEVEL']['VALUE'];
                    $canUseStamps        = ((bool)$maxStampsLevelValue || $canUseStamps);
                    
                    if ($useStamps) {
                        if ($usedStamps = unserialize($basketItem->getPropertyCollection()->getPropertyValues()['USED_STAMPS_LEVEL']['VALUE'])['stampsUsed']) {
                            $canUseStampsAmount = $usedStamps;
                        }
                    } else {
                        $canUseStampsObj       = unserialize($maxStampsLevelValue);
                        $canUseStampsAmountKey = $canUseStampsObj ? $canUseStampsObj['key'] : false;
                        if ($canUseStampsAmountKey) {
                            $discount           = $this->stampService->parseLevelKey($canUseStampsAmountKey);
                            $canUseStampsAmount = $discount['discountStamps'] * $canUseStampsObj['value'];
                        }
                    }
                }
            }
            
            $product      = $this->getBasketProduct($basketItem->getId(), $offer, $basketItem->getQuantity(), $useStamps, $canUseStamps, $canUseStampsAmount);
            $shortProduct = $product->getShortProduct();
            
            if (!$shortProduct) {
                continue;
            }
            
            $shortProduct->setPickupOnly(
                $this->isPickupOnly($basketItem, $delivery, $offer)
            );
            if (isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                $shortProduct->setGiftDiscountId($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT']['VALUE']);
                $shortProduct->setPrice((new Price())->setActual(0)->setOld(0));
            }
            
            if ($this->stampService::IS_STAMPS_OFFER_ACTIVE) {
                if (isset($basketItem->getPropertyCollection()->getPropertyValues()['USED_STAMPS_LEVEL'])) {
                    $usedStampsLevel = unserialize($basketItem->getPropertyCollection()->getPropertyValues()['USED_STAMPS_LEVEL']['VALUE']);
                    if ($usedStampsLevel) {
                        $shortProduct->setUsedStamps((int)$usedStampsLevel['stampsUsed']);
                    }
                }
                
                // уровни скидок за марки
                $serializer             = Application::getInstance()->getContainer()->get(SerializerInterface::class);
                $maxStampsLevelDiscount = 0;
                
                $maxStampsLevelKey = unserialize($basketItem->getPropertyCollection()->getPropertyValues()['MAX_STAMPS_LEVEL']['VALUE'])['key'];
                if ($maxStampsLevelKey) {
                    $maxStampsLevelDiscount = $this->stampService->parseLevelKey($maxStampsLevelKey)['discountStamps'];
                }
                
                $stampLevels = [];
                
                if ($canUseStamps) {
                    foreach ($this->stampService->getBasketItemStampLevels($basketItem, $offer->getXmlId(), $maxStampsLevelDiscount) as $stampLevel) {
                        $stampLevels[] = $serializer->fromArray($stampLevel, StampLevel::class);
                    }
                }
                
                /** @var StampLevel $stampLevelObj */
                foreach ($stampLevels as $stampLevelKey => $stampLevelObj) {
                    $oldShortProduct = $product->getShortProduct();
                    if ($oldShortProduct) {
                        $oldPrice = $oldShortProduct->getPrice()->getActual();
                        if ($stampLevelObj->getDiscountType() === $this->stampService::DISCOUNT_TYPE_PERCENTAGE) {
                            $stampLevels[$stampLevelKey]->setPrice($oldPrice * (1 - $stampLevelObj->getDiscountValue() / 100));
                        } elseif ($stampLevelObj->getDiscountType() === $this->stampService::DISCOUNT_TYPE_VALUE) {
                            $stampLevels[$stampLevelKey]->setPrice($oldPrice - $stampLevelObj->getDiscountValue());
                        }
                    }
                }
                //FIXME можно убрать лишние параметры из $stampLevels (discountValue и discountType)
                $shortProduct->setStampLevels($stampLevels); //TODO get stampLevels from Manzana. If Manzana doesn't answer then set no levels
            }
            
            $product->setShortProduct($shortProduct);
            $products->add($product);
            
        }
        
        $products = $this->fillBasketProductsPrices($basketItems, $products);
    
        if ($promoCode) {
            $couponStorage = Application::getInstance()->getContainer()->get(CouponStorageInterface::class);
            $couponStorage->save($promoCode);
        }
        
        return $products;
    }
    
    
    /**
     * Фильтруем товары в рамках акций n+1, 50% за второй товар и т.д.
     * Если basketCode = n1, n2 ... nX - значит это акционный товар например в рамках акции "берешь n товаров, 1 бесплатно" (sic!)
     * по сути является подпродуктом базового продукта
     * @param array|EntityCollection  $basketItems
     * @param BasketProductCollection $products
     * @return BasketProductCollection
     * @see BasketComponent::calcTemplateFields()
     *
     */
    private function fillBasketProductsPrices($basketItems, $products)
    {
        /** @var PriceWithQuantity[][] $pricesWithQuantityAll */
        $pricesWithQuantityAll = [];
        foreach ($products as $product) {
            /** @var Product $product */
            if ($isGift = ($product->getShortProduct()->getGiftDiscountId() > 0)) {
                continue;
            }
            /** @var BasketItem $basketItem */
            foreach ($basketItems as $basketItem) {
                if (
                    (int)$product->getShortProduct()->getId() === (int)$basketItem->getProductId()
                    && !isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])
                ) {
                    $pricesWithQuantityAll[$product->getBasketItemId()][] = (new PriceWithQuantity())
                        ->setPrice(
                            (new Price)
                                ->setActual($basketItem->getPrice())
                                ->setOld($basketItem->getBasePrice())
                                ->setSubscribe($this->orderSubscribeService->getSubscribePriceByBasketItem($basketItem))
                        )
                        ->setQuantity($basketItem->getQuantity());
                }
            }
        }
        
        return $products->map(function ($product) use ($pricesWithQuantityAll) {
            /** @var Product $product */
            if (array_key_exists($product->getBasketItemId(), $pricesWithQuantityAll)) {
                $pricesWithQuantity = $pricesWithQuantityAll[$product->getBasketItemId()];
                $totalQuantity      = 0;
                foreach ($pricesWithQuantity as $priceWithQuantity) {
                    $totalQuantity += $priceWithQuantity->getQuantity();
                }
                $product->setQuantity($totalQuantity);
                $product->setPrices($pricesWithQuantity);
            }
            return $product;
        });
    }
    
    /**
     * @param int       $basketItemId
     * @param Offer     $offer
     * @param int       $quantity
     * @param bool|null $useStamps
     * @param bool|null $canUseStamps
     * @param int|null  $canUseStampsAmount
     * @return Product
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    public function getBasketProduct(int $basketItemId, Offer $offer, int $quantity, ?bool $useStamps = false, ?bool $canUseStamps = false, ?int $canUseStampsAmount = 0)
    {
        $product      = $offer->getProduct();
        $shortProduct = $this->apiProductService->convertToShortProduct($product, $offer, $quantity);
        
        return (new Product())
            ->setBasketItemId($basketItemId)
            ->setShortProduct($shortProduct)
            ->setQuantity($quantity)
            ->setUseStamps($useStamps)
            ->setCanUseStamps($canUseStamps)
            ->setCanUseStampsAmount($canUseStampsAmount);
    }
    
    /**
     * @param BasketItem                 $basketItem
     * @param CalculationResultInterface $delivery
     * @param Offer                      $offer
     * @return bool
     * @throws ArgumentException
     * @throws ApplicationCreateException
     */
    protected function isPickupOnly(BasketItem $basketItem, CalculationResultInterface $delivery, Offer $offer)
    {
        try {
            if (!$basketItem->isDelay()) {
                if (
                    ($basketItem->getPrice() > 0 || $basketItem->getBasePrice() > 0)
                    && (
                        (null === $delivery)
                        || !(clone $delivery)->setStockResult(
                            $this->deliveryService->getStockResultForOffer(
                                $offer,
                                $delivery,
                                (int)$basketItem->getQuantity(),
                                $basketItem->getPrice()
                            )
                        )->isSuccess()
                    )
                ) {
                    return true;
                }
            }
        } catch (\FourPaws\DeliveryBundle\Exception\NotFoundException $e) {
            // do nothing
        } catch (\FourPaws\StoreBundle\Exception\NotFoundException $e) {
            // do nothing
        }
        return false;
    }
    
    /**
     * @param BasketItem $basketItem
     * @return bool
     */
    private function isSubProduct(BasketItem $basketItem): bool
    {
        return strpos($basketItem->getBasketCode(), 'n') === 0;
    }
    
    /**
     * @throws SqlQueryException
     */
    private function sqlHeartbeat()
    {
        BitrixApplication::getConnection()->queryExecute("SELECT CURRENT_TIMESTAMP");
    }
    
}
