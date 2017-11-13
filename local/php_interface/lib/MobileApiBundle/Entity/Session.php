<?php

namespace FourPaws\MobileApiBundle\Entity;

use JMS\Serializer\Annotation as Serializer;

class Session
{
    /**
     * @var int
     * @Serializer\SerializedName("ID")
     */
    protected $id;

    /**
     * @var \DateTime
     * @Serializer\SerializedName("DATE_INSERT")
     */
    protected $dateInsert;

    /**
     * @var \DateTime
     * @Serializer\SerializedName("DATE_UPDATE")
     */
    protected $dateUpdate;

    /**
     * @var null|int
     * @Serializer\SerializedName("USER_ID")
     */
    protected $userId;

    /**
     * @var null|string
     * @Serializer\SerializedName("USER_AGENT")
     */
    protected $userAgent;

    /**
     * @var int
     * @Serializer\SerializedName("FUSER_ID")
     */
    protected $fUserId;

    /**
     * @var string
     * @Serializer\SerializedName("TOKEN")
     */
    protected $token;

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
