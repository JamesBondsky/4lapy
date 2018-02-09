<?php

namespace FourPaws\UserBundle\Service;

interface UserAvatarAuthorizationInterface
{
    /**
     * Авторизация текущего пользователя под другим пользователем
     *
     * @param int $id
     *
     * @return bool
     */
    public function avatarAuthorize(int $id) : bool;
    
    /**
     * @return int
     */
    public function getAvatarHostUserId() : int;
    
    /**
     * @return bool
     */
    public function isAvatarAuthorized() : bool;
    
    /**
     * Возврат к авторизации под исходным пользователем
     *
     * @return bool
     */
    public function avatarLogout() : bool;
}
