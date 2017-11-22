<?php

namespace FourPaws\MobileApiBundle\Dto;

use JMS\Serializer\Annotation as Serializer;

class Error
{
    /**
     * @Serializer\SerializedName("code")
     * @Serializer\Type("int")
     * @var int
     */
    protected $code;

    /**
     * @Serializer\SerializedName("title")
     * @Serializer\Type("string")
     * @var string
     */
    protected $title;

    public function __construct(int $code, string $title = null)
    {
        $this->setCode($code);
        $this->setTitle($title ?: '');
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return Error
     */
    public function setCode(int $code): Error
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Error
     */
    public function setTitle(string $title): Error
    {
        $this->title = $title;
        return $this;
    }
}
