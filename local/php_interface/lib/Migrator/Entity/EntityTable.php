<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\Validator\Length;
use Bitrix\Main\Entity\Validator\RegExp;
use Bitrix\Main\Type\DateTime;

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
                'title' => 'Проблемные записи',
            ]),
        ];
    }
    
    /**
     * @throws \Bitrix\Main\ArgumentTypeException
     *
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
            new RegExp('~^[a-zA-Z]\w+$~'),
        ];
    }
    
    /**
     * @param string $entity
     * @param int    $timestamp
     * @param string $broken
     *
     * @return \Bitrix\Main\Entity\AddResult
     * @throws \Exception
     */
    public static function addEntity(string $entity, int $timestamp = 0, string $broken = '') : AddResult
    {
        $fields = ['ENTITY' => $entity];
        
        if ($timestamp) {
            $fields['TIMESTAMP'] = DateTime::createFromTimestamp($timestamp);
        }
        
        if ($broken) {
            $fields['BROKEN'] = $broken;
        }
        
        return parent::add($fields);
    }
    
    /**
     * @param string $entity
     * @param int    $timestamp
     *
     * @return \Bitrix\Main\Entity\UpdateResult
     * @throws \Exception
     */
    public static function updateEntity(string $entity, int $timestamp) : \Bitrix\Main\Entity\UpdateResult
    {
        if (!self::getByPrimary($entity)->fetch()) {
            parent::add(['ENTITY' => $entity]);
        }
        
        $fields = ['TIMESTAMP' => DateTime::createFromTimestamp($timestamp),];
        $result = parent::update($entity, $fields);
        
        if (!$result->isSuccess()) {
            /**
             * @todo добавить нормальный Exception
             */
            throw new \Exception("Entity update error: \n" . implode("\n", $result->getErrors()));
        }
        
        return $result;
    }
    
    /** @noinspection PhpDocMissingReturnTagInspection */
    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public static function add(array $data)
    {
        /**
         * @todo добавить нормальный Exception
         */
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
        /**
         * @todo добавить нормальный Exception
         */
        throw new \Exception('Use updateEntity');
    }
    
    /**
     * @param string $entity
     * @param string $primary
     *
     * @return \Bitrix\Main\Entity\UpdateResult
     * @throws \Exception
     */
    public static function pushBroken(string $entity, string $primary) : \Bitrix\Main\Entity\UpdateResult
    {
        $entityEntity = self::getByPrimary($entity)->fetch();
        
        if (!$entityEntity) {
            /**
             * @todo добавить нормальный Exception
             */
            throw new \Exception('Wrong entity');
        }
        
        $broken = $entityEntity['BROKEN'] ? array_unique(array_merge(self::decodeBroken($entityEntity['BROKEN']),
                                                                     [$primary])) : [$primary];
        
        return parent::update($entity, ['BROKEN' => self::encodeBroken($broken)]);
    }
    
    /**
     * @param string $entity
     * @param string $primary
     *
     * @return \Bitrix\Main\Entity\UpdateResult
     * @throws \Exception
     */
    public function popBroken(string $entity, string $primary) : \Bitrix\Main\Entity\UpdateResult
    {
        $entityEntity = self::getByPrimary($entity)->fetch();
        
        if (!$entityEntity) {
            /**
             * @todo добавить нормальный Exception
             */
            throw new \Exception('Wrong entity');
        }
        
        $broken = array_diff(self::decodeBroken($entityEntity['BROKEN']), [$primary]);
        
        return parent::update($entity, ['BROKEN' => $broken ? self::encodeBroken($broken) : '']);
    }
    
    /**
     * @param string $broken
     *
     * @return array
     */
    public static function decodeBroken(string $broken) : array
    {
        return json_decode($broken, JSON_OBJECT_AS_ARRAY | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_APOS);
    }
    
    /**
     * @param array $broken
     *
     * @return string
     */
    public static function encodeBroken(array $broken) : string
    {
        return json_encode($broken, JSON_OBJECT_AS_ARRAY | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_APOS);
    }
}