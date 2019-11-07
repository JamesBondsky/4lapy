<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Quest;

use FourPaws\Decorators\FullHrefDecorator;
use JMS\Serializer\Annotation as Serializer;

class Prize
{
    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("int")
     * @var int
     */
    protected $id = 0;

    /**
     * @Serializer\SerializedName("title")
     * @Serializer\Type("string")
     * @var string
     */
    protected $name = '';

    /**
     * @Serializer\SerializedName("xml_id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $xmlId = '';

    /**
     * @Serializer\SerializedName("image")
     * @Serializer\Type("string")
     * @var string
     */
    protected $image = '';

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Prize
     */
    public function setId(int $id): Prize
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Prize
     */
    public function setName(string $name): Prize
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): ?string
    {
        return $this->xmlId;
    }

    /**
     * @param string $xmlId
     * @return Prize
     */
    public function setXmlId(?string $xmlId): Prize
    {
        $this->xmlId = $xmlId;
        return $this;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return Prize
     */
    public function setImage(?string $image): Prize
    {
        if ($image !== null) {
            $this->image = (string)new FullHrefDecorator($image);
        }
        return $this;
    }
}
