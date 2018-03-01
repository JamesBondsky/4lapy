<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\ValidationException;

interface UserRegistrationProviderInterface
{
    /**
     * @todo remove manzanaSave parameter
     * @todo return entity
     *
     * @param User $user
     * @param bool $manzanaSave
     * @param bool $fromBasket
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @return User
     */
    public function register(User $user, bool $manzanaSave = true, bool $fromBasket = false): User;
}
