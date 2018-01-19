<?php

namespace FourPaws\SaleBundle\OrderStorage;

use Doctrine\Common\Collections\ArrayCollection;

abstract class Base implements StorageInterface
{
    /**
     * @var ArrayCollection
     */
    protected $fields;

    /**
     * @var ArrayCollection
     */
    protected $properties;

    protected function __construct($fields, $properties)
    {
        $this->fields = new ArrayCollection([]);
        
        foreach ($fields as $code => $value) {
            $this->fields[$code] = new Field($code, $value);
        }

        $this->properties = new ArrayCollection([]);
        foreach ($properties as $code => $value) {
            $this->properties[$code] = new Field($code, $value);
        }
    }

    protected static function init($fields, $properties)
    {
        return new static($fields, $properties);
    }

    /**
     * @{inheritdoc}
     */
    public function getFields(): ArrayCollection
    {
        return $this->fields;
    }

    /**
     * @{inheritdoc}
     */
    public function getField($code)
    {
        return $this->fields->get($code);
    }

    /**
     * @{inheritdoc}
     */
    public function setField($code, $value): bool
    {
        /** @var Field $property */
        if (!$field = $this->getField($code)) {
            $field = new Field($code, $value);
            $this->fields[$code] = $property;
        } else {
            $field->setValue($value);
        }

        return true;
    }

    /**
     * @{inheritdoc}
     */
    public function getProperties(): ArrayCollection
    {
        return $this->properties;
    }

    /**
     * @{inheritdoc}
     */
    public function getProperty($code)
    {
        return $this->properties->get($code);
    }

    /**
     * @{inheritdoc}
     */
    public function setProperty($code, $value): bool
    {
        /** @var Field $property */
        if (!$property = $this->getProperty($code)) {
            $property = new Field($code, $value);
            $this->properties[$code] = $property;
        } else {
            $property->setValue($value);
        }

        return true;
    }
}
