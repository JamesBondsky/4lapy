<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ValidationException;

interface UserRegistrationProviderInterface
{
    /**
     * @param User $user
     * @param bool $manzanaSave
     *
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function register(User $user, bool $manzanaSave = true): bool;
}
