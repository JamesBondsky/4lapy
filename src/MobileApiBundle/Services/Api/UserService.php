<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\MobileApiBundle\Dto\Request\UserLoginRequest;
use FourPaws\MobileApiBundle\Exception\AlreadyAuthorizedException;
use FourPaws\MobileApiBundle\Exception\InvalidCredentialException as MobileInvalidCredentialException;
use FourPaws\MobileApiBundle\Exception\LogicException;
use FourPaws\MobileApiBundle\Exception\SystemException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;

class UserService
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthorization;

    /**
     * @var UserRegistrationProviderInterface
     */
    private $userRegistrationProvider;

    public function __construct(
        UserRepository $userRepository,
        UserAuthorizationInterface $userAuthorization,
        UserRegistrationProviderInterface $userRegistrationProvider
    ) {
        $this->userRepository = $userRepository;
        $this->userAuthorization = $userAuthorization;
        $this->userRegistrationProvider = $userRegistrationProvider;
    }

    /**
     * @param UserLoginRequest $userLoginRequest
     *
     * @throws \FourPaws\MobileApiBundle\Exception\AlreadyAuthorizedException
     * @throws \FourPaws\MobileApiBundle\Exception\LogicException
     * @throws \FourPaws\MobileApiBundle\Exception\InvalidCredentialException
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @return bool
     */
    public function loginOrRegister(UserLoginRequest $userLoginRequest): bool
    {
        if ($this->userAuthorization->isAuthorized()) {
            throw new AlreadyAuthorizedException('Trying to login or create while already authorized');
        }
        try {
            if ($this->userRepository->isExist($userLoginRequest->getLogin())) {
                return $this->login($userLoginRequest->getLogin(), $userLoginRequest->getPassword());
            }
            return $this->register($userLoginRequest->getLogin(), $userLoginRequest->getPassword());
            /**
             * @todo update token on event?
             */
        } catch (TooManyUserFoundException $exception) {
            throw new SystemException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param string $login
     * @param string $password
     *
     * @throws TooManyUserFoundException
     * @throws MobileInvalidCredentialException
     * @throws LogicException
     * @return bool
     */
    protected function login(string $login, string $password): bool
    {
        try {
            return $this->userAuthorization->login($login, $password);
        } catch (UsernameNotFoundException $exception) {
            throw new LogicException(sprintf('Username %s not found and exists', $login));
        } catch (InvalidCredentialException $exception) {
            throw new MobileInvalidCredentialException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param string $login
     * @param string $password
     *
     * @return bool
     */
    protected function register(string $login, string $password)
    {
        /**
         * @todo factory
         */
        $user = (new User())
            ->setLogin($login)
            ->setPassword($password);

        return $this->userRegistrationProvider->register($user);
    }
}
