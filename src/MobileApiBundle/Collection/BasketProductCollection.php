<?php
/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Collection;

use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\Detailing;
use FourPaws\MobileApiBundle\Dto\Object\Price;

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
            $oldPrice += $product->getQuantity() * $product->getShortProduct()->getPrice()->getOld();
            $actualPrice += $product->getQuantity() * $product->getShortProduct()->getPrice()->getActual();
        }
        return (new Price())
            ->setActual($actualPrice)
            ->setOld($oldPrice);
    }

    /**
     * @return Detailing[]
     */
    public function getPriceDetails(): array
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
                ->setValue(0),
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