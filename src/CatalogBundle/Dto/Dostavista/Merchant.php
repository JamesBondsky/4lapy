<?php

namespace FourPaws\CatalogBundle\Dto\Dostavista;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Merchant
 *
 * @package FourPaws\CatalogBundle\Dto\Dostavista
 *
 * @Serializer\XmlRoot("merchant")
 */
class Merchant
{
    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Required()
     *
     * @var int
     */
    protected $id;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $address;

    /**
     * Время работы
     *
     * @Required()
     * @Serializer\XmlList(inline=false, entry="worktime")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Dostavista\Worktime>")
     *
     * @var Worktime[]|Collection
     */
    protected $worktimes;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Merchant
     */
    public function setId(int $id): Merchant
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return Merchant
     */
    public function setAddress(string $address): Merchant
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return ArrayCollection|Worktime[]
     */
    public function getWorktimes()
    {
        return $this->worktimes;
    }

    /**
     * @param ArrayCollection|Worktime[] $worktimes
     * @return Merchant
     */
    public function setWorktimes($worktimes): Merchant
    {
        $this->worktimes = $worktimes;

        return $this;
    }
}
