<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\Login;
use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CaptchaCreateRequest implements SimpleUnserializeRequest, PostRequest
{

    /**
     * @Serializer\SerializedName("login")
     * @Serializer\Type("string")

     * @Assert\NotBlank()

     * @var string
     */
    protected $login;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"user_registration","card_activation","edit_info"})
     * @Serializer\Type("string")
     * @var string
     */
    protected $sender = '';

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     * @return $this
     */
    public function setLogin(string $login)
    {
        $this->login = $login;
        return $this;
    }

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
}
