<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Iblock\SectionTable;
use FourPaws\Migrator\Entity\IBlockSection as IBlockSectionEntity;

/**
 * Class IBlockSection
 *
 * @package FourPaws\Migrator\Provider
 */
abstract class IBlockSection extends IBlock
{
    /**
     * @var IBlockSectionEntity
     */
    protected $entity;
    
    public function getMap() : array
    {
        $map = array_diff(array_keys(array_filter(SectionTable::getMap(), self::getScalarEntityMapFilter())),
                          [
                              $this->entity->getPrimary(),
                              'IBLOCK_SECTION_ID',
                              'CREATED_BY',
                              'MODIFIED_BY',
                          ]);
        
        $map = array_combine($map, $map);
        
        $map = array_merge($map,
                           [
                               $this->entityName . '.IBLOCK_SECTION_ID' => 'IBLOCK_SECTION_ID',
                               'user.CREATED_BY'                        => 'CREATED_BY',
                               'user.MODIFIED_BY'                       => 'MODIFIED_BY',
                           ]);
        
        return $map;
    }
    
    /**
     * IblockProvider constructor.
     *
     * @param string              $entityName
     * @param IBlockSectionEntity $entity
     */
    public function __construct(string $entityName, IBlockSectionEntity $entity)
    {
        parent::__construct($entityName, $entity);
    }
}