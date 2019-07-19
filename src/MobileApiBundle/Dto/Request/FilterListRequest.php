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
     * @Assert\Type("integer")
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("id")
     * @var int
     */
    protected $id;

    /**
     * @Assert\Type("integer")
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("stock_id")
     * @var int
     */
    protected $stockId;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id ?: 0;
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

    /**
     * @param int $stockId
     * @return FilterListRequest
     */
    public function setStockId(int $stockId): FilterListRequest
    {
        $this->stockId = $stockId;
        return $this;
    }

    /**
     * @return int
     */
    public function getStockId(): int
    {
        return $this->stockId ?: 0;
    }
}
