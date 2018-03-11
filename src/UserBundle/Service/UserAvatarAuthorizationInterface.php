<?php

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Exception\AvatarSelfAuthorizationException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;

interface UserAvatarAuthorizationInterface
{
    /**
     * Авторизация текущего пользователя под другим пользователем
     *
     * @param int $id
     *
     * @throws NotAuthorizedException
     * @throws AvatarSelfAuthorizationException
     * @return bool
     */
    public function avatarAuthorize(int $id) : bool;
    
    /**
     * @return int
     */
    public function getAvatarHostUserId() : int;

    /**
     * @return int
     */
    public function getAvatarGuestUserId() : int;

    /**
     * @return bool
     */
    public function isAvatarAuthorized() : bool;

    /**
     * Возврат к авторизации под исходным пользователем
     *
     * @throws NotAuthorizedException
     * @return bool
     */
    public function avatarLogout() : bool;
}
