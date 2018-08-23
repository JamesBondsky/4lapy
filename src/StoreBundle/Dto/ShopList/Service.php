<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Dto\ShopList;

use JMS\Serializer\Annotation as Serializer;

class Service
{
    /**
     * @Serializer\SerializedName("ID")
     * @Serializer\Type("int")
     * @Serializer\SkipWhenEmpty()
     *
     * @var int
     */
    protected $id;

    /**
     * @Serializer\SerializedName("UF_NAME")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $name;

    /**
     * @Serializer\SerializedName("UF_SORT")
     * @Serializer\Type("int")
     * @Serializer\SkipWhenEmpty()
     *
     * @var int
     */
    protected $sort;

    /**
     * @Serializer\SerializedName("UF_XML_ID")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $xmlId;

    /**
     * @Serializer\SerializedName("UF_LINK")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $link;

    /**
     * @Serializer\SerializedName("UF_DESCRIPTION")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $description;

    /**
     * @Serializer\SerializedName("UF_FULL_DESCRIPTION")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $fullDescription;

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
     * @return Service
     */
    public function setId(int $id): Service
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Service
     */
    public function setName(string $name): Service
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     *
     * @return Service
     */
    public function setSort(int $sort): Service
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->xmlId;
    }

    /**
     * @param string $xmlId
     *
     * @return Service
     */
    public function setXmlId(string $xmlId): Service
    {
        $this->xmlId = $xmlId;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return Service
     */
    public function setLink(string $link): Service
    {
        $this->link = $link;

        return $this;
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
     *
     * @return Service
     */
    public function setDescription(string $description): Service
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullDescription(): string
    {
        return $this->fullDescription;
    }

    /**
     * @param string $fullDescription
     *
     * @return Service
     */
    public function setFullDescription(string $fullDescription): Service
    {
        $this->fullDescription = $fullDescription;

        return $this;
    }
}
