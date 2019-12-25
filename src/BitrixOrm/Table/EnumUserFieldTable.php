<?php

namespace FourPaws\BitrixOrm\Table;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;

class EnumUserFieldTable extends DataManager
{
    /**
     * @inheritdoc
     */
    public static function getTableName()
    {
        return 'b_user_field_enum';
    }
    
    /**
     * @inheritdoc
     */
    public static function getMap()
    {
        return [
            'ID' => new IntegerField(
                'ID',
                [
                    'primary'      => true,
                    'autocomplete' => true,
                ]
            ),
            
            'USER_FIELD_ID' => new IntegerField('USER_FIELD_ID'),
            'VALUE'         => new StringField('VALUE'),
            'DEF'           => new StringField('DEF'),
            'SORT'          => new IntegerField('SORT'),
            'XML_ID'        => new StringField('XML_ID'),
        ];
    }
}
