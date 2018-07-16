<?php

namespace FourPaws\EcommerceBundle\Preset\Bitrix;

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product as ProductModel;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\ActionField;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Ecommerce;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\GoogleEcommerce;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Product;
use JMS\Serializer\ArrayTransformerInterface;

/**
 * Class SalePreset
 *
 * @package FourPaws\EcommerceBundle\Preset\Bitrix
 */
class SalePreset
{
    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;

    /**
     * SalePreset constructor.
     *
     * @param ArrayTransformerInterface $arrayTransformer
     */
    public function __construct(ArrayTransformerInterface $arrayTransformer)
    {
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @param Basket $basket
     * @param int $step
     * @param string $option
     *
     * @return GoogleEcommerce
     */
    public function createEcommerceToCheckoutFromBasket(Basket $basket, int $step, string $option): GoogleEcommerce
    {
        return (new GoogleEcommerce())->setEcommerce(
            (new Ecommerce())
                ->setCurrencyCode('RUB')
                ->setCheckout(
                    (new Action())
                        ->setActionField(
                            (new ActionField())
                                ->setStep($step)
                                ->setOption($option)
                        )
                        ->setProducts(
                            $this->createProductsFromBitrixBasket($basket)
                        )
                )
        );
    }

    /**
     * @param BasketItem $basketItem
     *
     * @return GoogleEcommerce
     */
    public function createAddFromBasketItem(BasketItem $basketItem): GoogleEcommerce
    {
        return (new GoogleEcommerce())->setEcommerce(
            (new Ecommerce())
                ->setCurrencyCode($basketItem->getCurrency())
                ->setAdd(
                    (new Action())
                        ->setProducts(
                            $this->createProductsFromBitrixBasketItem($basketItem)
                        )
                )
        );
    }

    /**
     * @param BasketItem $basketItem
     *
     * @return GoogleEcommerce
     */
    public function createRemoveFromBasketItem(BasketItem $basketItem): GoogleEcommerce
    {
        return (new GoogleEcommerce())->setEcommerce(
            (new Ecommerce())
                ->setCurrencyCode($basketItem->getCurrency())
                ->setRemove(
                    (new Action())
                        ->setProducts(
                            $this->createProductsFromBitrixBasketItem($basketItem)
                        )
                )
        );
    }

    /**
     * @param array|BasketItem[] $basketItemCollection
     *
     * @return GoogleEcommerce
     */
    public function createAddFromBasketItemCollection(array $basketItemCollection): GoogleEcommerce
    {
        $currency = $basketItemCollection[0] ? $basketItemCollection[0]->getCurrency() : 'RUB';

        return (new GoogleEcommerce())->setEcommerce(
            (new Ecommerce())
                ->setCurrencyCode($currency)
                ->setAdd(
                    (new Action())
                        ->setProducts(
                            $this->createProductsFromBitrixBasketItemCollection($basketItemCollection)
                        )
                )
        );
    }

    /**
     * @param Order $order
     * @param string $affiliation
     *
     * @return GoogleEcommerce
     */
    public function createPurchaseFromBitrixOrder(Order $order, string $affiliation): GoogleEcommerce
    {
        return (new GoogleEcommerce())->setEcommerce(
            (new Ecommerce())
                ->setCurrencyCode($order->getCurrency())
                ->setPurchase(
                    (new Action())
                        ->setActionField(
                            (new ActionField())
                                ->setId($order->getField('ACCOUNT_NUMBER'))
                                ->setAffiliation($affiliation)
                                ->setRevenue($order->getPrice())
                                ->setTax($order->getTaxPrice())
                                ->setShipping($order->getDeliveryPrice())
                                /**
                                 * @todo add coupon
                                 */
                                ->setCoupon('')
                        )
                        ->setProducts(
                            $this->createProductsFromBitrixBasket($order->getBasket())
                        )
                )
        );
    }

    /**
     * @param BasketItem $basketItem
     *
     * @return ArrayCollection
     *
     * @internal param Basket $basket
     */
    public function createProductsFromBitrixBasketItem(BasketItem $basketItem): ArrayCollection
    {
        /**
         * @var Basket $basket
         */
        $basket = Basket::create(\SITE_ID);
        $basket->addItem($basketItem);

        return $this->createProductsFromBitrixBasket($basket);
    }

    /**
     * @param array|BasketItem[] $basketItemCollection
     *
     * @return ArrayCollection
     *
     * @internal param Basket $basket
     */
    public function createProductsFromBitrixBasketItemCollection(array $basketItemCollection): ArrayCollection
    {
        /**
         * @var Basket $basket
         */
        $basket = Basket::create(\SITE_ID);

        foreach ($basketItemCollection as $basketItem) {
            $basket->addItem($basketItem);
        }

        return $this->createProductsFromBitrixBasket($basket);
    }

    /**
     * @param Basket $basket
     *
     * @return ArrayCollection
     */
    public function createProductsFromBitrixBasket(Basket $basket): ArrayCollection
    {
        $productCollection = new ArrayCollection();

        $this->enrichBasketCollection($basket)->map(function (array $item) use ($productCollection) {
            $productCollection->add(
                $this->arrayTransformer->fromArray($item, Product::class)
            );
        });

        return $productCollection;
    }

    /**
     * @todo плохо
     *
     * @param Basket $basket
     *
     * @return ArrayCollection
     */
    private function enrichBasketCollection(Basket $basket): ArrayCollection
    {
        $basketArrayCollection = new ArrayCollection();

        /**
         * @var BasketItem $basketItem
         */
        foreach ($basket as $basketItem) {
            /**
             * @var ProductModel $product
             */
            $product = null;
            /**
             * Получение отдельного оффера у нас кешируется, а коллекции - нет. Ну и не маппим.
             */
            $offer = Offer::createFromPrimary($basketItem->getProductId());
            if ($offer) {
                $product = $offer->getProduct();
            } else {
                continue;
            }

            $basketArrayCollection->add(
                \array_filter([
                    'basketId' => $basketItem->getId(),
                    'id' => $offer->getXmlId(),
                    'price' => $basketItem->getPrice(),
                    'discount' => $basketItem->getDiscountPrice(),
                    'tax' => $basketItem->getVat(),
                    'quantity' => $basketItem->getQuantity(),
                    'name' => $offer ? $offer->getName() : $basketItem->getField('NAME'),
                    'brand' => $product ? $product->getBrandName() : '',
                    'category' => $product ? \implode('|', \array_reverse($product->getFullPathCollection()->map(function (Category $category) {
                        return $category->getName();
                    })->toArray())) : '',
                ])
            );
        }

        return $basketArrayCollection;
    }
}
