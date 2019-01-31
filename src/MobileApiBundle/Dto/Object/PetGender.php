<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use FourPaws\Decorators\FullHrefDecorator;
use JMS\Serializer\Annotation as Serializer;

class PetGender
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("id")
     * @var string
     */
    protected $id;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @var string
     */
    protected $title;

    /**
     * @return int
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return PetGender
     */
    public function setId(string $id): PetGender
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return PetGender
     */
    public function setTitle(string $title): PetGender
    {
        $this->title = $title;
        return $this;
    }

}