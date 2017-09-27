<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Iblock\ElementTable;
use FourPaws\Migrator\Entity\IBlockElement as IBlockElementEntity;

/**
 * Class IBlockElement
 *
 * @package FourPaws\Migrator\Provider
 */
abstract class IBlockElement extends IBlock
{
    /**
     * @var IBlockElementEntity
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
                               $this->entityName . '_section.IBLOCK_SECTION_ID' => 'IBLOCK_SECTION_ID',
                               'user.CREATED_BY'                                => 'CREATED_BY',
                               'user.MODIFIED_BY'                               => 'MODIFIED_BY',
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
     * @param string              $entityName
     * @param IBlockElementEntity $entity
     */
    public function __construct(string $entityName, IBlockElementEntity $entity)
    {
        parent::__construct($entityName, $entity);
    }
}