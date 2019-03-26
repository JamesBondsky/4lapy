<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 07.03.2019
 * Time: 18:09
 */

namespace FourPaws\Catalog\Model;

use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

class Price
{
    /**
     * @var int
     * @Type("int")
     * @Groups({"elastic"})
     * @Accessor(getter="getId")
     */
    protected $ID;

    /**
     * @var int
     * @Type("int")
     * @Groups({"elastic"})
     * @Accessor(getter="getProductId")
     */
    protected $PRODUCT_ID;

    /**
     * @var int
     * @Type("int")
     * @Groups({"elastic"})
     * @Accessor(getter="getCatalogGroupId")
     */
    protected $CATALOG_GROUP_ID;

    /**
     * @var float
     * @Type("float")
     * @Groups({"elastic"})
     * @Accessor(getter="getPrice")
     */
    protected $PRICE;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     * @Accessor(getter="getCurrency")
     */
    protected $CURRENCY;

    /**
     * Price constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        foreach ($fields as $field => $value) {
            if ($value === null) {
                continue;
            }

            $this->{$field} = $value;
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->ID;
    }

    /**
     * @param int $ID
     * @return Price
     */
    public function setId(int $ID): Price
    {
        $this->ID = $ID;
        return $this;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->PRODUCT_ID;
    }

    /**
     * @param int $PRODUCT_ID
     * @return Price
     */
    public function setProductId(int $PRODUCT_ID): Price
    {
        $this->PRODUCT_ID = $PRODUCT_ID;
        return $this;
    }

    /**
     * @return int
     */
    public function getCatalogGroupId(): int
    {
        return $this->CATALOG_GROUP_ID;
    }

    /**
     * @param int $CATALOG_GROUP_ID
     * @return Price
     */
    public function setCatalogGroupId(int $CATALOG_GROUP_ID): Price
    {
        $this->CATALOG_GROUP_ID = $CATALOG_GROUP_ID;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->PRICE;
    }

    /**
     * @param float $PRICE
     * @return Price
     */
    public function setPrice(float $PRICE): Price
    {
        $this->PRICE = $PRICE;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->CURRENCY;
    }

    /**
     * @param string $CURRENCY
     * @return Price
     */
    public function setCurrency(string $CURRENCY): Price
    {
        $this->CURRENCY = $CURRENCY;
        return $this;
    }


}