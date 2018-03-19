<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Location;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

class MetroLine
{
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     * @var int
     */
    protected $id = 0;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @var string
     */
    protected $title = '';

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("color")
     * @var string
     */
    protected $colour = '';

    /**
     * @var Collection|MetroStation[]
     */
    protected $stations;

    public function __construct(int $id, string $title, string $colour)
    {
        $this->stations = new ArrayCollection();
        $this->id = $id;
        $this->title = $title;
        $this->colour = $colour;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getColour(): string
    {
        return $this->colour;
    }

    /**
     * @return Collection|MetroStation[]
     */
    public function getStations()
    {
        return $this->stations;
    }

    /**
     * @param MetroStation $station
     *
     * @return bool
     */
    public function addStation(MetroStation $station): bool
    {
        return $this->stations->add($station);
    }

    /**
     * @param MetroStation $station
     *
     * @return bool
     */
    public function removeStation(MetroStation $station): bool
    {
        return $this->stations->removeElement($station);
    }
}
