<?php

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Entity\User;

interface UserRegistrationProviderInterface
{
    /**
     * @param User $user
     *
     * @return bool
     */
    public function register(User $user): bool;
}
