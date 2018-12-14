<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Bitrix\Sale\BasketItem;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\MobileApiBundle\Dto\Object\OrderCalculate;
use FourPaws\MobileApiBundle\Dto\Object\OrderParameter;
use FourPaws\MobileApiBundle\Dto\Request\UserCartRequest;
use FourPaws\MobileApiBundle\Dto\Response\UserCartResponse;
use FourPaws\SaleBundle\Service\BasketService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FourPaws\MobileApiBundle\Dto\Object\Price;

/**
 * Class UserCartController
 * @package FourPaws\MobileApiBundle\Controller\v0
 */
class UserCartController extends FOSRestController
{
    /**
     * @var BasketService
     */
    private $basketService;

    public function __construct(BasketService $basketService)
    {
        $this->basketService = $basketService;
    }

    /**
     * @Rest\Get("/user_cart/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @throws \Bitrix\Main\SystemException
     */
    public function getUserCartAction(UserCartRequest $userCartRequest)
    {
        $totalBasePrice = 0;
        $totalPrice = 0;
        $products = [];
        $basket = $this->basketService->getBasket();
        /** @var BasketItem $basketItem */
        $orderableBasket = $basket->getOrderableItems();
        /**
         * @var $basketItem BasketItem
         */
        foreach ($orderableBasket as $basketItem) {

            $offer = OfferQuery::getById($basketItem->getProductId());
            $productOriginal = $offer->getProduct();
            $picture = $offer->getImages()->first();
            $picturePreview = $offer->getResizeImages(200, 250)->first();

            $price = (new Price())
                ->setActual($offer->getPrice())
                ->setOld($offer->getOldPrice());

            $tags = [];
            if ($offer->isHit()) {
                $tags[] = (new ShortProduct\Tag())->setImg(MarkHelper::MARK_HIT_IMAGE_SRC);
            }
            if ($offer->isNew()) {
                $tags[] = (new ShortProduct\Tag())->setImg(MarkHelper::MARK_NEW_IMAGE_SRC);
            }
            if ($offer->isSale()) {
                $tags[] = (new ShortProduct\Tag())->setImg(MarkHelper::MARK_SALE_IMAGE_SRC);
            }

            $shortProduct = (new ShortProduct())
                ->setTitle($productOriginal->getName())
                ->setWebPage($productOriginal->getCanonicalPageUrl())
                ->setXmlId($productOriginal->getXmlId())
                ->setBrandName($productOriginal->getBrandName())
                ->setPicture($picture)
                ->setPicturePreview($picturePreview)
                ->setPrice($price)
                ->setInPack($offer->getMultiplicity())
                ->setTag($tags);


            $products[] = (new Product())
                ->setShortProduct($shortProduct)
                ->setQuantity($basketItem->getQuantity());

            $itemQuantity = (int)$basketItem->getQuantity();
            //если не подарок
            if (!isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                $totalBasePrice += (float)$offer->getOldPrice() * $itemQuantity;
                $totalPrice += (float)$offer->getPrice() * $itemQuantity;
                // Слияние строчек с одинаковыми sku
                // $offer = $this->getOffer((int)$basketItem->getProductId());
                // if (!$offer || $offer->isByRequest()) {
                //    continue;
                // }
            }

            //toDo подсчет бонусов для товара
        }

        $orderParameter = (new OrderParameter())
            ->setProducts($products);

        $totalPrice = (new Price())
            ->setOld($totalBasePrice)
            ->setActual($totalPrice);

        $orderCalculate = (new OrderCalculate())->setTotalPrice($totalPrice);

        if ($promoCode = $userCartRequest->getPromoCode()) {

            $promoCodeResult = false;
            // toDo добавление промокода в купоны
            // $promoCodeResult = MyCAjax::AddCouponAPI($promoCode, $this->getFuserId(), $this->getUserId());

            if ($promoCodeResult) {
                $orderCalculate->setPromoCodeResult($promoCode);
            }
        }

        return (new UserCartResponse())
            ->setCartCalc($orderCalculate)
            ->setCartParam($orderParameter);
    }

}
