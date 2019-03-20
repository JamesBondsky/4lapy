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
     * @return Price
     */
    public function getTotalPrice()
    {
        $actualPrice = 0;
        $oldPrice = 0;
        /** @var Product $product */
        foreach ($this->getValues() as $product) {
            /** @var $priceWithQuantity PriceWithQuantity */
            foreach ($product->getPrices() as $priceWithQuantity) {
                $quantity = $priceWithQuantity->getQuantity();
                $price = $priceWithQuantity->getPrice();
                $oldPrice += $quantity * ($price->getOld() ? $price->getOld() : $price->getActual());
                $actualPrice += $quantity * $price->getActual();
            }
        }
        return (new Price())
            ->setActual($actualPrice)
            ->setOld($oldPrice === $actualPrice ? 0 : $oldPrice);
    }

    /**
     * @param float $deliveryPrice
     * @return Detailing[]
     */
    public function getPriceDetails(float $deliveryPrice = 0): array
    {
        $price = $this->getTotalPrice()->getOld();
        $discountPrice = $this->getTotalPrice()->getActual();
        $discount = $price - $discountPrice;
        $discount = max($discount, 0);

        return [
            (new Detailing())
                ->setId('cart_price_old')
                ->setTitle('Стоимость товаров без скидки')
                ->setValue($price),
            (new Detailing())
                ->setId('cart_price')
                ->setTitle('Стоимость товаров со скидкой')
                ->setValue($discountPrice),
            (new Detailing())
                ->setId('discount')
                ->setTitle('Скидка')
                ->setValue($discount),
            (new Detailing())
                ->setId('delivery')
                ->setTitle('Стоимость доставки')
                ->setValue($deliveryPrice),
        ];
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