<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\ProductQuantity;
use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;

class PostUserCartRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("goods")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\ProductQuantity>")
     * @var ProductQuantity[]
     */
    protected $goods = [];

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
     * @return PostUserCartRequest
     */
    public function setGoods(array $goods): PostUserCartRequest
    {
        $this->goods = $goods;
        return $this;
    }
}
