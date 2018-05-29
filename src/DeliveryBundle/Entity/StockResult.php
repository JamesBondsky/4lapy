<?php

namespace FourPaws\DeliveryBundle\Entity;

use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\PriceForAmountCollection;
use FourPaws\StoreBundle\Entity\Store;

class StockResult
{
    public const TYPE_AVAILABLE = 'available';

    public const TYPE_DELAYED = 'delayed';

    public const TYPE_UNAVAILABLE = 'unavailable';

    /**
     * @var PriceForAmountCollection
     */
    protected $priceForAmount;

    /**
     * @var string
     */
    protected $type = self::TYPE_AVAILABLE;

    /**
     * @var Offer
     */
    protected $offer;

    /**
     * Склады, откуда будет осуществляться доставка/самовывоз
     *
     * @var Store
     */
    protected $store;

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->priceForAmount->getAmount();
    }

    /**
     * @return Offer
     */
    public function getOffer(): Offer
    {
        return $this->offer;
    }

    /**
     * @param Offer $offer
     *
     * @return StockResult
     */
    public function setOffer(Offer $offer): StockResult
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * @return Store
     */
    public function getStore(): Store
    {
        return $this->store;
    }

    /**
     * @param Store $stores
     *
     * @return StockResult
     */
    public function setStore(Store $stores): StockResult
    {
        $this->store = $stores;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return StockResult
     */
    public function setType(string $type): StockResult
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return PriceForAmountCollection
     */
    public function getPriceForAmount(): PriceForAmountCollection
    {
        return $this->priceForAmount;
    }

    /**
     * @param PriceForAmountCollection $priceForAmount
     * @return StockResult
     */
    public function setPriceForAmount(PriceForAmountCollection $priceForAmount): StockResult
    {
        $this->priceForAmount = $priceForAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->priceForAmount->getPrice();
    }

    /**
     * Разделяет элемент, оставляя в нем нужно кол-во товара.
     * Возращает собственный клон, содержащий остаток.
     *
     * @param $amount
     *
     * @return StockResult;
     */
    public function splitByAmount($amount): StockResult
    {
        $result = clone $this;

        $neededAmount = $amount;
        $currentAmountCollection = clone $this->getPriceForAmount();
        $priceForAmountCollection = new PriceForAmountCollection();
        foreach ($currentAmountCollection as $item) {
            if ($neededAmount <= 0) {
                $priceForAmountCollection->add(clone $item);
                $currentAmountCollection->removeElement($item);
                continue;
            }

            $diff = $item->getAmount() - $neededAmount;
            if ($diff > 0) {
                $item->setAmount($neededAmount);
                $priceForAmountCollection->add(
                    (clone $item)->setAmount($diff)
                );
            }

            $neededAmount -= $item->getAmount();
        }

        $this->setPriceForAmount($currentAmountCollection);
        return $result->setPriceForAmount($priceForAmountCollection);
    }

    /**
     * @param string $basketCode
     *
     * @return PriceForAmount|null
     */
    public function getPriceForAmountByBasketCode(string $basketCode): ?PriceForAmount
    {
        $result = null;
        /** @var PriceForAmount $item */
        foreach ($this->getPriceForAmount()->getIterator() as $item) {
            if ($item->getBasketCode() === $basketCode) {
                $result = $item;
                break;
            }
        }

        return $result;
    }
}
