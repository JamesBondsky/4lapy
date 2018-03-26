<?php

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Repository\UserRepository;

/**
 * Interface CurrentUserProviderInterface
 * @package FourPaws\UserBundle\Service
 */
interface CurrentUserProviderInterface
{
    /**
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @return User
     */
    public function getCurrentUser(): User;

    /**
     * @throws NotAuthorizedException
     * @return int
     */
    public function getCurrentUserId(): int;

    /**
     * @return UserRepository
     */
    public function getUserRepository(): UserRepository;

    /**
     * @return int
     */
    public function getCurrentFUserId(): int;

    /**
     * @param int $id
     *
     * @throws InvalidIdentifierException
     * @throws NotAuthorizedException
     * @return array
     */
    public function getUserGroups(int $id = 0): array;
}
