<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class MetroStationsRequest implements GetRequest, SimpleUnserializeRequest
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
}
