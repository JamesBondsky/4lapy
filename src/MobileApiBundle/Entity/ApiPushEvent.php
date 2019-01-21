<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Entity;

use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\AppBundle\Traits\UserFieldEnumTrait;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ApiPushEvent
{
    use UserFieldEnumTrait;

    const EXEC_PENDING_CODE = 'W';
    const EXEC_SUCCESS_CODE = 'S';
    const EXEC_FAIL_CODE = 'F';

    /**
     * @var int
     * @Serializer\SerializedName("ID")
     * @Serializer\Type("int")
     * @Serializer\Groups(groups={"read"})
     * @Assert\Type(type="int",groups={"update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"update","delete"})
     */
    protected $id = 0;

    /**
     * @var string
     * @Serializer\SerializedName("PLATFORM")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"read","update","create"})
     */
    protected $platform;

    /**
     * @var string
     * @Serializer\SerializedName("PUSH_TOKEN")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"read","update","create"})
     */
    protected $pushToken;

    /**
     * @var \DateTime
     * @Serializer\SerializedName("DATE_TIME_EXEC")
     * @Serializer\Type("bitrix_date_time_object")
     * @Serializer\Groups(groups={"read","update","create"})
     */
    protected $dateTimeExec;

    /**
     * @var int
     * @Serializer\SerializedName("MESSAGE_ID")
     * @Serializer\Type("int")
     * @Serializer\Groups(groups={"read","update","create"})
     */
    protected $messageId;

    /**
     * @var string
     * @Serializer\SerializedName("MESSAGE_TEXT")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"read"})
     */
    protected $messageText;

    /**
     * @var int
     * @Serializer\SerializedName("MESSAGE_TYPE")
     * @Serializer\Type("int")
     * @Serializer\Groups(groups={"read"})
     */
    protected $messageType;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("SUCCESS_EXEC")
     * @Serializer\Groups(groups={"read","update","create"})
     */
    protected $successExec = self::EXEC_PENDING_CODE;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("VIEWED")
     * @Serializer\Groups(groups={"read","update","create"})
     */
    protected $viewed;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("SERVICE_RESPONSE_STATUS")
     * @Serializer\Groups(groups={"read","update","create"})
     */
    protected $serviceResponseStatus;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("SERVICE_RESPONSE_ERROR")
     * @Serializer\Groups(groups={"read","update","create"})
     */
    protected $serviceResponseError;

    /** @var UserFieldEnumValue $messageTypeEntity */
    private $messageTypeEntity;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     * @return ApiPushEvent
     */
    public function setPlatform(string $platform): ApiPushEvent
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * @return string
     */
    public function getPushToken(): string
    {
        return $this->pushToken;
    }

    /**
     * @param string $pushToken
     * @return ApiPushEvent
     */
    public function setPushToken(string $pushToken): ApiPushEvent
    {
        $this->pushToken = $pushToken;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateTimeExec(): \DateTime
    {
        return $this->dateTimeExec;
    }

    /**
     * @param \DateTime $dateTimeExec
     * @return ApiPushEvent
     */
    public function setDateTimeExec(\DateTime $dateTimeExec): ApiPushEvent
    {
        $this->dateTimeExec = $dateTimeExec;
        return $this;
    }

    /**
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->messageId;
    }

    /**
     * @param int $messageId
     * @return ApiPushEvent
     */
    public function setMessageId(int $messageId): ApiPushEvent
    {
        $this->messageId = $messageId;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageText(): string
    {
        return $this->messageText;
    }

    /**
     * @return int
     */
    public function getMessageType(): int
    {
        return $this->messageType;
    }

    /**
     * @return UserFieldEnumValue
     */
    public function getMessageTypeEntity()
    {
        if (!isset($this->messageTypeEntity)) {
            $this->messageTypeEntity = $this->getUserFieldEnumService()->getEnumValueEntity(
                $this->getMessageType()
            );
        }

        return $this->messageTypeEntity;
    }

    /**
     * @return string
     */
    public function getSuccessExec(): string
    {
        return $this->successExec;
    }

    /**
     * @param string $successExec
     * @return ApiPushEvent
     */
    public function setSuccessExec(string $successExec): ApiPushEvent
    {
        $this->successExec = $successExec;
        return $this;
    }

    /**
     * @return string
     */
    public function getViewed(): string
    {
        return $this->viewed;
    }

    /**
     * @param string $viewed
     * @return ApiPushEvent
     */
    public function setViewed(string $viewed): ApiPushEvent
    {
        $this->viewed = $viewed;
        return $this;
    }

    /**
     * @return int
     */
    public function getServiceResponseStatus(): int
    {
        return $this->serviceResponseStatus;
    }

    /**
     * @param int $serviceResponseStatus
     * @return ApiPushEvent
     */
    public function setServiceResponseStatus(int $serviceResponseStatus): ApiPushEvent
    {
        $this->serviceResponseStatus = $serviceResponseStatus;
        return $this;
    }

    /**
     * @return int
     */
    public function getServiceResponseError(): string
    {
        return $this->serviceResponseError;
    }

    /**
     * @param string $serviceResponseError
     * @return ApiPushEvent
     */
    public function setServiceResponseError(string $serviceResponseError): ApiPushEvent
    {
        $this->serviceResponseError = $serviceResponseError;
        return $this;
    }
}