<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\Sort;
use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class GoodsItemRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * id продукта
     *
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * @Serializer\SerializedName("id")
     * @Serializer\Type("integer")
     * @var int
     */
    protected $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return GoodsItemRequest
     */
    public function setId(string $id): GoodsItemRequest
    {
        $this->id = $id;
        return $this;
    }
}
