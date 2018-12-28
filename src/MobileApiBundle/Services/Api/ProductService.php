<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\CatalogBundle\Helper\MarkHelper;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\UserBundle\Service\UserService;

class ProductService
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param Offer $offer
     * @return array<ShortProduct\Tag()>
     * @throws \Bitrix\Main\SystemException
     */
    public function getTags(Offer $offer)
    {
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
        return $tags;
    }

    /**
     * @param Product $product
     * @param Offer $offer
     * @param int $quantity
     * @return ShortProduct
     * @throws \Bitrix\Main\SystemException
     */
    public function convertToShortProduct(Product $product, Offer $offer, $quantity = 1): ShortProduct
    {
        $shortProduct = (new ShortProduct())
            ->setId($offer->getId())
            ->setTitle($offer->getName())
            ->setXmlId($offer->getXmlId())
            ->setBrandName($product->getBrandName())
            ->setWebPage($offer->getCanonicalPageUrl())
            ->setPicture($offer->getImages() ? $offer->getImages()->first() : '')
            ->setPicturePreview($offer->getResizeImages(200, 250) ? $offer->getResizeImages(200, 250)->first() : '');

        // цена
        $price = (new Price())
            ->setActual($offer->getOldPrice())
            ->setOld($offer->getPrice());
        $shortProduct->setPrice($price);

        // лейблы
        $shortProduct->setTag($this->getTags($offer));

        // бонусы
        $shortProduct->setBonusAll($offer->getBonusCount(3, $quantity));
        $shortProduct->setBonusUser($offer->getBonusCount($this->userService->getDiscount(), $quantity));

        return $shortProduct;
    }

    /**
     * @param Product $product
     * @param Offer $offer
     * @return FullProduct
     * @throws \Bitrix\Main\SystemException
     */
    public function convertToFullProduct(Product $product, Offer $offer): FullProduct
    {
        $shortProduct = $this->convertToShortProduct($product, $offer);
        $fullProduct = (new FullProduct())
            ->setDetailsHtml($product->getDetailText()->getText());

        // toDo: is there any way to merge ShortProduct into FullProduct?
        $fullProduct
            ->setId($shortProduct->getId())
            ->setTitle($shortProduct->getTitle())
            ->setXmlId($shortProduct->getXmlId())
            ->setBrandName($shortProduct->getBrandName())
            ->setWebPage($shortProduct->getWebPage())
            ->setPicture($shortProduct->getPicture())
            ->setPicturePreview($shortProduct->getPicturePreview())
            ->setPrice($shortProduct->getPrice())
            ->setTag($shortProduct->getTag())
            ->setBonusAll($shortProduct->getBonusAll())
            ->setBonusUser($shortProduct->getBonusUser());

        return $fullProduct;
    }

}
