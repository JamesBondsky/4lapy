<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserLoginRequest
 *
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class LoginRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("login")
     * @Assert\NotBlank()
     * @var string
     */
    protected $login = '';

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("captcha_id")
     * @Assert\NotBlank()
     * @var string
     */
    protected $captchaId = '';

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("captcha_value")
     * @Assert\NotBlank()
     * @var string
     */
    protected $captchaValue = '';

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param $login
     * @return LoginRequest
     */
    public function setLogin($login): LoginRequest
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return string
     */
    public function getCaptchaId(): string
    {
        return $this->captchaId;
    }

    /**
     * @param $captchaId
     * @return LoginRequest
     */
    public function setCaptchaId($captchaId): LoginRequest
    {
        $this->captchaId = $captchaId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCaptchaValue(): string
    {
        return $this->captchaValue;
    }

    /**
     * @param $captchaValue
     * @return LoginRequest
     */
    public function setCaptchaValue($captchaValue): LoginRequest
    {
        $this->captchaValue = $captchaValue;
        return $this;
    }


}
