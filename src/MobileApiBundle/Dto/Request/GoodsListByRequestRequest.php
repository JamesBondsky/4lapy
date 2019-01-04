<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class GoodsListByRequestRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Assert\NotBlank()
     * @Serializer\SerializedName("ids")
     * @Serializer\Type("array<int>")
     * @var array<int>
     */
    protected $ids = [];

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
