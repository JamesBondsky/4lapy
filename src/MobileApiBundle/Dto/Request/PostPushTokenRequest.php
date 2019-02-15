<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class PostPushTokenRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"ios","android"})
     * @Serializer\SerializedName("platform")
     * @Serializer\Type("string")
     * @var string
     */
    protected $platform;

    /**
     * @Assert\NotBlank()
     * @Serializer\SerializedName("push_token")
     * @Serializer\Type("string")
     * @var string
     */
    protected $pushToken;

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * @return string
     */
    public function getPushToken(): string
    {
        return $this->pushToken;
    }
}
