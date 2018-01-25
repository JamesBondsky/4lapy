<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class FilterListRequest
{
    /**
     * @Assert\GreaterThanOrEqual("0")
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
