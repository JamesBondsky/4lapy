<?php

namespace FourPaws\Migrator\Entity;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\AddResult;
use FourPaws\Migrator\Factory;

class LazyTable extends DataManager
{
    protected static $entityStack;
    
    /**
     * @return string
     */
    public static function getTableName() : string
    {
        return 'adv_migrator_lazy';
    }
    
    /**
     * @return array
     */
    public static function getMap() : array
    {
        return [
            'ENTITY_FROM' => new StringField('ENTITY_FROM', [
                'title'    => 'Ссылающаяся сущность',
                'required' => true,
                'primary'  => true,
            ]),
            'ENTITY_TO'   => new StringField('ENTITY_TO', [
                'title'    => 'Сущность, на которую ссылаются',
                'required' => true,
                'primary'  => true,
            ]),
            'FIELD'       => new StringField('FIELD', [
                'title'    => 'Поле ссылки',
                'required' => true,
                'primary'  => true,
            ]),
            'INTERNAL_ID' => new StringField('INTERNAL_ID', [
                'title'    => 'Внутренний идентификатор сущности from',
                'required' => true,
                'primary'  => true,
            ]),
            'EXTERNAL_ID' => new StringField('EXTERNAL_ID', [
                'title'    => 'Внешний идентификатор сущности to',
                'required' => true,
            ]),
        ];
    }
    
    /**
     * @param array $data
     *
     * @return \Bitrix\Main\Entity\AddResult
     *
     * @throws \Exception
     */
    public static function add(array $data) : AddResult
    {
        $primary = [
            'ENTITY_FROM' => $data['ENTITY_FROM'],
            'ENTITY_TO'   => $data['ENTITY_TO'],
            'FIELD'       => $data['FIELD'],
            'INTERNAL_ID' => $data['INTERNAL_ID'],
        ];
        
        if (self::getByPrimary($primary)->getSelectedRowsCount() === 1) {
            return new AddResult();
        }
        
        return parent::add($data);
    }
    
    /**
     * @param string $entity
     * @param array  $idList
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function getLazyByIdList(string $entity, array $idList) : array
    {
        return self::getList([
                                 'filter' => [
                                     'ENTITY_TO'    => $entity,
                                     '@EXTERNAL_ID' => $idList,
                                 ],
                             ])->fetchAll();
    }
    
    /**
     * @param string $entity
     * @param array  $idList
     *
     * @throws \Exception
     */
    public static function handleLazy(string $entity, array $idList)
    {
        $lazyList = self::getLazyByIdList($entity, $idList);
        
        foreach ($lazyList as $lazyElement) {
            $targetEntity = self::getEntityByName($lazyElement['ENTITY_FROM']);
            
            try {
                $internalId =
                    MapTable::getInternalIdByExternalId($lazyElement['EXTERNAL_ID'], $lazyElement['ENTITY_TO']);
                
                $targetEntity->setFieldValue($lazyElement['FIELD'], $lazyElement['INTERNAL_ID'], $internalId);
                
                self::delete([
                                 'ENTITY_FROM' => $lazyElement['ENTITY_FROM'],
                                 'ENTITY_TO'   => $lazyElement['ENTITY_TO'],
                                 'FIELD'       => $lazyElement['FIELD'],
                                 'INTERNAL_ID' => $lazyElement['INTERNAL_ID'],
                             ]);
            } catch (\Throwable $e) {
                LoggerFactory::create('migrator_lazy')->error($e->getMessage());
            }
        }
    }
    
    /**
     * @param string $entityName
     *
     * @return \FourPaws\Migrator\Entity\EntityInterface
     *
     * @throws \Exception
     */
    public static function getEntityByName(string $entityName) : EntityInterface
    {
        if (!self::$entityStack[$entityName]) {
            self::$entityStack[$entityName] = (new Factory())->getEntityByEntityName($entityName);
        }
        
        return self::$entityStack[$entityName];
    }
}