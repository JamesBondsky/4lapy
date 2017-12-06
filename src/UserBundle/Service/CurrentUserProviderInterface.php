<?php

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Entity\User;

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
}
