<?php

namespace FourPaws\MobileApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class PreAuthenticationApiToken extends AbstractToken
{
    /**
     * @var string
     */
    private $token;

    public function __construct(array $roles = [], string $token = '')
    {
        parent::__construct($roles);
        $this->token = $token;
    }


    /**
     * Returns the user credentials.
     *
     * @return string The user credentials
     */
    public function getCredentials(): string
    {
        return $this->getToken();
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token)
    {
        $this->token = $token;
    }
}
