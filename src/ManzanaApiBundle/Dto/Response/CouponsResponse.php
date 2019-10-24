<?php

namespace FourPaws\ManzanaApiBundle\Dto\Response;

use Symfony\Component\HttpFoundation\Response;

class CouponsResponse extends Response
{
    /**
     * @Serializer\Type("array<FourPaws\ManzanaApiBundle\Dto\Object\Message>")
     * @Serializer\SerializedName("messages")
     * @var array<FourPaws\ManzanaApiBundle\Dto\Object\Message>
     */
    protected $messages = [];

    /**
     * @param array $messages
     * @return CouponsResponse
     */
    public function setMessages(array $messages): CouponsResponse
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}