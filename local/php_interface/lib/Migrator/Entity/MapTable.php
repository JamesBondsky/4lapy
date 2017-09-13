<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
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
            'ID'          => new IntegerField('ID', [
                'primary'      => true,
                'autocomplete' => true,
                'title'        => 'Идентификатор',
            ]),
            'ENTITY_ID'   => new IntegerField('ENTITY_ID', [
                'title'      => 'Id сущности',
                'required'   => true,
                'validation' => [
                    __CLASS__,
                    'validateEntityId',
                ],
            ]),
            'INTERNAL_ID' => new IntegerField('INTERNAL_ID', [
                'title' => 'Внутренний идентификатор',
            ]),
            'EXTERNAL_ID' => new IntegerField('EXTERNAL_ID', [
                'title'    => 'Внешний идентификатор',
                'required' => true,
            ]),
            'LAZY'        => new BooleanField('LAZY', [
                'values'        => [
                    'N',
                    'Y',
                ],
                'default_value' => 'N',
                'title'         => 'Запись ещё не создана',
            ]),
            'ENTITY'      => new ReferenceField('ENTITY',
                                                '\FourPaws\Migrator\Entity\Entity',
                                                ['=this.ENTITY_ID' => 'ref.ENTITY'],
                                                ['join_type' => 'left']),
        ];
    }
    
    /**
     * @return \Bitrix\Main\Entity\IValidator[] array
     */
    public function validateEntity() : array
    {
        return [
            /** ID сущности должен существовать */
            new Foreign(EntityTable::getEntity()->getField('ID')),
        ];
    }
}