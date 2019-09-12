<?php


namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

class Color
{

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     */
    protected $name;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("hexCode")
     */
    protected $hexCode;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("imageUrl")
     */
    protected $imageUrl;

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setHexCode($code)
    {
        $this->hexCode = $code;
        return $this;
    }

    public function getHexCode()
    {
        return $this->hexCode;
    }

    public function setImageUrl($url)
    {
        $this->imageUrl = $url;
        return $this;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }
}
