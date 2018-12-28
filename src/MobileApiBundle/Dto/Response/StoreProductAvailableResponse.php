<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Response;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\MobileApiBundle\Dto\Object\Store\Store;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class StoreListResponse
 * @package FourPaws\MobileApiBundle\Dto\Response
 */
class StoreProductAvailableResponse
{
    /**
     * Массив доступных в магазине товаров
     * @Serializer\SerializedName("available_goods")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct>")
     * @var ShortProduct[]
     */
    protected $availableGoods = [];

    /**
     * Массив НЕ доступных товаров в магазине
     * @Serializer\SerializedName("not_available_goods")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct>")
     * @var ShortProduct[]
     */
    protected $notAvailableGoods = [];

    /**
     * @var Store
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Store\Store")
     * @Serializer\SerializedName("shop")
     */
    protected $shop;

    /**
     * @return ShortProduct[]
     */
    public function getAvailableGoods(): array
    {
        return $this->availableGoods;
    }

    /**
     * @param ShortProduct[] $availableGoods
     *
     * @return StoreProductAvailableResponse
     */
    public function setAvailableGoods(array $availableGoods): StoreProductAvailableResponse
    {
        $this->availableGoods = $availableGoods;
        return $this;
    }

    /**
     * @return ShortProduct[]
     */
    public function getNotAvailableGoods(): array
    {
        return $this->notAvailableGoods;
    }

    /**
     * @param ShortProduct[] $notAvailableGoods
     *
     * @return StoreProductAvailableResponse
     */
    public function setNotAvailableGoods(array $notAvailableGoods): StoreProductAvailableResponse
    {
        $this->notAvailableGoods = $notAvailableGoods;
        return $this;
    }

    /**
     * @return Store
     */
    public function getShop(): Store
    {
        return $this->shop;
    }

    /**
     * @param Store $shop
     *
     * @return StoreProductAvailableResponse
     */
    public function setShop(Store $shop): StoreProductAvailableResponse
    {
        $this->shop = $shop;
        return $this;
    }
}
