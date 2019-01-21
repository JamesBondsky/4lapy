<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;


use Bitrix\Sale\BasketItem;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\MobileApiBundle\Collection\BasketProductCollection;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
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



    public function __construct(
        AppBasketService $appBasketService,
        ApiProductService $apiProductService,
        ApiUserSessionRepository $apiUserSessionRepository,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->appBasketService = $appBasketService;
        $this->apiProductService = $apiProductService;
        $this->apiUserSessionRepository = $apiUserSessionRepository;
        $this->tokenStorage = $tokenStorage;
    }


    /**
     * @return BasketProductCollection
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getBasketProducts(): BasketProductCollection
    {
        $basket = $this->appBasketService->getBasket();
        $products = [];
        foreach ($basket->getOrderableItems() as $basketItem) {
            /** @var $basketItem BasketItem */
            $products[] = $this->getBasketProduct($basketItem->getId(), $basketItem->getProductId(), $basketItem->getQuantity());
        }

        return new BasketProductCollection($products);
    }

    /**
     * @param $basketItemId int
     * @param $offerId int
     * @param $quantity int
     * @return Product
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getBasketProduct($basketItemId, $offerId, $quantity)
    {
        $offer = OfferQuery::getById($offerId);

        $product = $offer->getProduct();
        $shortProduct = $this->apiProductService->convertToShortProduct($product, $offer, $quantity);

        return (new Product())
            ->setBasketItemId($basketItemId)
            ->setShortProduct($shortProduct)
            ->setQuantity($quantity);
    }
}
