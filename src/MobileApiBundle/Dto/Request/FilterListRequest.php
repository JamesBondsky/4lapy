<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class FilterListRequest implements GetRequest, SimpleUnserializeRequest
{
    /**
     * @Assert\GreaterThan("0")
     * @Assert\Type("integer")
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("id")
     * @var int
     */
    protected $id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return FilterListRequest
     */
    public function setId(int $id): FilterListRequest
    {
        $this->id = $id;
        return $this;
    }
}
