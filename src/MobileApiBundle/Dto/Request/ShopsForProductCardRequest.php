<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\ProductQuantity;
use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StoreAvailableRequest
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class ShopsForProductCardRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Assert\NotBlank()
     * @Serializer\SerializedName("product_id")
     * @Serializer\Type("int")
     * @var int
     */
    protected $productId;

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     * @return ShopsForProductCardRequest
     */
    public function setProductId(int $productId): ShopsForProductCardRequest
    {
        $this->productId = $productId;
        return $this;
    }
}
