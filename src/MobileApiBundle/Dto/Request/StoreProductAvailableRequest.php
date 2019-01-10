<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\ProductQuantity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StoreProductAvailableRequest
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class StoreProductAvailableRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Assert\NotBlank()
     * @Serializer\SerializedName("goods")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\ProductQuantity>")
     * @var ProductQuantity[]
     */
    protected $goods = [];

    /**
     * @Assert\NotBlank()
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("shop_id")
     */
    protected $shopId = '';

    /**
     * @return ProductQuantity[]
     */
    public function getGoods(): array
    {
        return $this->goods;
    }

    /**
     * @param ProductQuantity[] $goods
     *
     * @return StoreProductAvailableRequest
     */
    public function setGoods(array $goods): StoreProductAvailableRequest
    {
        $this->goods = $goods;
        return $this;
    }

    /**
     * @return string
     */
    public function getShopId(): string
    {
        return $this->shopId ?? '';
    }

    /**
     * @param string $shopId
     *
     * @return StoreProductAvailableRequest
     */
    public function setShopId(string $shopId): StoreProductAvailableRequest
    {
        $this->shopId = $shopId;
        return $this;
    }
}
