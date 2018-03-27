<?php

namespace FourPaws\External\Manzana\Dto;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Coupon
 *
 * @package FourPaws\External\Manzana\Dto
 *
 * @todo
 * Реализовать EmissionID и TypeID в случае необходимости
 *
 * @Serializer\XmlRoot("Coupon")
 */
class Coupon
{
    /**
     * Номер купона
     *
     * Присутствует строго один раз. Не может присутствовать с тегами EmissionID и TypeID в теге Coupon.
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Number")
     *
     * @var string
     */
    protected $number = '';

    /**
     * Сообщение о применимости купона к акции
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ApplicabilityMessage")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $applicabilityMessage = '';

    /**
     * Код применимости купона к акции ()
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("int")
     * @Serializer\SerializedName("ApplicabilityCode")
     * @Serializer\SkipWhenEmpty()
     *
     * @var int
     */
    protected $applicabilityCode;
    
    /**
     * @return string
     */
    public function getNumber() : string
    {
        return $this->number;
    }
    
    /**
     * @param string $number
     *
     * @return $this
     */
    public function setNumber(string $number)
    {
        $this->number = $number;
        
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplicabilityCode(): bool
    {
        return $this->applicabilityCode === 1;
    }

    /**
     * @param bool $applicabilityCode
     *
     * @return Coupon
     */
    public function setApplicabilityCode(bool $applicabilityCode): Coupon
    {
        $this->applicabilityCode = $applicabilityCode ? 1 : 0;

        return $this;
    }
}
