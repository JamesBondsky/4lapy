<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class StampLevel
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct
 *
 * ОбъектКаталога.ПолныйТовар.УровеньСкидкиМарками
 */
class StampLevel
{
    /**
     * Цена на этом уровне
     * @Serializer\Type("string")
     * @Serializer\SerializedName("price")
     * @var string
     */
    protected $price;

    /**
     * Количество марок, необходимых для обмена
     * @Serializer\Type("int")
     * @Serializer\SerializedName("stamps")
     * @var int
     */
    protected $stamps;


    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @param string $price
     * @return StampLevel
     */
    public function setPrice(string $price): StampLevel
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return int
     */
    public function getStamps(): int
    {
        return $this->stamps;
    }

    /**
     * @param int $stamps
     * @return StampLevel
     */
    public function setStamps(int $stamps): StampLevel
    {
        $this->stamps = $stamps;
        return $this;
    }
}
