<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\UserBundle\Service\UserService as ApiUserService;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;

class OrderService
{
    /**
     * @var ApiUserService
     */
    private $apiUserService;

    /**
     * @var ApiProductService;
     */
    private $apiProductService;

    public function __construct(ApiUserService $apiUserService, ApiProductService $apiProductService)
    {
        $this->apiUserService = $apiUserService;
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
            $products[] = $this->getProduct($orderItem->getProductId(), $orderItem->getQuantity());

            //toDo подсчет бонусов для товара
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

        $productRaw = $offer->getProduct();
        $picture = $offer->getResizeImages(200, 250)->first();

        $shortProduct = (new ShortProduct())
            ->setId($offer->getId())
            ->setTitle($productRaw->getName())
            ->setWebPage($productRaw->getCanonicalPageUrl())
            ->setXmlId($productRaw->getXmlId())
            ->setBrandName($productRaw->getBrandName())
            ->setPicture($picture)
            ->setInPack($offer->getMultiplicity());

        // цена
        $price = (new Price())
            ->setActual($offer->getPrice())
            ->setOld($offer->getOldPrice());
        $shortProduct->setPrice($price);

        // лейблы
        $shortProduct->setTag($this->apiProductService->getTags($offer));

        $shortProduct->setBonusAll($offer->getBonusCount(3, $quantity));
        $shortProduct->setBonusUser($offer->getBonusCount($this->apiUserService->getDiscount(), $quantity));

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
