<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class PaginationRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("page")
     * @var int
     */
    protected $page = 1;

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("count")
     * @var string
     */
    protected $count = 10;

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return string
     */
    public function getCount(): int
    {
        return $this->count;
    }

}
