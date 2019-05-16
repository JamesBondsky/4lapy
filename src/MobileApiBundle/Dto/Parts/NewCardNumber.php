<?php

namespace FourPaws\MobileApiBundle\Dto\Parts;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use FourPaws\MobileApiBundle\Validation as MobileApiAssert;

trait NewCardNumber
{
    /**
     * @Assert\NotBlank()
     * @MobileApiAssert\CardNumber()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("new_card_number")
     * @var string
     */
    protected $newCardNumber = '';

    /**
     * @return string
     */
    public function getNewCardNumber(): string
    {
        return $this->newCardNumber;
    }

    /**
     * @param string $newCardNumber
     * @return $this
     */
    public function setNewCardNumber(string $newCardNumber)
    {
        $this->newCardNumber = $newCardNumber;
        return $this;
    }
}
