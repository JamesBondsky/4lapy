<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Main\Loader;
use FourPaws\Migrator\Entity\IBlock as IBlockEntity;

abstract class Sale extends ProviderAbstract
{
    
    /**
     * Sale constructor.
     *
     * @param string                           $entityName
     * @param \FourPaws\Migrator\Entity\IBlock $entity
     *
     * @throws \Bitrix\Main\LoaderException
     * @throws \RuntimeException
     */
    public function __construct(string $entityName, IBlockEntity $entity)
    {
        Loader::includeModule('sale');
        
        parent::__construct($entityName, $entity);
    }
}