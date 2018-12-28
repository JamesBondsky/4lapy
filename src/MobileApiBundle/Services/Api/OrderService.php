<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;

class OrderService
{

    /**
     * @var ApiProductService;
     */
    private $apiProductService;

    public function __construct(ApiProductService $apiProductService)
    {
        $this->apiProductService = $apiProductService;
    }

    /**
     * @param $orderItems ArrayCollection
     * @return Product[]
     * @throws \Bitrix\Main\SystemException
     */
    public function getProducts($orderItems)
    {
        $products = [];
        foreach ($orderItems as $orderItem) {
            /**
             * @var $orderItem OrderItem
             */
            $products[] = $this->getProduct($orderItem->getId(), $orderItem->getProductId(), $orderItem->getQuantity());
        }
        return $products;
    }

    /**
     * @param $basketItemId int
     * @param $offerId int
     * @param $quantity int
     * @return Product
     * @throws \Bitrix\Main\SystemException
     */
    public function getProduct($basketItemId, $offerId, $quantity)
    {
        $offer =  OfferQuery::getById($offerId);

        $product = $offer->getProduct();
        $shortProduct = $this->apiProductService->convertToShortProduct($product, $offer, $quantity);

        return (new Product())
            ->setBasketItemId($basketItemId)
            ->setShortProduct($shortProduct)
            ->setQuantity($quantity);
    }

    /**
     * @param $products Product[]
     * @return float|int
     */
    public function calculateProductsPrice(array $products)
    {
        $price = 0;
        foreach ($products as $product) {
            $price += $product->getQuantity() * $product->getShortProduct()->getPrice()->getOld();
        }
        return $price;
    }

    /**
     * @param $products Product[]
     * @return float|int
     */
    public function calculateProductsDiscountPrice(array $products)
    {
        $discountPrice = 0;
        foreach ($products as $product) {
            $discountPrice += $product->getQuantity() * $product->getShortProduct()->getPrice()->getActual();
        }
        return $discountPrice;
    }

}
