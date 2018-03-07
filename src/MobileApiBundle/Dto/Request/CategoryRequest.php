<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * Идентификатор родительской категории.
     * Если пусто, то происходит выгрузка от корневой категории
     *
     * @Assert\GreaterThanOrEqual("0")
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     *
     * @var string
     */
    protected $id = 0;

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
     * @return CategoryRequest
     */
    public function setId(int $id): CategoryRequest
    {
        $this->id = $id;
        return $this;
    }
}
