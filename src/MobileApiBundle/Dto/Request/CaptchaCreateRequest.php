<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;

class CaptchaCreateRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"user_registration","card_activation","edit_info"})
     * @Serializer\Type("string")
     * @var string
     */
    protected $sender = '';

    /**
     * @Assert\NotBlank()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("login")
     * @var string
     */
    protected $login = '';

    /**
     * @return string
     */
    public function getSender(): string
    {
        return $this->sender;
    }

    /**
     * @param string $sender
     * @return CaptchaCreateRequest
     */
    public function setSender(string $sender): CaptchaCreateRequest
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     * @return CaptchaCreateRequest
     */
    public function setLogin(string $login): CaptchaCreateRequest
    {
        $this->login = $login;
        return $this;
    }
}
