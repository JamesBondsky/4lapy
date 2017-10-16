<?php

namespace FourPaws\ProductAutoSort\Table;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Psr\Log\LoggerInterface;

class ElementPropertyConditionTable extends DataManager
{
    /**
     * @var LoggerInterface
     */
    protected static $logger;

    /**
     * @inheritdoc
     */
    public static function getTableName()
    {
        return '4lp_elem_prop_cond';
    }

    /**
     * @return array
     */
    public static function getMap()
    {
        return [
            'ID'             => new IntegerField(
                'ID',
                [
                    'primary'      => true,
                    'autocomplete' => true,
                ]
            ),

            //ID пользовательского свойства
            'UF_ID'          => new IntegerField('UF_ID', ['required' => true]),

            //ID раздела, в котором используется кастомное свойство "Условие для свойств элемента"
            'SECTION_ID'     => new IntegerField('SECTION_ID', ['required' => true]),

            //ID свойства элемента, которое надо проверить.
            'PROPERTY_ID'    => new IntegerField('PROPERTY_ID', ['required' => true]),

            //Значение свойства. Если null - символизирует незаполненное свойство.
            'PROPERTY_VALUE' => new StringField('PROPERTY_VALUE', ['default_value' => null, 'size' => 255]),
        ];
    }
}
