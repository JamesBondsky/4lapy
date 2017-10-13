<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use FourPaws\Migrator\Entity\IBlock as IBlockEntity;

abstract class IBlock extends ProviderAbstract
{
    /**
     * @var \FourPaws\Migrator\Entity\IBlock
     */
    protected $entity;
    
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
     * @param string                           $entityName
     * @param \FourPaws\Migrator\Entity\IBlock $entity
     *
     * @throws \Bitrix\Main\LoaderException
     * @throws \RuntimeException
     */
    public function __construct(string $entityName, IBlockEntity $entity)
    {
        Loader::includeModule('iblock');
        
        parent::__construct($entityName, $entity);
    }
}