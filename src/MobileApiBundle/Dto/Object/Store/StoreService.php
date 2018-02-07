<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Store;

use JMS\Serializer\Annotation as Serializer;

class StoreService
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("image")
     */
    protected $image;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     */
    protected $title;

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     *
     * @return StoreService
     */
    public function setImage(string $image): StoreService
    {
        $this->image = $image;
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
     *
     * @return StoreService
     */
    public function setTitle(string $title): StoreService
    {
        $this->title = $title;
        return $this;
    }
}