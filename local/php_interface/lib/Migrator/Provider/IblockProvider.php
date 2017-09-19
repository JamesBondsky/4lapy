<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Entity\IblockEntity;

abstract class IblockProvider extends ProviderAbstract
{
    
    /**
     * IblockProvider constructor.
     *
     * @param string                                 $entityName
     * @param \FourPaws\Migrator\Entity\IblockEntity $entity
     */
    public function __construct(string $entityName, IblockEntity $entity)
    {
        parent::__construct($entityName, $entity);
    }
}