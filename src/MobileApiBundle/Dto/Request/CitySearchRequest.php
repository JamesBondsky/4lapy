<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CitySearchRequest implements GetRequest, SimpleUnserializeRequest
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("search")
     * @Assert\NotBlank()
     * @var string
     */
    protected $query;

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }
}
