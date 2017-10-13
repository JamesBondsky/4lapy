<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Main\Loader;
use FourPaws\Migrator\Entity\AbstractEntity;

abstract class Sale extends ProviderAbstract
{
    
    /**
     * Sale constructor.
     *
     * @param string                                   $entityName
     * @param \FourPaws\Migrator\Entity\AbstractEntity $entity
     *
     * @throws \Bitrix\Main\LoaderException
     * @throws \RuntimeException
     */
    public function __construct(string $entityName, AbstractEntity $entity)
    {
        Loader::includeModule('sale');
        
        parent::__construct($entityName, $entity);
    }
}
