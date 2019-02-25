<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;


use Bitrix\Sale\BasketItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResult;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\MobileApiBundle\Collection\BasketProductCollection;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\SaleBundle\Service\BasketService as AppBasketService;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


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
     * @var ApiUserSessionRepository
     */
    private $apiUserSessionRepository;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /** @var DeliveryService */
    private $deliveryService;

    public function __construct(
        AppBasketService $appBasketService,
        ApiProductService $apiProductService,
        ApiUserSessionRepository $apiUserSessionRepository,
        TokenStorageInterface $tokenStorage,
        DeliveryService $deliveryService
    )
    {
        $this->appBasketService = $appBasketService;
        $this->apiProductService = $apiProductService;
        $this->apiUserSessionRepository = $apiUserSessionRepository;
        $this->tokenStorage = $tokenStorage;
        $this->deliveryService = $deliveryService;
    }


    /**
     * @return BasketProductCollection
     * @throws \Bitrix\Main\ArgumentException
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

        $basket = $this->appBasketService->getBasket();
        $products = [];
        foreach ($basket->getOrderableItems() as $basketItem) {

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
            $product->setShortProduct($shortProduct);
            $products[] = $product;
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
