<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Iblock\ElementTable;
use FourPaws\Migrator\Entity\IblockEntity;

abstract class IblockProvider extends ProviderAbstract
{
    public function getMap() : array
    {
        $map = array_diff(array_keys(array_filter(ElementTable::getMap(), self::getScalarEntityMapFilter())),
                          [
                              $this->entity->getPrimary(),
                              'IBLOCK_SECTION_ID',
                          ]);
        
        return array_combine($map, $map);
    }
    
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