<?php

namespace FourPaws\SaleBundle\Dto\ShopList;

use JMS\Serializer\Annotation as Serializer;

class Payment
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("name")
     * @Serializer\Type("string")
     */
    protected $name;

    /**
     * @var string
     *
     * @Serializer\SerializedName("code")
     * @Serializer\Type("string")
     */
    protected $code;

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
     * @return Payment
     */
    public function setName(string $name): Payment
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Payment
     */
    public function setCode(string $code): Payment
    {
        $this->code = $code;

        return $this;
    }
}
