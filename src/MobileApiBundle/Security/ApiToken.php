<?php

namespace FourPaws\MobileApiBundle\Security;

use FourPaws\MobileApiBundle\Entity\ApiUserSession;
use FourPaws\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class ApiToken extends AbstractToken
{
    const UNAUTHORIZED_USER = 'Анонимный пользователь';

    /**
     * @var ApiUserSession
     */
    private $apiUserSession;


    public function __construct(array $roles = [], ApiUserSession $session, User $user = null)
    {
        parent::__construct($roles);
        $this->setApiUserSession($session);
        $this->setUser($user ?: static::UNAUTHORIZED_USER);
        $this->setAuthenticated(true);
    }

    /**
     * @return ApiUserSession
     */
    public function getApiUserSession(): ApiUserSession
    {
        return $this->apiUserSession;
    }

    /**
     * @param ApiUserSession $apiUserSession
     *
     * @return ApiToken
     */
    public function setApiUserSession(ApiUserSession $apiUserSession): ApiToken
    {
        $this->apiUserSession = $apiUserSession;
        return $this;
    }


    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return $this->getApiUserSession()->getToken();
    }
}
