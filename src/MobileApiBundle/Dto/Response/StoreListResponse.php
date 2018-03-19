<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Response;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\MobileApiBundle\Dto\Object\Store\Store;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class StoreListResponse
 * @package FourPaws\MobileApiBundle\Dto\Response
 */
class StoreListResponse
{
    /**
     * @var Collection|Store[]
     * @Serializer\Type("ArrayCollection<FourPaws\MobileApiBundle\Dto\Object\Store\Store>")
     * @Serializer\SerializedName("shops")
     */
    protected $shops;

    public function __construct(Collection $collection = null)
    {
        $this->shops = $collection ?: new ArrayCollection();
    }

    /**
     * @return Collection|Store[]
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param Collection|Store[] $shops
     *
     * @return StoreListResponse
     */
    public function setShops(Collection $shops)
    {
        $this->shops = $shops;
        return $this;
    }

    /**
     * @param Store $store
     *
     * @return bool
     */
    public function addShop(Store $store)
    {
        return $this->shops->add($store);
    }

    /**
     * @param Store $store
     *
     * @return bool
     */
    public function removeShop(Store $store)
    {
        return $this->shops->removeElement($store);
    }
}
