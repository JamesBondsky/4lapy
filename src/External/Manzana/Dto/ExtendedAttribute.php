<?php

namespace FourPaws\External\Manzana\Dto;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class ExtendedAttribute
 *
 * @package FourPaws\External\Manzana\Dto
 *
 * @Serializer\XmlRoot("ExtendedAttribute")
 */
class ExtendedAttribute
{
    /**
     * Тип обмена скидки на марки (например, "Stamps_trade_action_20", т.е. 20% скидка)
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Key")
     *
     * @var string
     */
    protected $key = '';

    /**
     * В первом запросе/ответе мягкого чека и во втором запросе (с выбранной на сайте скидкой) - количество товара в позиции, к которому применить обмен марок на скидку.
     * В ответе на второй запрос - размер примененной скидки (@fixme wattt? ошибка в доке?)
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Value")
     *
     * @var float
     */
    protected $value = 0;


    /**
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return ExtendedAttribute
     */
    public function setKey(string $key) : ExtendedAttribute
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return float
     */
    public function getValue() : float
    {
        return $this->value;
    }

    /**
     * @param float $value
     *
     * @return ExtendedAttribute
     */
    public function setValue(float $value) : ExtendedAttribute
    {
        $this->value = $value;

        return $this;
    }


}
