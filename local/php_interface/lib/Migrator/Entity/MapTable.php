<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\Validator\Foreign;

class MapTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName() : string
    {
        return 'adv_migrator_map';
    }
    
    /**
     * @return array
     */
    public static function getMap() : array
    {
        return [
            'ID'            => new IntegerField('ID', [
                'primary'      => true,
                'autocomplete' => true,
                'title'        => 'Идентификатор',
            ]),
            'ENTITY'        => new StringField('ENTITY', [
                'title'      => 'Сущность',
                'required'   => true,
                'validation' => [
                    __CLASS__,
                    'validateEntityId',
                ],
            ]),
            'INTERNAL_ID'   => new StringField('INTERNAL_ID', [
                'title' => 'Внутренний идентификатор',
            ]),
            'EXTERNAL_ID'   => new StringField('EXTERNAL_ID', [
                'title'    => 'Внешний идентификатор',
                'required' => true,
            ]),
            'LAZY'          => new BooleanField('LAZY', [
                'values'        => [
                    'N',
                    'Y',
                ],
                'default_value' => 'N',
                'title'         => 'Запись ещё не создана',
            ]),
            'ENTITY_ENTITY' => new ReferenceField('ENTITY_ENTITY',
                                                  '\FourPaws\Migrator\Entity\Entity',
                                                  ['=this.ENTITY' => 'ref.ENTITY'],
                                                  ['join_type' => 'left']),
        ];
    }
    
    /**
     * @return \Bitrix\Main\Entity\IValidator[] array
     */
    public function validateEntity() : array
    {
        return [
            /** Сущность должна существовать */
            new Foreign(EntityTable::getEntity()->getField('ENTITY')),
        ];
    }

    /**
     * @param string $external
     * @param string $entity
     *
     * @return bool
     */
    public static function isInternalEntityExists(string $external, string $entity) : bool
    {
        try {
            var_dump(self::getList([
                                       'filter' => [
                                           'EXTERNAL_ID'  => $external,
                                           '!INTERNAL_ID' => false,
                                           'ENTITY'       => $entity,
                                       ],
                                       'select' => ['ID'],
                                   ]));
        } catch (\Throwable $e) {
            var_dump($e);
        }
        
        die;

        return self::getList([
                                 'filter' => [
                                     'EXTERNAL_ID'  => $external,
                                     '!INTERNAL_ID' => false,
                                     'ENTITY'       => $entity,
                                 ],
                                 'select' => ['ID'],
                             ])->getSelectedRowsCount() === 1;
    }

    /**
     * @param string $external
     * @param string $entity
     *
     * @return string
     */
    public static function getInternalIdByExternalId(string $external, string $entity) : string
    {
        return self::getList([
                                 'filter' => [
                                     'EXTERNAL_ID' => $external,
                                     'ENTITY'      => $entity,
                                 ],
                                 'select' => ['INTERNAL_ID'],
                             ])['INTERNAL_ID'];
    }
    
    /**
     * @param string $internal
     * @param string $entity
     *
     * @return string
     */
    public static function getExternalIdByInternalId(string $internal, string $entity) : string
    {
        return self::getList([
                                 'filter' => [
                                     'EXTERNAL_ID' => $internal,
                                     'ENTITY'      => $entity,
                                 ],
                                 'select' => ['EXTERNAL_ID'],
                             ])['EXTERNAL_ID'];
    }
}