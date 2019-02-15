<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\CardProfile;
use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeCardConfirmPersonalRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("profile")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\CardProfile")
     * @Assert\NotBlank()
     * @Assert\Valid()
     * @var CardProfile
     */
    protected $profile;

    /**
     * @return CardProfile
     */
    public function getProfile(): CardProfile
    {
        return $this->profile;
    }
}
