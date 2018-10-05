<?php

namespace FourPaws\Catalog\Model;


class FilterSet
{
    /** @var string $name */
    private $name;

    /** @var string $url */
    private $url;

    /** @var string $targetUrl */
    private $targetUrl;

    /** @var string $h1 */
    private $h1;

    /** @var string $title */
    private $title;

    /** @var string $description */
    private $description;

    /** @var string $seoText */
    private $seoText;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }

    /**
     * @param string $targetUrl
     */
    public function setTargetUrl(string $targetUrl): void
    {
        $this->targetUrl = $targetUrl;
    }

    /**
     * @return string
     */
    public function getH1(): string
    {
        return $this->h1;
    }

    /**
     * @param string $h1
     */
    public function setH1(string $h1): void
    {
        $this->h1 = $h1;
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
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getSeoText(): string
    {
        return $this->seoText;
    }

    /**
     * @param string $seoText
     */
    public function setSeoText(string $seoText): void
    {
        $this->seoText = $seoText;
    }

}