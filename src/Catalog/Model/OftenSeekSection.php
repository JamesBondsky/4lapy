<?php

namespace FourPaws\Catalog\Model;

use DateTimeImmutable;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\BitrixOrm\Model\IblockElement;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

class OftenSeekSection extends BaseEntity
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
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_COUNT")
     * @Serializer\Groups(groups={"read"})
     */
    protected $countItems = 3;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_SECTION")
     * @Serializer\Groups(groups={"read"})
     */
    protected $catalogSection = 0;


    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active ?? false;
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
        return $this->name ?? '';
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getCountItems(): int
    {
        return $this->countItems ?? 3;
    }

    /**
     * @param int $countItems
     */
    public function setCountItems(int $countItems)
    {
        $this->countItems = $countItems;
    }

    /**
     * @return int
     */
    public function getCatalogSection(): int
    {
        return $this->catalogSection ?? 0;
    }

    /**
     * @param int $catalogSection
     */
    public function setCatalogSection(int $catalogSection)
    {
        $this->catalogSection = $catalogSection;
    }
}
