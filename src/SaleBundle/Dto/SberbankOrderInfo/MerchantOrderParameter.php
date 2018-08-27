<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\SberbankOrderInfo;

use JMS\Serializer\Annotation as Serializer;

class MerchantOrderParameter
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("name")
     * @Serializer\Type("string")
     */
    protected $name = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("value")
     * @Serializer\Type("string")
     */
    protected $value = '';

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MerchantOrderParameter
     */
    public function setName(string $name): MerchantOrderParameter
    {
        $this->name = $name;

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
     * @return MerchantOrderParameter
     */
    public function setValue(string $value): MerchantOrderParameter
    {
        $this->value = $value;

        return $this;
    }
}
