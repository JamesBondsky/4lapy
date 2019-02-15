<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\Store\Store;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class StoreListResponse
 * @package FourPaws\MobileApiBundle\Dto\Response
 */
class StoreProductAvailableResponse
{
    /**
     * @var Store
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Store\Store")
     * @Serializer\SerializedName("shop")
     */
    protected $shop;

    public function __construct(Store $shop)
    {
        $this->shop = $shop;
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
