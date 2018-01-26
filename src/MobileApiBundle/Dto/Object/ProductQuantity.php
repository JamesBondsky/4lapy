<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

class ProductQuantity
{
    /**
     * @Serializer\SerializedName("goods_id")
     * @Serializer\Type("integer")
     * @var int
     */
    protected $productId = 0;
    
    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("qty")
     * @var int
     */
    protected $quantity = 0;

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     *
     * @return ProductQuantity
     */
    public function setProductId(int $productId): ProductQuantity
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return ProductQuantity
     */
    public function setQuantity(int $quantity): ProductQuantity
    {
        $this->quantity = $quantity;
        return $this;
    }
}
