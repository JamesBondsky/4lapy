<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\ProductQuantity;
use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StoreProductAvailableRequest
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class StoreProductAvailableRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Assert\NotBlank()
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("shop_id")
     */
    protected $storeCode = '';

    /**
     * @return string
     */
    public function getStoreCode(): string
    {
        return $this->storeCode ?? '';
    }

    /**
     * @param string $storeCode
     *
     * @return StoreProductAvailableRequest
     */
    public function setStoreCode(string $storeCode): StoreProductAvailableRequest
    {
        $this->storeCode = $storeCode;
        return $this;
    }
}
