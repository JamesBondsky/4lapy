<?php

namespace FourPaws\Migrator\Entity;

/**
 * Class CityPhone
 *
 * @package FourPaws\Migrator\Entity
 */
class CityPhone extends HighloadBlock
{
    /**
     * @inheritdoc
     */
    public function setDefaults() : array
    {
        return [];
    }
    
    /**
     * CityPhone constructor.
     *
     * @param string $entity
     * @param string $highloadBlockCode
     */
    public function __construct($entity, $highloadBlockCode = 'Cities')
    {
        parent::__construct($entity, $highloadBlockCode);
    }
}
