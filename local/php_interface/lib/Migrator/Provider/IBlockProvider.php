<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use FourPaws\Migrator\Entity\IblockEntity;

abstract class IBlockProvider extends ProviderAbstract
{
    public function getMap() : array
    {
        $map = array_diff(array_keys(array_filter(ElementTable::getMap(), self::getScalarEntityMapFilter())),
                          [
                              $this->entity->getPrimary(),
                              'IBLOCK_SECTION_ID',
                              'CREATED_BY',
                              'MODIFIED_BY',
                          ]);
        
        $map = array_combine($map, $map);
        
        $map = array_merge($map,
                           [
                               'user.CREATED_BY'  => 'CREATED_BY',
                               'user.MODIFIED_BY' => 'MODIFIED_BY',
                           ]);

        return $map;
    }
    
    /**
     * IblockProvider constructor.
     *
     * @param string                                 $entityName
     * @param \FourPaws\Migrator\Entity\IblockEntity $entity
     */
    public function __construct(string $entityName, IblockEntity $entity)
    {
        Loader::includeModule('iblock');
        
        parent::__construct($entityName, $entity);
    }
    
    public function prepareData(array $data)
    {
        return parent::prepareData($data);
    }
}