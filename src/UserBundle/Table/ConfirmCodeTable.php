<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Table;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\Validator\Length;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class ConfirmCodeTable
 *
 * Fields:
 * <ul>
 * <li> ID string(255) mandatory
 * <li> CODE string(255) mandatory
 * <li> DATE datetime mandatory
 * </ul>
 *
 * @package FourPaws\UserBundle\Table
 **/
class ConfirmCodeTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName() : string
    {
        return '4lp_ConfirmCode';
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap() : array
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
                          'default_value' => new DateTime(),
                      ]
            ),
        ];
    }
    
    /**
     * Returns validators for ID field.
     *
     * @return array
     */
    public static function validateId() : array
    {
        return [
            new Length(null, 255),
        ];
    }
    
    /**
     * Returns validators for CODE field.
     *
     * @return array
     */
    public static function validateCode() : array
    {
        return [
            new Length(null, 255),
        ];
    }
}
