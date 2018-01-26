<?php

namespace FourPaws\MobileApiBundle\Services;

use FourPaws\MobileApiBundle\Entity\Session;
use FourPaws\MobileApiBundle\Exception\BitrixException;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException;
use FourPaws\MobileApiBundle\Exception\SessionCreateException;
use FourPaws\MobileApiBundle\Exception\TokenNotFoundException;
use FourPaws\MobileApiBundle\Exception\ValidationException;
use FourPaws\MobileApiBundle\Exception\WrongTransformerResultException;
use FourPaws\MobileApiBundle\Repository\UserSessionRepository;
use FourPaws\MobileApiBundle\Services\Security\SessionFactory;

class UserSessionService
{
    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var UserSessionRepository
     */
    private $userSessionRepository;

    public function __construct(UserSessionRepository $userSessionRepository, SessionFactory $sessionFactory)
    {
        $this->sessionFactory = $sessionFactory;
        $this->userSessionRepository = $userSessionRepository;
    }

    /**
     * @throws SessionCreateException
     * @throws WrongTransformerResultException
     * @throws ValidationException
     * @throws BitrixException
     * @throws InvalidIdentifierException
     * @return Session
     */
    public function create(): Session
    {
        $session = $this->sessionFactory->create();
        if ($this->userSessionRepository->create($session)) {
            return $session;
        }
        throw new SessionCreateException('Error while trying to create session');
    }

    /**
     * @param string $token
     *
     * @throws \FourPaws\MobileApiBundle\Exception\WrongTransformerResultException
     * @throws \FourPaws\MobileApiBundle\Exception\ValidationException
     * @throws \FourPaws\MobileApiBundle\Exception\TokenNotFoundException
     * @throws \FourPaws\MobileApiBundle\Exception\BitrixException
     * @throws \FourPaws\MobileApiBundle\Exception\InvalidIdentifierException
     * @return bool
     */
    public function update(string $token): bool
    {
        if ($session = $this->findByToken($token)) {
            $this->sessionFactory->update($session);
            return $this->userSessionRepository->update($session);
        }
        throw new TokenNotFoundException(sprintf('Token with identifier %s not found', $token));
    }

    /**
     * @param string $token
     *
     * @throws InvalidIdentifierException
     * @return null|Session
     */
    public function findByToken(string $token)
    {
        return $this->userSessionRepository->findByToken($token);
    }

    /**
     * @param string $token
     *
     * @throws InvalidIdentifierException
     * @return bool
     */
    public function isExist(string $token): bool
    {
        return $this->findByToken($token) instanceof Session;
    }
}
