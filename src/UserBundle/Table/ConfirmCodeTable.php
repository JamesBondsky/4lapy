<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Table;

use Bitrix\Main\ArgumentTypeException;
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
 * <li> TYPE string(50) mandatory
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
    public static function getTableName(): string
    {
        return '4lp_confirm_code';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws \Bitrix\Main\ObjectException
     */
    public static function getMap(): array
    {
        return [
            'ID'   => new StringField(
                'ID',
                [
                    'primary'  => true,
                    'required' => true,
                    'unique'   => true,
                    'validation' => array(__CLASS__, 'validateId'),
                ]
            ),
            'CODE' => new StringField(
                'CODE',
                [
                    'required' => true,
                    'unique'   => true,
                    'validation' => array(__CLASS__, 'validateCode'),
                ]
            ),
            'TYPE' => new StringField(
                'TYPE',
                [
                    'required' => true,
                    'default_value' => 'sms',
                    'validation' => array(__CLASS__, 'validateType'),
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
     * @throws ArgumentTypeException
     */
    public static function validateId(): array
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for CODE field.
     *
     * @return array
     * @throws ArgumentTypeException
     */
    public static function validateCode(): array
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for TYPE field.
     *
     * @return array
     * @throws ArgumentTypeException
     */
    public static function validateType(): array
    {
        return [
            new Length(null, 50),
        ];
    }
}
