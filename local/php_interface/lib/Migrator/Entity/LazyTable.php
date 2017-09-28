<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\AddResult;

class LazyTable extends DataManager
{
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
     */
    public static function add(array $data)
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
     */
    public static function getLazyByIdList(string $entity, array $idList) :array {
        return self::getList(['filter' => ['ENTITY_TO' => $entity, '@EXTERNAL_ID' => $idList]])->fetchAll();
    }
    
    /**
     * @param string $entity
     * @param array  $idList
     */
    public static function handleLazy(string $entity, array $idList) {
        $lazyCollection = LazyTable::getLazyByIdList($entity, $idList);

        var_dump($lazyCollection);
    }
}