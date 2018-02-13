<?php

namespace FourPaws\MobileApiBundle\Services\Session;

use FourPaws\MobileApiBundle\Exception\BitrixException;
use FourPaws\MobileApiBundle\Exception\ValidationException;
use FourPaws\MobileApiBundle\Exception\WrongTransformerResultException;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserService;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Interface SessionHandlerInterface
 *
 * @package FourPaws\MobileApiBundle\Services\Session
 */
interface SessionHandlerInterface
{

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ApiUserSessionRepository $sessionRepository,
        UserService $userService
    );

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
    public function login();

    /**
     * Update toket and session after logout
     *
     * @throws WrongTransformerResultException
     * @throws ValidationException
     * @throws BitrixException
     * @throws InvalidArgumentException
     */
    public function logout();
}