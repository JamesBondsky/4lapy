<?php

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ValidationException;

interface UserRegistrationProviderInterface
{
    /**
     * @param User $user
     *
     * @return bool
     * @throws ValidationException
     * @throws BitrixRuntimeException
     */
    public function register(User $user): bool;
}
