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
     * @param array $data
     *
     * @return array
     */
    public function prepareData(array $data)
    {
        $data = parent::prepareData($data);
        
        foreach ($data as $k => $v) {
            if (strpos($k, 'PROPERTY_') === 0) {
                $data['PROPERTY_VALUES'][str_replace('PROPERTY_', '', $k)] = $v;
                
                unset($data[$k]);
            }
        }

        $data['IBLOCK_ID'] = $this->entity->getIblockId();

        return $data;
    }
    
    /**
     * IblockProvider constructor.
     *
     * @param string                           $entityName
     * @param \FourPaws\Migrator\Entity\IBlock $entity
     */
    public function __construct(string $entityName, IBlockEntity $entity)
    {
        Loader::includeModule('iblock');
        
        parent::__construct($entityName, $entity);
    }
}