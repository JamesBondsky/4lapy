<?php

namespace FourPaws\MobileApiBundle\Dto\Parts;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

trait Token
{
    /**
     * @Assert\NotBlank(message = "Token is empty")
     * @Assert\Length(min="32",max="32")
     * @Serializer\Type("string")
     * @Serializer\SerializedName("token")
     * @var string
     */
    protected $token = '';

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return Token
     */
    public function setToken(string $token): Token
    {
        $this->token = $token;
        return $this;
    }
}
