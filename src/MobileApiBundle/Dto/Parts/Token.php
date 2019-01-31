<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Parts;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

trait Token
{
    /**
     * @Assert\NotBlank(message = "Не указан токен")
     * @Assert\Length(min="32",max="32",message="Токен должен содержать 32 символа")
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
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;
        return $this;
    }
}
