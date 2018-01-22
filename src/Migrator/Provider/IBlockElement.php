<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use FourPaws\Migrator\Entity\IBlockElement as IBlockElementEntity;
use RuntimeException;

/**
 * Class IBlockElement
 *
 * @property $entity IBlockElementEntity
 *
 * @package FourPaws\Migrator\Provider
 */
abstract class IBlockElement extends IBlock
{
    /**
     * @inheritdoc
     */
    public function getMap() : array {
        $map = array_diff(array_keys(array_filter(ElementTable::getMap(), self::getScalarEntityMapFilter())), [
                                                                                                                $this->entity->getPrimary(),
                                                                                                                'CREATED_BY',
                                                                                                                'MODIFIED_BY',
                                                                                                            ]);
        
        $map = array_combine($map, $map);
        
        $map = array_merge($map, [
                                   'user.CREATED_BY'  => 'CREATED_BY',
                                   'user.MODIFIED_BY' => 'MODIFIED_BY',
                                   'SECTIONS'         => 'SECTIONS',
                               ]);
        
        return $map;
    }
    
    /**
     * @param array $data
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws LoaderException
     * @throws RuntimeException
     */
    public function prepareData(array $data) : array {
        $data = parent::prepareData($data);
        
        $data['DETAIL_TEXT_TYPE'] = $data['DETAIL_TEXT_TYPE'] ?: 'html';
        
        foreach ($data as $k => $v) {
            if (strpos($k, 'PROPERTY_') === 0) {
                $code                           = str_replace('PROPERTY_', '', $k);
                $data['PROPERTY_VALUES'][$code] = $v;
                
                unset($data[$k]);
            }
        }
        
        $data['IBLOCK_ID'] = $this->entity->getIblockId();
        
        return $data;
    }
    
    /**
     * IblockProvider constructor.
     *
     * @param IBlockElementEntity $entity
     *
     * @throws LoaderException
     * @throws RuntimeException
     */
    public function __construct(IBlockElementEntity $entity) {
        parent::__construct($entity);
    }
}
