<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;

class CheckPushTokensRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("pushTokens")
     * @Serializer\Type("array<string>")
     * @var array<string>
     */
    protected $pushTokens = [];

    /**
     * @return array
     */
    public function getPushTokens(): array
    {
        return $this->pushTokens;
    }

    /**
     * @param array $pushTokens
     * @return CheckPushTokensRequest
     */
    public function setPushTokens(array $pushTokens): CheckPushTokensRequest
    {
        $this->pushTokens = $pushTokens;
        return $this;
    }
}
