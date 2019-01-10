<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\ProductQuantity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StoreAvailableRequest
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class StoreAvailableRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Assert\NotBlank()
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
     * @return StoreAvailableRequest
     */
    public function setGoods(array $goods): StoreAvailableRequest
    {
        $this->goods = $goods;
        return $this;
    }
}
