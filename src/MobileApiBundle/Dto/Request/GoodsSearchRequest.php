<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class GoodsSearchRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Assert\Type("string")
     * @Assert\NotBlank()
     * @Serializer\SerializedName("request")
     * @Serializer\Type("string")
     * @var string
     */
    protected $query = '';

    /**
     * Номер страницы, начиная с 1
     *
     * @Assert\GreaterThanOrEqual(value="1")
     * @Serializer\Type("int")
     * @Serializer\SerializedName("page")
     * @var int
     */
    protected $page = 1;
    /**
     * Количество товаров, начиная с 1
     *
     * @Assert\GreaterThanOrEqual(value="1")
     * @Serializer\Type("int")
     * @Serializer\SerializedName("count")
     * @var int
     */
    protected $count = 10;

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return GoodsSearchRequest
     */
    public function setQuery(string $query): GoodsSearchRequest
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     *
     * @return GoodsSearchRequest
     */
    public function setPage(int $page): GoodsSearchRequest
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return GoodsSearchRequest
     */
    public function setCount(int $count): GoodsSearchRequest
    {
        $this->count = $count;
        return $this;
    }
}
