<?php

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;

class UserService implements CurrentUserProviderInterface, UserAuthorizationInterface, UserRegistrationProviderInterface
{
    /**
     * @var \CAllUser|\CUser
     */
    private $bitrixUserService;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        /**
         * todo move to factory service
         */
        global $USER;
        $this->bitrixUserService = $USER;
        $this->userRepository = $userRepository;
    }


    /**
     * @param string $rawLogin
     * @param string $password
     *
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @throws InvalidCredentialException
     * @return bool
     */
    public function login(string $rawLogin, string $password): bool
    {
        $login = $this->userRepository->findLoginByRawLogin($rawLogin);
        $result = $this->bitrixUserService->Login($login, $password);
        if ($result === true) {
            return true;
        }

        throw new InvalidCredentialException($result['MESSAGE']);
    }

    /**
     * @return bool
     */
    public function logout(): bool
    {
        $this->bitrixUserService->Logout();
        return $this->isAuthorized();
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return $this->bitrixUserService->IsAuthorized();
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function authorize(int $id): bool
    {
        $this->bitrixUserService->Authorize($id);
        return $this->isAuthorized();
    }

    /**
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->userRepository->find($this->getCurrentUserId());
    }

    /**
     * @throws NotAuthorizedException
     * @return int
     */
    public function getCurrentUserId(): int
    {
        $id = (int)$this->bitrixUserService->GetID();
        if ($id > 0) {
            return $id;
        }
        throw new NotAuthorizedException('Trying to get user id without authorization');
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function register(User $user): bool
    {
        return true;
    }
}
