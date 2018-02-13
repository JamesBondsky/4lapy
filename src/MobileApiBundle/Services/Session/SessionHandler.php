<?php

namespace FourPaws\MobileApiBundle\Services\Session;


use Bitrix\Sale\Fuser;
use FourPaws\MobileApiBundle\Exception\BitrixException;
use FourPaws\MobileApiBundle\Exception\ValidationException;
use FourPaws\MobileApiBundle\Exception\WrongTransformerResultException;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\MobileApiBundle\Security\ApiToken;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserService;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionHandler
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ApiUserSessionRepository
     */
    private $sessionRepository;

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ApiUserSessionRepository $sessionRepository,
        UserService $userService
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->sessionRepository = $sessionRepository;
        $this->userService = $userService;
    }

    /**
     * Update toket and session after login
     *
     * @throws WrongTransformerResultException
     * @throws ValidationException
     * @throws BitrixException
     * @throws InvalidArgumentException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     */
    public function login()
    {
        /**
         * @var ApiToken $token | null
         */
        $token = $this->tokenStorage->getToken();
        if ($token && $session = $token->getApiUserSession()) {
            $session->setFUserId($this->userService->getCurrentFUserId());
            $session->setUserId($this->userService->getCurrentUserId());
            $token->setApiUserSession($session);
            $this->sessionRepository->update($session);

            $token->setUser($this->userService->getCurrentUser());
            $this->tokenStorage->setToken($token);
        }
    }

    /**
     * Update toket and session after logout
     *
     * @throws WrongTransformerResultException
     * @throws ValidationException
     * @throws BitrixException
     * @throws InvalidArgumentException
     */
    public function logout()
    {
        /**
         * @var ApiToken $token | null
         */
        $token = $this->tokenStorage->getToken();
        if ($token && $session = $token->getApiUserSession()) {
            $session->setUserId(0);
            $session->setFUserId(Fuser::getId());
            $token->setApiUserSession($session);
            $this->sessionRepository->update($session);
            $token->setUser('');
            $this->tokenStorage->setToken($token);
        }
    }
}