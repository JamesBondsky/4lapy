<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;


use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Components\BasketComponent;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\MobileApiBundle\Collection\BasketProductCollection;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\MobileApiBundle\Dto\Object\PriceWithQuantity;
use FourPaws\SaleBundle\Service\BasketService as AppBasketService;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FourPaws\UserBundle\Service\UserService as AppUserService;

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

    public function __construct(
        AppBasketService $appBasketService,
        ApiProductService $apiProductService,
        TokenStorageInterface $tokenStorage,
        DeliveryService $deliveryService,
        AppUserService $appUserService
    )
    {
        $this->appBasketService = $appBasketService;
        $this->apiProductService = $apiProductService;
        $this->tokenStorage = $tokenStorage;
        $this->deliveryService = $deliveryService;
        $this->appUserService = $appUserService;
    }


    /**
     * @return BasketProductCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getBasketProducts(): BasketProductCollection
    {
        $deliveries = $this->deliveryService->getByLocation();
        $delivery = null;
        foreach ($deliveries as $calculationResult) {
            if ($this->deliveryService->isDelivery($calculationResult)) {
                $delivery = $calculationResult;
                break;
            }
        }

        $basket = $this->appBasketService->getBasket(true);

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

            $order =  \Bitrix\Sale\Order::create(SITE_ID, $userId);
            $order->setBasket($basket);
            // но иногда он так просто не запускается
            if (!\FourPaws\SaleBundle\Discount\Utils\Manager::isExtendCalculated()) {
                $order->doFinalAction(true);
            }
        }

        $products = new BasketProductCollection();
        $orderAbleBasket = $basket->getOrderableItems();
        // В этом массиве будут храниться детализация цены для каждого товара в случае акций "берешь n товаров, 1 бесплатно", "50% скидка на второй товар" и т.д.

        foreach ($orderAbleBasket as $basketItem) {
            if ($this->isSubProduct($basketItem)) {
                continue;
            }

            /** @var $basketItem BasketItem */
            $offer = OfferQuery::getById($basketItem->getProductId());
            $product = $this->getBasketProduct($basketItem->getId(), $offer, $basketItem->getQuantity());
            $shortProduct = $product->getShortProduct();
            $shortProduct->setPickupOnly(
                $this->isPickupOnly($basketItem, $delivery, $offer)
            );
            if (isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                $shortProduct->setGiftDiscountId($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT']['VALUE']);
                $shortProduct->setPrice((new Price())->setActual(0)->setOld(0));
            }

            $product->setShortProduct($shortProduct);
            $products->add($product);

        }

        $products = $this->fillBasketProductsPrices($orderAbleBasket, $products);

        return $products;
    }


    /**
     * Фильтруем товары в рамках акций n+1, 50% за второй товар и т.д.
     * Если basketCode = n1, n2 ... nX - значит это акционный товар например в рамках акции "берешь n товаров, 1 бесплатно" (sic!)
     * по сути является подпродуктом базового продукта
     * @see BasketComponent::calcTemplateFields()
     *
     * @param Basket $orderAbleBasket
     * @param BasketProductCollection $products
     * @return BasketProductCollection
     */
    private function fillBasketProductsPrices(Basket $orderAbleBasket, BasketProductCollection $products)
    {
        /** @var PriceWithQuantity[][] $pricesWithQuantityAll */
        $pricesWithQuantityAll = [];
        foreach ($products as $product) {
            /** @var Product $product */
            if ($isGift = $product->getShortProduct()->getGiftDiscountId() > 0) {
                continue;
            }
            /** @var BasketItem $basketItem */
            foreach ($orderAbleBasket as $basketItem) {
                if (
                    (int)$product->getShortProduct()->getId() === (int)$basketItem->getProductId()
                    &&
                    !isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])
                ) {
                    $pricesWithQuantityAll[$product->getBasketItemId()][] = (new PriceWithQuantity())
                        ->setPrice(
                            (new Price)
                                ->setActual($basketItem->getPrice())
                                ->setOld($basketItem->getBasePrice())
                        )
                        ->setQuantity($basketItem->getQuantity())
                    ;
                }
            }
        }

        return $products->map(function ($product) use ($pricesWithQuantityAll) {
            /** @var Product $product */
            if (array_key_exists($product->getBasketItemId(), $pricesWithQuantityAll)) {
                $pricesWithQuantity = $pricesWithQuantityAll[$product->getBasketItemId()];
                $totalQuantity = 0;
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
     * @param int $basketItemId
     * @param Offer $offer
     * @param int $quantity
     * @return Product
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getBasketProduct(int $basketItemId, Offer $offer, int $quantity)
    {
        $product = $offer->getProduct();
        $shortProduct = $this->apiProductService->convertToShortProduct($product, $offer, $quantity, true);

        return (new Product())
            ->setBasketItemId($basketItemId)
            ->setShortProduct($shortProduct)
            ->setQuantity($quantity);
    }

    /**
     * @param BasketItem $basketItem
     * @param CalculationResultInterface $delivery
     * @param Offer $offer
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function isPickupOnly(BasketItem $basketItem, CalculationResultInterface $delivery, Offer $offer)
    {
        try {
            if (!$basketItem->isDelay()) {
                if ($basketItem->getPrice() && (
                        (null === $delivery) ||
                        !(clone $delivery)->setStockResult(
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
}
