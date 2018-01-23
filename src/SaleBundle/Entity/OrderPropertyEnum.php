<?php

namespace FourPaws\SaleBundle\Entity;

use JMS\Serializer\Annotation as Serializer;

class OrderPropertyEnum
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read"})
     */
    protected $id = 0;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ORDER_PROPS_ID")
     * @Serializer\Groups(groups={"read"})
     */
    protected $propertyId = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("SALE_INTERNALS_ORDER_PROPS_VARIANT_PROPERTY_CODE")
     * @Serializer\Groups(groups={"read"})
     */
    protected $propertyCode = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NAME")
     * @Serializer\Groups(groups={"read"})
     */
    protected $name = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("VALUE")
     * @Serializer\Groups(groups={"read"})
     */
    protected $value = '';

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("SORT")
     * @Serializer\Groups(groups={"read"})
     */
    protected $sort = 0;

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->id;
    }

    /**
     * @param mixed $id
     *
     * @return OrderPropertyEnum
     */
    public function setId(int $id): OrderPropertyEnum
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getPropertyId(): int
    {
        return (int)$this->propertyId;
    }

    /**
     * @param mixed $propertyId
     *
     * @return OrderPropertyEnum
     */
    public function setPropertyId($propertyId): OrderPropertyEnum
    {
        $this->propertyId = $propertyId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPropertyCode(): string
    {
        return (string)$this->propertyCode;
    }

    /**
     * @param string $propertyCode
     *
     * @return OrderPropertyEnum
     */
    public function setPropertyCode(string $propertyCode): OrderPropertyEnum
    {
        $this->propertyCode = $propertyCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->name;
    }

    /**
     * @param string $name
     *
     * @return OrderPropertyEnum
     */
    public function setName(string $name): OrderPropertyEnum
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return (string)$this->value;
    }

    /**
     * @param string $value
     *
     * @return OrderPropertyEnum
     */
    public function setValue(string $value): OrderPropertyEnum
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return (string)$this->sort;
    }

    /**
     * @param int $sort
     *
     * @return OrderPropertyEnum
     */
    public function setSort(int $sort): OrderPropertyEnum
    {
        $this->sort = $sort;

        return $this;
    }
}
