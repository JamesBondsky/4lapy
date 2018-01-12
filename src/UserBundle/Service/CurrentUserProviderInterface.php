<?php

namespace FourPaws\UserBundle\Service;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Interface CurrentUserProviderInterface
 * @package FourPaws\UserBundle\Service
 */
interface CurrentUserProviderInterface
{
    /**
     * @return User
     */
    public function getCurrentUser(): User;

    /**
     * @return int
     */
    public function getCurrentUserId(): int;

    /**
     * @return UserRepository
     */
    public function getUserRepository(): UserRepository;

    /**
     * @param Client $client
     * @param User|null $user
     *
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ConstraintDefinitionException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     */
    public function setClientPersonalDataByCurUser(&$client, User $user = null);

    /**
     *
     *
     * @return int
     */
    public function getCurrentFUserId(): int;
}
