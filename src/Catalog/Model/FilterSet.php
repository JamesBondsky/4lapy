<?php

namespace FourPaws\Catalog\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class FilterSet
 *
 * @package FourPaws\Catalog\Model
 */
class FilterSet
{
    /**
     * @var int
     * @Serializer\Type("int")
     */
    protected $ID;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_NAME")
     */
    protected $UF_NAME;
    /**
     * @var string
     * @Serializer\Type("string")
     */
    protected $UF_URL;
    /**
     * @var string
     * @Serializer\Type("string")
     */
    protected $UF_TARGET_URL;
    /**
     * @var string
     * @Serializer\Type("string")
     */
    protected $UF_H1;
    /**
     * @var string
     * @Serializer\Type("string")
     */
    protected $UF_TITLE;
    /**
     * @var string
     * @Serializer\Type("string")
     */
    protected $UF_DESCRIPTION;
    /**
     * @var string
     * @Serializer\Type("string")
     */
    protected $UF_SEO_TEXT;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->UF_NAME ?? '';
    }

    /**
     * @param string $lll
     */
    public function setName(string $lll): void
    {
        $this->UF_NAME = $lll;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->UF_URL ?? '';
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->UF_URL = $url;
    }

    /**
     * @return string
     */
    public function getTargetUrl(): string
    {
        return $this->UF_TARGET_URL ?? '';
    }

    /**
     * @param string $targetUrl
     */
    public function setTargetUrl(string $targetUrl): void
    {
        $this->UF_TARGET_URL = $targetUrl;
    }

    /**
     * @return string
     */
    public function getH1(): string
    {
        return $this->UF_H1;
    }

    /**
     * @param string $h1
     */
    public function setH1(string $h1): void
    {
        $this->UF_H1 = $h1;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->UF_TITLE;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->UF_TITLE = $title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->UF_DESCRIPTION;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->UF_DESCRIPTION = $description;
    }

    /**
     * @return string
     */
    public function getSeoText(): string
    {
        return $this->UF_SEO_TEXT;
    }

    /**
     * @param string $seoText
     */
    public function setSeoText(string $seoText): void
    {
        $this->UF_SEO_TEXT = $seoText;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->ID;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id)
    {
        $this->ID = $id;

        return $this;
    }

}
