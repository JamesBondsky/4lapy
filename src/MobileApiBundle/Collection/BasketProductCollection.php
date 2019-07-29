<?php
/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Collection;

use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\Detailing;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\MobileApiBundle\Dto\Object\PriceWithQuantity;

class BasketProductCollection extends ProductQuantityCollection
{
    /**
     * BasketProductCollection constructor.
     * @param Product[] $elements
     */
    public function __construct(array $elements = [])
    {
        parent::__construct($elements);
    }

    /**
     * @return int
     */
    public function getTotalQuantity()
    {
        $quantity = 0;
        /** @var Product $product */
        foreach ($this->getValues() as $product) {
            /** @var $priceWithQuantity PriceWithQuantity */
            foreach ($product->getPrices() as $priceWithQuantity) {
                $quantity += $priceWithQuantity->getQuantity();
            }
        }
        return $quantity;
    }

    /**
     * @return Price
     */
    public function getTotalPrice()
    {
        $actualPrice = 0;
        $oldPrice = 0;
        $subscribePrice = 0;
        /** @var Product $product */
        foreach ($this->getValues() as $product) {
            /** @var $priceWithQuantity PriceWithQuantity */
            foreach ($product->getPrices() as $priceWithQuantity) {
                $quantity = $priceWithQuantity->getQuantity();
                $price = $priceWithQuantity->getPrice();
                $oldPrice += $quantity * ($price->getOld() ? $price->getOld() : $price->getActual());
                $actualPrice += $quantity * $price->getActual();
                $subscribePrice += $quantity * $price->getSubscribe();
            }
        }
        return (new Price())
            ->setActual($actualPrice)
            ->setOld($oldPrice)
            ->setSubscribe($subscribePrice);
    }

    /**
     * @return int
     */
    public function getTotalBonuses(): int
    {
        $totalBonuses = 0;
        /** @var Product $product */
        foreach ($this->getValues() as $product) {
            /** @var $priceWithQuantity PriceWithQuantity */
            $totalBonuses += $product->getShortProduct()->getBonusUser() * $product->getQuantity();
        }
        return $totalBonuses;
    }

    /**
     * @return int
     */
    public function getAmountBonus(): int
    {
        $totalBonuses = 0;
        array_map(function ($productItem) use (&$totalBonuses) {
            $totalBonuses += $productItem->getShortProduct()->getBonusUser();
        }, $this->getValues());
        return $totalBonuses;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        $price = $this->getTotalPrice()->getOld();
        $discountPrice = $this->getTotalPrice()->getActual();
        $discount = $price - $discountPrice;
        $discount = max($discount, 0);
        return $discount;
    }

    /**
     * @param string $storeCode
     * @return Product[]
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getAvailableInStore(string $storeCode): array
    {
        return parent::getAvailableInStore($storeCode);
    }

    /**
     * @param string $storeCode
     * @return Product[]
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getUnAvailableInStore(string $storeCode): array
    {
        return parent::getUnAvailableInStore($storeCode);
    }

    /**
     * @var Product $product
     * @return int
     */
    protected function getOfferId($product)
    {
        return $product->getShortProduct()->getId();
    }

    /**
     * @var Product $product
     * @return int
     */
    protected function getQuantity($product)
    {
        return $product->getQuantity();
    }

}
