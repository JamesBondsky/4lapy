<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class PushEvent
{
    /**
     * @var int
     * @Serializer\SerializedName("id")
     * @Serializer\Type("int")
     * @Assert\NotBlank()
     */
    protected $id;

    /**
     * @var \DateTime
     * @Serializer\SerializedName("date")
     * @Serializer\Type("DateTime<'d.m.Y'>")
     */
    protected $dateTimeExec;

    /**
     * @var string
     * @Serializer\SerializedName("text")
     * @Serializer\Type("string")
     */
    protected $text;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("read")
     */
    protected $viewed;

    /**
     * @var PushEventOptions
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\PushEventOptions")
     * @Serializer\SerializedName("options")
     */
    protected $options;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return PushEvent
     */
    public function setId(int $id): PushEvent
    {
        $this->id = $id;
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
     * @return PushEvent
     */
    public function setDateTimeExec(\DateTime $dateTimeExec): PushEvent
    {
        $this->dateTimeExec = $dateTimeExec;
        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return PushEvent
     */
    public function setText(string $text): PushEvent
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return bool
     */
    public function getViewed(): bool
    {
        return $this->viewed;
    }

    /**
     * @param bool $viewed
     * @return PushEvent
     */
    public function setViewed(bool $viewed): PushEvent
    {
        $this->viewed = $viewed;
        return $this;
    }

    /**
     * @return PushEventOptions
     */
    public function getOptions(): PushEventOptions
    {
        return $this->options;
    }

    /**
     * @param PushEventOptions $options
     * @return PushEvent
     */
    public function setOptions(PushEventOptions $options): PushEvent
    {
        $this->options = $options;
        return $this;
    }
}