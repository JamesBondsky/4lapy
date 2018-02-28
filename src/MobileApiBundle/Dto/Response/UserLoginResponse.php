<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\User;
use JMS\Serializer\Annotation as Serializer;

class UserLoginResponse
{
    /**
     * @Serializer\SerializedName("user")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\User")
     * @var User
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->setUser($user);
    }

    /**
     * @param User $user
     *
     * @return UserLoginResponse
     */
    public function setUser(User $user): UserLoginResponse
    {
        $this->user = $user;
        return $this;
    }
}
