<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserLoginRequest
 *
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class LoginRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Assert\Collection(
     *     fields = {
     *         "login" = @Assert\NotBlank,
     *         "password" = {
     *             @Assert\NotBlank(),
     *         }
     *     },
     *     allowMissingFields = false
     * )
     *
     * @var array
     * @Serializer\Type("array")
     * @Serializer\SerializedName("user_login_info")
     */
    protected $userLoginInfo = [
        'login'    => '',
        'password' => '',
    ];

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->userLoginInfo['login'];
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->userLoginInfo['password'];
    }
}
