<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Quest;

use FourPaws\Decorators\FullHrefDecorator;
use JMS\Serializer\Annotation as Serializer;

class Pet
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
    protected $title = '';

    /**
     * @Serializer\SerializedName("image")
     * @Serializer\Type("string")
     * @var string
     */
    protected $image = '';

    /**
     * @Serializer\SerializedName("description")
     * @Serializer\Type("string")
     * @var string
     */
    protected $description = '';

    /**
     * @Serializer\SerializedName("prizes")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Quest\Prize>")
     * @var Prize[]
     */
    protected $prizes = [];

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Pet
     */
    public function setId(int $id): Pet
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
     * @return Pet
     */
    public function setTitle(string $title): Pet
    {
        $this->title = $title;
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
     * @return Pet
     */
    public function setImage(string $image): Pet
    {
        $this->image = (string) new FullHrefDecorator($image);
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Pet
     */
    public function setDescription(string $description): Pet
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Prize[]
     */
    public function getPrizes(): array
    {
        return $this->prizes;
    }

    /**
     * @param Prize[] $prizes
     * @return Pet
     */
    public function setPrizes(array $prizes): Pet
    {
        $this->prizes = $prizes;
        return $this;
    }
}
