<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\ProductQuantity;
use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StoreListRequest
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class StoreListAvailableRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Assert\NotBlank()
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_id")
     */
    protected $cityId = '';

    /**
     * @return string
     */
    public function getCityId(): string
    {
        return $this->cityId ?? '';
    }

    /**
     * @param string $cityId
     *
     * @return StoreListAvailableRequest
     */
    public function setCityId(string $cityId): StoreListAvailableRequest
    {
        $this->cityId = $cityId;
        return $this;
    }
}
