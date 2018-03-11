<?php

namespace FourPaws\MobileApiBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUserSession
{
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
     * @Serializer\Groups(groups={"read"})
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
     *
     * @return ApiUserSession
     */
    public function setId(int $id): ApiUserSession
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
     *
     * @return ApiUserSession
     */
    public function setRemoteAddress(string $remoteAddress): ApiUserSession
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
     *
     * @return ApiUserSession
     */
    public function setHttpClientIp($httpClientIp): ApiUserSession
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
     *
     * @return ApiUserSession
     */
    public function setHttpXForwardedFor($httpXForwardedFor): ApiUserSession
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
     *
     * @return ApiUserSession
     */
    public function setDateInsert(\DateTime $dateInsert): ApiUserSession
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
     *
     * @return ApiUserSession
     */
    public function setDateUpdate(\DateTime $dateUpdate): ApiUserSession
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
     *
     * @return ApiUserSession
     */
    public function setUserId(int $userId): ApiUserSession
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
     *
     * @return ApiUserSession
     */
    public function setUserAgent(string $userAgent): ApiUserSession
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
     * @return ApiUserSession
     */
    public function setFUserId(int $fUserId): ApiUserSession
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
     * @return ApiUserSession
     */
    public function setToken(string $token): ApiUserSession
    {
        $this->token = $token;
        return $this;
    }
}
