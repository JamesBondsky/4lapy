<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Offers;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\SapBundle\Exception\LogicException;
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
    public function setProperties(Collection $properties): Properties
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * @param string $code
     *
     * @throws LogicException
     * @return null|Property
     */
    public function getProperty(string $code)
    {
        $properties = $this->getProperties()->filter(function (Property $property) use ($code) {
            return $property->getCode() === $code;
        });

        if ($properties->count() > 1) {
            throw new LogicException(sprintf('Found more than one property with code %s', $code));
        }

        return $properties->current();
    }

    /**
     * @param string $code
     * @param array  $default
     *
     * @throws LogicException
     * @return Collection
     */
    public function getPropertyValues(string $code, array $default = []): Collection
    {
        $values = $default;
        $property = $this->getProperty($code);
        if ($property instanceof Property) {
            $propertyValues = $property->getValues()->map(function (PropertyValue $propertyValue) {
                return $propertyValue->getCode();
            });
            $values = $propertyValues->count() ? $propertyValues->getValues() : $values;
        }
        return new ArrayCollection($values);
    }
}
