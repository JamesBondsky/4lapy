<?php

namespace FourPaws\MobileApiBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Session
{
    /**
     * @var int
     * @Serializer\SerializedName("ID")
     * @Serializer\Type("int")
     * @Serializer\Groups(groups={"read"})
     * @Assert\Type(type="int",groups={"update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"update","delete"})
     */
    protected $id;

    /**
     * @var \DateTime
     * @Serializer\SerializedName("DATE_INSERT")
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\Groups(groups={"read"})
     * @Assert\DateTime(groups={"update"})
     */
    protected $dateInsert;

    /**
     * @var \DateTime
     * @Serializer\SerializedName("DATE_UPDATE")
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\Groups(groups={"read"})
     * @Assert\DateTime(groups={"update"})
     */
    protected $dateUpdate;

    /**
     * @var null|int
     * @Serializer\SerializedName("USER_ID")
     * @Serializer\Type("int")
     * @Serializer\Groups(groups={"read","update","create"})
     */
    protected $userId;

    /**
     * @var null|string
     * @Serializer\SerializedName("USER_AGENT")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"read","update","create"})
     */
    protected $userAgent;

    /**
     * @var int
     * @Serializer\SerializedName("FUSER_ID")
     * @Serializer\Type("int")
     * @Serializer\Groups(groups={"read","update","create"})
     * @Assert\GreaterThanOrEqual(value="1", groups={"update","create"})
     * @Assert\Type(type="int", groups={"update","create"})
     */
    protected $fUserId;

    /**
     * @var string
     * @Serializer\SerializedName("TOKEN")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"read","update","create"})
     * @Assert\Type(type="string",groups={"update","create"})
     * @Assert\Length(groups={"update","create"},min="32",max="32")
     */
    protected $token;

    /**
     * @Serializer\SerializedName("REMOTE_ADDR")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"read","update","create"})
     * @var string
     */
    protected $remoteAddress;

    /**
     * @Serializer\SerializedName("HTTP_CLIENT_IP")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"read","update","create"})
     * @var string
     */
    protected $httpClientIp;

    /**
     * @Serializer\SerializedName("HTTP_X_FORWARDED_FOR")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"read","update","create"})
     * @var string
     */
    protected $httpXForwardedFor;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Session
     */
    public function setId(int $id): Session
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteAddress(): string
    {
        return $this->remoteAddress;
    }

    /**
     * @param string $remoteAddress
     * @return Session
     */
    public function setRemoteAddress(string $remoteAddress): Session
    {
        $this->remoteAddress = $remoteAddress;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHttpClientIp()
    {
        return $this->httpClientIp;
    }

    /**
     * @param mixed $httpClientIp
     * @return Session
     */
    public function setHttpClientIp($httpClientIp): Session
    {
        $this->httpClientIp = $httpClientIp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHttpXForwardedFor()
    {
        return $this->httpXForwardedFor;
    }

    /**
     * @param mixed $httpXForwardedFor
     * @return Session
     */
    public function setHttpXForwardedFor($httpXForwardedFor): Session
    {
        $this->httpXForwardedFor = $httpXForwardedFor;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateInsert(): \DateTime
    {
        return $this->dateInsert;
    }

    /**
     * @param \DateTime $dateInsert
     * @return Session
     */
    public function setDateInsert(\DateTime $dateInsert): Session
    {
        $this->dateInsert = $dateInsert;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdate(): \DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * @param \DateTime $dateUpdate
     * @return Session
     */
    public function setDateUpdate(\DateTime $dateUpdate): Session
    {
        $this->dateUpdate = $dateUpdate;
        return $this;
    }

    /**
     * @return null|int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param null|int $userId
     * @return Session
     */
    public function setUserId(int $userId): Session
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param null|string $userAgent
     * @return Session
     */
    public function setUserAgent(string $userAgent): Session
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * @return int
     */
    public function getFUserId(): int
    {
        return $this->fUserId;
    }

    /**
     * @param int $fUserId
     * @return Session
     */
    public function setFUserId(int $fUserId): Session
    {
        $this->fUserId = $fUserId;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return Session
     */
    public function setToken(string $token): Session
    {
        $this->token = $token;
        return $this;
    }
}
