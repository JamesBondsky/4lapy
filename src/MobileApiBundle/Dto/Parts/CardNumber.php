<?php

namespace FourPaws\MobileApiBundle\Dto\Parts;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use FourPaws\MobileApiBundle\Validation as MobileApiAssert;

trait CardNumber
{
    /**
     * @Assert\NotBlank()
     * @MobileApiAssert\CardNumber()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("number")
     * @var string
     */
    protected $cardNumber = '';

    /**
     * @return string
     */
    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    /**
     * @param string $cardNumber
     * @return $this
     */
    public function setCardNumber(string $cardNumber)
    {
        $this->cardNumber = $cardNumber;
        return $this;
    }
}
