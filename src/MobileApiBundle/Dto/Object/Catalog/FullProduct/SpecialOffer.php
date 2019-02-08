<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;

use FourPaws\Decorators\FullHrefDecorator;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class SpecialOffer
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct
 *
 * ОбъектКаталога.ПолныйТовар.Акция
 */
class SpecialOffer
{
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     * @var int
     */
    protected $id;
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("image")
     *
     * @var string
     */
    protected $image;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("name")
     *
     * @var string
     */
    protected $name;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("date")
     *
     * @var string
     */
    protected $date;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("description")
     *
     * @var string
     */
    protected $description = '';

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return SpecialOffer
     */
    public function setId(int $id): SpecialOffer
    {
        $this->id = $id;
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
     *
     * @return SpecialOffer
     */
    public function setImage(string $image): SpecialOffer
    {
        $this->image = (string) new FullHrefDecorator($image);
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
     *
     * @return SpecialOffer
     */
    public function setName(string $name): SpecialOffer
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     *
     * @return SpecialOffer
     */
    public function setDate(string $date): SpecialOffer
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->date;
    }

    /**
     * @param string $description
     *
     * @return SpecialOffer
     */
    public function setDescription(string $description): SpecialOffer
    {
        $this->description = $description;
        return $this;
    }
}
