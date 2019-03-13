<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;


use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertyItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Components\BasketComponent;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\MobileApiBundle\Collection\BasketProductCollection;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\SaleBundle\Service\BasketService as AppBasketService;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;
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
            $userId = $this->appUserService->getCurrentUserId();
            $order =  \Bitrix\Sale\Order::create(SITE_ID, $userId);
            $order->setBasket($basket);
            // но иногда он так просто не запускается
            if (!\FourPaws\SaleBundle\Discount\Utils\Manager::isExtendCalculated()) {
                $order->doFinalAction(true);
            }
        }

        $products = [];
        $orderAbleBasket = $basket->getOrderableItems();
        // В этом массиве будет сохраняться корректное кол-во товара в случае акции "берешь n товаров, 1 бесплатно"
        $productsAmountFix = [];
        foreach ($orderAbleBasket as $basketItem) {

            /** @var $basketItem BasketItem */
            $offer = OfferQuery::getById($basketItem->getProductId());
            $product = $this->getBasketProduct($basketItem->getId(), $offer, $basketItem->getQuantity());
            $shortProduct = $product->getShortProduct();
            $shortProduct->setPickupOnly(
                $this->isPickupOnly($basketItem, $delivery, $offer)
            );
            if (isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                $shortProduct->setIsGift(true);
                $shortProduct->setPrice((new Price())->setActual(0)->setOld(0));
            }
            /**
             * Ищем бесплатные товары в рамках акции n+1 в массиве с товарами (sic!)
             * @see BasketComponent::calcTemplateFields()
             */
            foreach ($orderAbleBasket as $tBasketItem) {
                if (
                    (int)$basketItem->getProductId() === (int)$tBasketItem->getProductId()
                    &&
                    $basketItem->getBasketCode() !== $tBasketItem->getBasketCode()
                    &&
                    !$productsAmountFix[$tBasketItem->getBasketCode()]
                    &&
                    !isset($tBasketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])
                ) {
                    // не уверен что так правильно делать по логике костыля...
                    $productsAmountFix[$basketItem->getBasketCode()] = [
                        'PRICED' => $tBasketItem->getQuantity(),
                        'FREE' => $basketItem->getQuantity(),
                    ];
                }
            }

            // Если basketCode = n1, n2 ... nX - значит это бесплатный товар в рамках акции "берешь n товаров, 1 бесплатно" (sic!)
            $skipRow = strpos($basketItem->getBasketCode(), "n") === 0;
            if (!$skipRow) {
                $product->setShortProduct($shortProduct);
                $products[] = $product;
            }
        }

        /** @var Product $product */
        foreach ($products as &$product) {
            $shortProduct = $product->getShortProduct();
            if (array_key_exists($product->getBasketItemId(), $productsAmountFix)) {
                $amounts = $productsAmountFix[$product->getBasketItemId()];
                $shortProduct->setFreeGoodsAmount($amounts['FREE']);
                $product->setShortProduct($shortProduct);
                $product->setQuantity($amounts['FREE'] + $amounts['PRICED']);
            }
        }

        return new BasketProductCollection($products);
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
}
