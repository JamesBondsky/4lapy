<?php

namespace FourPaws\Catalog\Model;

use FourPaws\AppBundle\Entity\BaseEntity;
use JMS\Serializer\Annotation as Serializer;

class OftenSeek extends BaseEntity
{
    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("ACTIVE")
     * @Serializer\Groups(groups={"read","update"})
     */
    protected $active = false;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NAME")
     * @Serializer\Groups(groups={"read","update"})
     */
    protected $name = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_LINK")
     * @Serializer\Groups(groups={"read"})
     */
    protected $link = '';

    /**
     * Выбран ли фильтр
     * @var bool
     */
    protected $isChosen = false;

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
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
     */
    public function setName(string $name)
    {
        $this->name = $name;
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
     */
    public function setLink(string $link)
    {
        $this->link = $link;
    }

    /**
     * @param bool $isChosen
     * @return OftenSeek
     */
    public function setChosen(bool $isChosen)
    {
        $this->isChosen = $isChosen;
    }

    /**
     * @return bool
     */
    public function isChosen(): bool
    {
        return $this->isChosen;
    }
}
