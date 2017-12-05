<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\Entity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CaptchaCreateRequest
{
    use Entity;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"user_registration","card_activation","edit_info"})
     * @Serializer\Type("string")
     * @Serializer\SerializedName("sender")
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
     *
     * @return CaptchaCreateRequest
     */
    public function setSender(string $sender): CaptchaCreateRequest
    {
        $this->sender = $sender;
        return $this;
    }
}
