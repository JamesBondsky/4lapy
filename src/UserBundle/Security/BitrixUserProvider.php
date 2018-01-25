<?php

namespace FourPaws\UserBundle\Security;

use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class BitrixUserProvider implements BitrixUserProviderInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritdoc
     * @return User
     */
    public function loadUserByUsername($username): User
    {
        try {
            $id = $this->userRepository->findIdentifierByRawLogin($username);
            $user = $this->userRepository->find($id);
            if ($user && $user instanceof User) {
                return $user;
            }
        } catch (\Exception $exception) {
        }
        throw new UsernameNotFoundException(sprintf('Пользователь с логином %s не найден', $username));
    }

    /**
     * @inheritdoc
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     * @return User
     */
    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', \get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @inheritdoc
     */
    public function loadUserById(int $id)
    {
        try {
            if ($user = $this->userRepository->find($id)) {
                return $user;
            }
        } catch (\Exception $exception) {
        }
        throw new UsernameNotFoundException(sprintf('Пользователь с идентификатором %s не найден', $id));
    }

    /**
     * @inheritdoc
     */
    public function supportsClass($class): bool
    {
        return $class instanceof User;
    }
}
