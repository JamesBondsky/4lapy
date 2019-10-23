<?php


namespace FourPaws\ManzanaApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

class Message
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("messageId")
     * @var string
     */
    protected $messageId;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("messageStatus")
     * @var string
     */
    protected $messageStatus;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("messageText")
     * @var string
     */
    protected $messageText;


    /**
     * @param string $messageId
     * @return Message
     */
    public function setMessageId(string $messageId): Message
    {
        $this->messageId = $messageId;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @param string $messageStatus
     * @return Message
     */
    public function setMessageStatus(string $messageStatus): Message
    {
        $this->messageStatus = $messageStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageStatus(): string
    {
        return $this->messageStatus;
    }

    /**
     * @param string $messageText
     * @return Message
     */
    public function setMessageText(string $messageText): Message
    {
        $this->messageText = $messageText;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageText(): string
    {
        return $this->messageText;
    }
}