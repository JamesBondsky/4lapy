<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\ProductQuantity;
use Symfony\Component\Validator\Constraints as Assert;

class PutUserCartRequest
{
    /**
     * @Assert\Valid()
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
     * @return PutUserCartRequest
     */
    public function setGoods(array $goods): PutUserCartRequest
    {
        $this->goods = $goods;
        return $this;
    }
}
