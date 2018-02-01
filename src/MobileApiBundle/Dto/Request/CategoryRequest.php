<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;

class CategoryRequest
{
    /**
     * Идентификатор родительской категории.
     * Если пусто, то происходит выгрузка от корневой категории
     * @Serializer\Type("string")
     * @Serializer\SerializedName("id")
     *
     * @var string
     */
    protected $id = '';

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
     * @return CategoryRequest
     */
    public function setId(string $id): CategoryRequest
    {
        $this->id = $id;
        return $this;
    }
}
