<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\ChangeCardProfile;
use FourPaws\MobileApiBundle\Dto\Parts\Captcha;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeCardConfirmPinRequest implements SimpleUnserializeRequest, PostRequest
{
    use Captcha;

    /**
     * @Serializer\SerializedName("profile")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\ChangeCardProfile")
     * @Assert\NotBlank()
     * @Assert\Valid()
     * @var ChangeCardProfile
     */
    protected $profile;

    /**
     * @return ChangeCardProfile
     */
    public function getProfile(): ChangeCardProfile
    {
        return $this->profile;
    }
}
