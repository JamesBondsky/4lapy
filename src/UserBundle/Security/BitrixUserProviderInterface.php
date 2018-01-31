<?php

namespace FourPaws\UserBundle\Security;

use FourPaws\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface BitrixUserProviderInterface extends UserProviderInterface
{
    /**
     * @param int $id
     *
     * @throws UsernameNotFoundException if the user is not found
     * @return User
     */
    public function loadUserById(int $id);
}
