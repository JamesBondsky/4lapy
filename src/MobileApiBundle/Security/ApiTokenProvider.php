<?php

namespace FourPaws\MobileApiBundle\Security;

use Bitrix\Sale\Fuser;
use FourPaws\MobileApiBundle\Entity\ApiUserSession;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException as MobileInvalidIdentifierException;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Repository\UserRepository;
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
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(BitrixUserProviderInterface $userRepository, ApiUserSessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
        $this->cUser = new \CUser();
        $this->userRepository = $userRepository;
    }

    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param PreAuthenticationApiToken|TokenInterface $token The TokenInterface instance to authenticate
     *
     * @throws AuthenticationException if the authentication fails
     * @return TokenInterface An authenticated TokenInterface instance, never null
     *
     */
    public function authenticate(TokenInterface $token): ApiToken
    {
        $session = null;
        try {
            $session = $this->sessionRepository->findByToken($token->getToken());
        } catch (MobileInvalidIdentifierException $exception) {
        }


        if ($session && $this->initBySession($session)) {
            $user = null;
            if ($session->getUserId()) {
                try {
                    $user = $this->userRepository->find($session->getUserId());
                    $user->getRolesCollection()->add(new Role('ROLE_API'));
                } catch (InvalidIdentifierException $exception) {
                } catch (ConstraintDefinitionException $exception) {
                }
            }
            return new ApiToken(
                $user ? $user->getRoles() : ['ROLE_API'],
                $session,
                $user
            );
        }
        throw new AuthenticationException('The Api Token authentication failed.');
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
        if (!$session->getFUserId()) {
            return false;
        }
        $_SESSION['SALE_USER_ID'] = $session->getFUserId();
        return Fuser::getId(true) === $session->getFUserId();
    }
}
