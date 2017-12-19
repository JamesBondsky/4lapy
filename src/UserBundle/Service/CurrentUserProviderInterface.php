<?php

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Repository\UserRepository;

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
    public function getUserRepository() : UserRepository;
}
