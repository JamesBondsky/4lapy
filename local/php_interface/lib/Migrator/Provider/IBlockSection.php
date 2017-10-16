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
     * @inheritdoc
     */
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
     * @param array $data
     *
     * @return array
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \RuntimeException
     */
    public function prepareData(array $data) : array
    {
        $data = parent::prepareData($data);
        
        $data['IBLOCK_ID'] = $this->entity->getIblockId();
        
        return $data;
    }
    
    /**
     * IblockProvider constructor.
     *
     * @param string              $entityName
     * @param IBlockSectionEntity $entity
     *
     * @throws \Bitrix\Main\LoaderException
     * @throws \RuntimeException
     */
    public function __construct(string $entityName, IBlockSectionEntity $entity)
    {
        parent::__construct($entityName, $entity);
    }
}
