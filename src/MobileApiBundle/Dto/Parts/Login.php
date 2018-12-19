<?php

namespace FourPaws\MobileApiBundle\Dto\Parts;

use JMS\Serializer\Annotation as Serializer;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

trait Login
{
    /**
     * @Serializer\SerializedName("login")
     * @Serializer\Type("string")

     * @Assert\NotBlank()
     * @AssertPhoneNumber(defaultRegion="RU")

     * @var string
     */
    protected $login;

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     * @return Login
     */
    public function setLogin(string $login): Login
    {
        $this->login = $login;
        return $this;
    }
}
