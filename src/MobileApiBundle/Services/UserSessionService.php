<?php

namespace FourPaws\MobileApiBundle\Services;

use FourPaws\MobileApiBundle\Entity\ApiUserSession;
use FourPaws\MobileApiBundle\Exception\BitrixException;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException;
use FourPaws\MobileApiBundle\Exception\SessionCreateException;
use FourPaws\MobileApiBundle\Exception\TokenNotFoundException;
use FourPaws\MobileApiBundle\Exception\ValidationException;
use FourPaws\MobileApiBundle\Exception\WrongTransformerResultException;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\MobileApiBundle\Services\Security\SessionFactory;
use FourPaws\UserBundle\Service\UserService as UserBundleService;

class UserSessionService
{
    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var ApiUserSessionRepository
     */
    private $userSessionRepository;

    /**
     * @var UserBundleService
     */
    private $userBundleService;

    public function __construct(
        ApiUserSessionRepository $userSessionRepository,
        SessionFactory $sessionFactory,
        UserBundleService $userBundleService
    )
    {
        $this->sessionFactory = $sessionFactory;
        $this->userSessionRepository = $userSessionRepository;
        $this->userBundleService = $userBundleService;
    }

    /**
     * @throws SessionCreateException
     * @throws WrongTransformerResultException
     * @throws ValidationException
     * @throws BitrixException
     * @throws InvalidIdentifierException
     * @return ApiUserSession
     */
    public function create(): ApiUserSession
    {
        if ($this->userBundleService->isAuthorized()) {
            // added by coldshine 07.01.18: при создании сессии пользователь не должен быть авторизован
            $this->userBundleService->logout();
        }
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
     * @return null|ApiUserSession
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
        return $this->findByToken($token) instanceof ApiUserSession;
    }
}
