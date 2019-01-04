<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\LocationBundle\LocationService;
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
    /**
     * @var LocationService
     */
    private $locationService;
    /**
     * @var DeliveryService
     */
    private $deliveryService;

    public function __construct(
        UserService $userService,
        LocationService $locationService,
        DeliveryService $deliveryService
    )
    {
        $this->userService = $userService;
        $this->locationService = $locationService;
        $this->deliveryService = $deliveryService;
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
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function convertToFullProduct(Product $product, Offer $offer): FullProduct
    {
        $shortProduct = $this->convertToShortProduct($product, $offer);
        $fullProduct = (new FullProduct())
            ->setDetailsHtml($product->getDetailText()->getText())
            ->setAvailability($offer->getAvailabilityText());

        // toDo: is there any better way to merge ShortProduct into FullProduct?
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

    /**
     * @param Offer $offer
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getDeliveries(Offer $offer)
    {
        $userLocationCode = $this->userService->getSelectedCity()['CODE'];

        /** @var CalculationResultInterface[] $deliveries */
        $allDeliveryCodes = array_merge(DeliveryService::DELIVERY_CODES, DeliveryService::PICKUP_CODES);
        $deliveries = $this->deliveryService->getByLocation($userLocationCode, $allDeliveryCodes);
        $deliveriesWithStock = [];
        foreach ($deliveries as $delivery) {
            $delivery->setStockResult(
                $this->deliveryService->getStockResultForOffer($offer, $delivery)
            )->setCurrentDate(new \DateTime());
            if ($delivery->isSuccess()) {
                $deliveriesWithStock[] = $delivery;
            }
        }
        return $deliveriesWithStock;
    }

    /**
     * @param Offer $offer
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getDeliveryText(Offer $offer): string
    {
        /** @var $deliveryResult DeliveryResult */
        $deliveryResult = $this->filterDeliveries($this->getDeliveries($offer));
        return $deliveryResult->getTextForOffer($offer->isByRequest(), true);
    }

    /**
     * @param Offer $offer
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getPickupText(Offer $offer): string
    {
        /** @var $pickupResult PickupResult */
        $pickupResult = $this->filterPickups($this->getDeliveries($offer));
        return $pickupResult->getTextForOffer();
    }

    /**
     * @param CalculationResultInterface[] $deliveries
     *
     * @return null|DeliveryResultInterface
     */
    protected function filterDeliveries($deliveries): ?DeliveryResultInterface
    {
        $filtered = array_filter(
            $deliveries,
            function (CalculationResultInterface $delivery) {
                return $this->deliveryService->isDelivery($delivery);
            }
        );

        return reset($filtered) ?: null;
    }

    /**
     * @param CalculationResultInterface[] $deliveries
     *
     * @return null|PickupResultInterface
     */
    protected function filterPickups($deliveries): ?PickupResultInterface
    {
        $filtered = array_filter(
            $deliveries,
            function (CalculationResultInterface $delivery) {
                return $this->deliveryService->isPickup($delivery);
            }
        );

        return reset($filtered) ?: null;
    }

}
