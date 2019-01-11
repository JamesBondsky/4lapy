<?php

namespace FourPaws\MobileApiBundle\Security;

use Bitrix\Sale\Fuser;
use FourPaws\MobileApiBundle\Entity\ApiUserSession;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException as MobileInvalidIdentifierException;
use FourPaws\MobileApiBundle\Exception\InvalidTokenException;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Security\BitrixUserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Role\Role;

class ApiTokenProvider implements AuthenticationProviderInterface
{
    /**
     * @var ApiUserSessionRepository
     */
    private $sessionRepository;

    /**
     * @var \CUser
     */
    private $cUser;

    /**
     * @var BitrixUserProviderInterface
     */
    private $bitrixUserProvider;

    public function __construct(BitrixUserProviderInterface $bitrixUserProvider, ApiUserSessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
        $this->cUser = new \CUser();
        $this->bitrixUserProvider = $bitrixUserProvider;
    }

    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param PreAuthenticationApiToken|TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return ApiToken An authenticated TokenInterface instance, never null
     */
    public function authenticate(TokenInterface $token): ApiToken
    {
        $session = $this->sessionRepository->findByToken($token->getToken());

        if (!$session) {
            throw new InvalidTokenException('Invalid token provided');
        }

        $user = null;
        // if there is userID in the session - authorize this user,
        // otherwise just return a token
        if ($this->initBySession($session) && $session->getUserId()) {
            $user = $this->bitrixUserProvider->loadUserById($session->getUserId());
            $user->getRolesCollection()->add(new Role('ROLE_API'));
        }

        return new ApiToken(
            $user ? $user->getRoles() : ['ROLE_API'],
            $session,
            $user
        );
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token
     *
     * @return bool true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token): bool
    {
        return $token instanceof PreAuthenticationApiToken;
    }

    protected function initBySession(ApiUserSession $session)
    {
        if ($session->getUserId()) {
            return $this->cUser->Authorize($session->getUserId());
        }
    }
}
