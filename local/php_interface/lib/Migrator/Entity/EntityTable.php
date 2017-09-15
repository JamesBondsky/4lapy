<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\Validator\Length;
use Bitrix\Main\Entity\Validator\RegExp;

/**
 * Class EntityTable
 *
 * Сущности с датой последней миграции
 *
 * @package FourPaws\Migrator\Entity
 */
class EntityTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName() : string
    {
        return 'adv_migrator_entity';
    }
    
    /**
     * @return array
     */
    public static function getMap() : array
    {
        return [
            'ENTITY'    => new StringField('ENTITY', [
                'title'      => 'Код сущности',
                'unique'     => true,
                'primary'    => true,
                'validation' => [
                    __CLASS__,
                    'validateEntity',
                ],
            ]),
            'TIMESTAMP' => new DatetimeField('TIMESTAMP', [
                'title' => 'Последний успешный обмен',
            
            ]),
            'BROKEN'    => new StringField('BROKEN', [
                'title'    => 'Проблемные записи',
                'required' => true,
            ]),
        ];
    }
    
    /**@noinspection
     * @return \Bitrix\Main\Entity\IValidator[] array
     */
    public function validateEntity() : array
    {
        return [
            /**  Имя сущности должно содержать от 3 до 32 символов */
            new Length(3, 32),
            /**
             * должно начинаться с латинской буквы
             * и не должно содержать ничего, кроме латинских букв в любом регистре, _ и цифр
             */
            new RegExp('^[a-zA-Z](?>(?!\W))$'),
        ];
    }
    
    /**
     * @param string $entity
     * @param string $timestamp
     * @param string $broken
     *
     * @return \Bitrix\Main\Entity\AddResult
     */
    public static function addEntity(string $entity, string $timestamp, string $broken)
    {
        return parent::add([
                               'ENTITY'    => $entity,
                               'TIMESTAMP' => $timestamp,
                               'BROKEN'    => $broken,
                           ]);
    }
    
    /**
     * @param string $entity
     * @param string $timestamp
     * @param string $broken
     *
     * @return \Bitrix\Main\Entity\UpdateResult
     */
    public static function updateEntity(string $entity, string $timestamp, string $broken)
    {
        return parent::update($entity,
                              [
                                  'TIMESTAMP' => $timestamp,
                                  'BROKEN'    => $broken,
                              ]);
    }

    /** @noinspection PhpDocMissingReturnTagInspection */
    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public static function add(array $data)
    {
        throw new \Exception('Use addEntity');
    }
    
    /** @noinspection PhpDocMissingReturnTagInspection */
    /**
     * @param mixed $primary
     * @param array $data
     *
     * @throws \Exception
     */
    public static function update($primary, array $data)
    {
        throw new \Exception('Use updateEntity');
    }
}