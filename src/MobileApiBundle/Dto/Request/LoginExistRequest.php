<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class LoginExistRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Serializer\SerializedName("login")
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     * @var string
     */
    protected $login = '';

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }
}
