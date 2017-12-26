<?php

namespace FourPaws\SapBundle\Dto\In;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Properties
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("Properties")
 */
class Properties
{
    /**
     * Свойства
     *
     * @Serializer\XmlList(inline=true, entry="Property")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Property>")
     *
     * @var Collection|Property[]
     */
    protected $properties;
}
