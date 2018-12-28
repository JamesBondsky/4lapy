<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class DeliveryRangeRequest implements SimpleUnserializeRequest, GetRequest
{

    /**
     * @Serializer\SerializedName("city_id")
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     * @Assert\GreaterThan("0")
     * @var string
     */
    protected $cityId = '';

    /**
     * @return string
     */
    public function getCityId(): string
    {
        return $this->cityId;
    }

    /**
     * @param string $cityId
     * @return DeliveryRangeRequest
     */
    public function setCityId(string $cityId): DeliveryRangeRequest
    {
        $this->cityId = $cityId;
        return $this;
    }
}
