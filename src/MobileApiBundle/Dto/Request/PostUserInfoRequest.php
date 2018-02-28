<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\User;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class PostUserInfoRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("user")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\User")
     * @Assert\Valid()
     * @var User
     */
    protected $user;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
