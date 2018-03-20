<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

class Filter
{
    /**
     * Идентификатор фильтра
     *
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $id;

    /**
     * @Serializer\Groups(groups={"response"})
     * @Serializer\SerializedName("name")
     * @Serializer\Type("string")
     * @var string
     */
    protected $name = '';


    /**
     * @Serializer\Groups(groups={"response"})
     * @Serializer\SerializedName("values")
     * @Serializer\Type("ArrayCollection<FourPaws\MobileApiBundle\Dto\Object\Catalog\FilterVariant>")
     * @var Collection|string[]
     */
    protected $values;

    /**
     * Примененное значение
     *
     * @Serializer\Groups(groups={"request"})
     * @Serializer\SerializedName("value")
     * @Serializer\Type("array")
     * @var array
     */
    protected $value = [];

    /**
     * @Serializer\Groups(groups={"response"})
     * @Serializer\SerializedName("min")
     * @Serializer\Type("int")
     * @var int
     */
    protected $min = 0;

    /**
     * @Serializer\Groups(groups={"response"})
     * @Serializer\SerializedName("max")
     * @Serializer\Type("int")
     * @var int
     */
    protected $max = 0;

    public function __construct()
    {
        $this->values = new ArrayCollection();
    }

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
     * @return Filter
     */
    public function setId(string $id): Filter
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
     *
     * @return Filter
     */
    public function setName(string $name): Filter
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return $this->value;
    }

    /**
     * @param array $value
     *
     * @return Filter
     */
    public function setValue(array $value): Filter
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getMin(): int
    {
        return $this->min;
    }

    /**
     * @param int $min
     *
     * @return Filter
     */
    public function setMin(int $min): Filter
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @return int
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * @param int $max
     *
     * @return Filter
     */
    public function setMax(int $max): Filter
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    /**
     * @param Collection $values
     *
     * @return Filter
     */
    public function setValues(Collection $values): Filter
    {
        $this->values = $values;
        return $this;
    }
}
