<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\Login;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CaptchaCreateRequest implements SimpleUnserializeRequest, PostRequest
{
    use Login;
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
