<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class UserLoginRequest
 * @package FourPaws\MobileApiBundle\Dto\Request
 * @todo constraint
 */
class UserLoginRequest
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("login")
     */
    protected $login;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("password")
     */
    protected $password;

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     *
     * @return UserLoginRequest
     */
    public function setLogin(string $login): UserLoginRequest
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return UserLoginRequest
     */
    public function setPassword(string $password): UserLoginRequest
    {
        $this->password = $password;
        return $this;
    }
}
