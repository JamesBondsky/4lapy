<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog;

use JMS\Serializer\Annotation as Serializer;

class Sort
{
    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $id = '';

    /**
     * @Serializer\SerializedName("value")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"request"})
     * @var string
     */
    protected $value = '';

    /**
     * @Serializer\SerializedName("values")
     * @Serializer\Type("array")
     * @Serializer\Groups(groups={"response"})
     * @var array
     */
    protected $values = [];

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
     * @return Sort
     */
    public function setId(string $id): Sort
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return Sort
     */
    public function setValue(string $value): Sort
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array $values
     *
     * @return Sort
     */
    public function setValues(array $values): Sort
    {
        $this->values = $values;
        return $this;
    }
}
