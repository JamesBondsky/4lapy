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
     * @return $this
     */
    public function setLogin(string $login)
    {
        $this->login = $login;
        return $this;
    }
}
