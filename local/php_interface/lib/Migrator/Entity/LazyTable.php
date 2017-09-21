<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\StringField;

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
            'ENTITY_FROM'        => new StringField('ENTITY_FROM', [
                'title'    => 'Ссылающаяся сущность',
                'required' => true,
            ]),
            'ENTITY_TO'        => new StringField('ENTITY_TO', [
                'title'    => 'Сущность, на которую ссылаются',
                'required' => true,
            ]),
            'FIELD'        => new StringField('FIELD', [
                'title'    => 'Поле ссылки',
                'required' => true,
            ]),
            'INTERNAL_ID'   => new StringField('INTERNAL_ID', [
                'title' => 'Внутренний идентификатор сущности from',
                'required' => true,
            ]),
            'EXTERNAL_ID'   => new StringField('EXTERNAL_ID', [
                'title'    => 'Внешний идентификатор сущности to',
                'required' => true,
            ])
        ];
    }
    
}