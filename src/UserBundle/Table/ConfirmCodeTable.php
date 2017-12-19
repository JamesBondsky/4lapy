<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Table;

use Bitrix\Main;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class SmscodesTable
 *
 * Fields:
 * <ul>
 * <li> ID string(255) mandatory
 * <li> CODE string(255) mandatory
 * <li> DATE datetime mandatory
 * </ul>
 *
 * @package Bitrix\SmsCodes
 **/
class ConfirmCodeTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return '4lp_ConfirmCode';
    }
    
    /**
     * Returns entity map definition.
     *
     * @throws \Bitrix\Main\ObjectException
     * @return array
     */
    public static function getMap()
    {
        return [
            'ID'   => new StringField(
                'ID',
                [
                        'primary'  => true,
                        'required' => true,
                        'unique'   => true,
                    ]
            ),
            'CODE' => new StringField(
                'CODE',
                [
                          'required' => true,
                          'unique'   => true,
                      ]
            ),
            'DATE' => new DatetimeField(
                'DATE',
                [
                          'required'      => true,
                          'default_value' => new Main\Type\DateTime(),
                      ]
            ),
        ];
    }
    
    /**
     * Returns validators for ID field.
     *
     * @return array
     */
    public static function validateId()
    {
        return [
            new Main\Entity\Validator\Length(null, 255),
        ];
    }
    
    /**
     * Returns validators for CODE field.
     *
     * @return array
     */
    public static function validateCode()
    {
        return [
            new Main\Entity\Validator\Length(null, 255),
        ];
    }
}
