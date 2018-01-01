<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Offers;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Properties
 *
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("Properties")
 */
class Properties
{
    /**
     * Свойства
     *
     * @Serializer\XmlList(inline=true, entry="Property")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Offers\Property>")
     *
     * @var Collection|Property[]
     */
    protected $properties;

    /**
     * @return Collection|Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param Collection|Property[] $properties
     *
     * @return Properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
        return $this;
    }
}
