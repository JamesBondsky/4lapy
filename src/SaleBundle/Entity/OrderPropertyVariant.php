<?php

namespace FourPaws\SaleBundle\Entity;

use JMS\Serializer\Annotation as Serializer;

class OrderPropertyVariant
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
     * @return OrderPropertyVariant
     */
    public function setId(int $id): OrderPropertyVariant
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
     * @return OrderPropertyVariant
     */
    public function setPropertyId($propertyId): OrderPropertyVariant
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
     * @return OrderPropertyVariant
     */
    public function setPropertyCode(string $propertyCode): OrderPropertyVariant
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
     * @return OrderPropertyVariant
     */
    public function setName(string $name): OrderPropertyVariant
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
     * @return OrderPropertyVariant
     */
    public function setValue(string $value): OrderPropertyVariant
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
     * @return OrderPropertyVariant
     */
    public function setSort(int $sort): OrderPropertyVariant
    {
        $this->sort = $sort;

        return $this;
    }
}
