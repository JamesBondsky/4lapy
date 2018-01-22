<?php
namespace FourPaws\Helpers\Table;

use Bitrix\Main,
    Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\Validator\Length;

Loc::loadMessages(__FILE__);

/**
 * Class FormTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime optional
 * <li> NAME string(255) mandatory
 * <li> SID string(50) mandatory
 * <li> BUTTON string(255) optional
 * <li> C_SORT int optional default 100
 * <li> FIRST_SITE_ID string(2) optional
 * <li> IMAGE_ID int optional
 * <li> USE_CAPTCHA bool optional default 'N'
 * <li> DESCRIPTION string optional
 * <li> DESCRIPTION_TYPE enum ('text', 'html') optional default 'html'
 * <li> FORM_TEMPLATE string optional
 * <li> USE_DEFAULT_TEMPLATE bool optional default 'Y'
 * <li> SHOW_TEMPLATE string(255) optional
 * <li> MAIL_EVENT_TYPE string(50) optional
 * <li> SHOW_RESULT_TEMPLATE string(255) optional
 * <li> PRINT_RESULT_TEMPLATE string(255) optional
 * <li> EDIT_RESULT_TEMPLATE string(255) optional
 * <li> FILTER_RESULT_TEMPLATE string optional
 * <li> TABLE_RESULT_TEMPLATE string optional
 * <li> USE_RESTRICTIONS bool optional default 'N'
 * <li> RESTRICT_USER int optional
 * <li> RESTRICT_TIME int optional
 * <li> RESTRICT_STATUS string(255) optional
 * <li> STAT_EVENT1 string(255) optional
 * <li> STAT_EVENT2 string(255) optional
 * <li> STAT_EVENT3 string(255) optional
 * </ul>
 *
 * @package Bitrix\Form
 **/

class FormTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName() : string
    {
        return 'b_form';
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap() : array
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('FORM_ENTITY_ID_FIELD'),
            ),
            'TIMESTAMP_X' => array(
                'data_type' => 'datetime',
                'title' => Loc::getMessage('FORM_ENTITY_TIMESTAMP_X_FIELD'),
            ),
            'NAME' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateName'),
                'title' => Loc::getMessage('FORM_ENTITY_NAME_FIELD'),
            ),
            'SID' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateSid'),
                'title' => Loc::getMessage('FORM_ENTITY_SID_FIELD'),
            ),
            'BUTTON' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateButton'),
                'title' => Loc::getMessage('FORM_ENTITY_BUTTON_FIELD'),
            ),
            'C_SORT' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('FORM_ENTITY_C_SORT_FIELD'),
            ),
            'FIRST_SITE_ID' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateFirstSiteId'),
                'title' => Loc::getMessage('FORM_ENTITY_FIRST_SITE_ID_FIELD'),
            ),
            'IMAGE_ID' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('FORM_ENTITY_IMAGE_ID_FIELD'),
            ),
            'USE_CAPTCHA' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('FORM_ENTITY_USE_CAPTCHA_FIELD'),
            ),
            'DESCRIPTION' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('FORM_ENTITY_DESCRIPTION_FIELD'),
            ),
            'DESCRIPTION_TYPE' => array(
                'data_type' => 'enum',
                'values' => array('text', 'html'),
                'title' => Loc::getMessage('FORM_ENTITY_DESCRIPTION_TYPE_FIELD'),
            ),
            'FORM_TEMPLATE' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('FORM_ENTITY_FORM_TEMPLATE_FIELD'),
            ),
            'USE_DEFAULT_TEMPLATE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('FORM_ENTITY_USE_DEFAULT_TEMPLATE_FIELD'),
            ),
            'SHOW_TEMPLATE' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateShowTemplate'),
                'title' => Loc::getMessage('FORM_ENTITY_SHOW_TEMPLATE_FIELD'),
            ),
            'MAIL_EVENT_TYPE' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateMailEventType'),
                'title' => Loc::getMessage('FORM_ENTITY_MAIL_EVENT_TYPE_FIELD'),
            ),
            'SHOW_RESULT_TEMPLATE' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateShowResultTemplate'),
                'title' => Loc::getMessage('FORM_ENTITY_SHOW_RESULT_TEMPLATE_FIELD'),
            ),
            'PRINT_RESULT_TEMPLATE' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validatePrintResultTemplate'),
                'title' => Loc::getMessage('FORM_ENTITY_PRINT_RESULT_TEMPLATE_FIELD'),
            ),
            'EDIT_RESULT_TEMPLATE' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateEditResultTemplate'),
                'title' => Loc::getMessage('FORM_ENTITY_EDIT_RESULT_TEMPLATE_FIELD'),
            ),
            'FILTER_RESULT_TEMPLATE' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('FORM_ENTITY_FILTER_RESULT_TEMPLATE_FIELD'),
            ),
            'TABLE_RESULT_TEMPLATE' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('FORM_ENTITY_TABLE_RESULT_TEMPLATE_FIELD'),
            ),
            'USE_RESTRICTIONS' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('FORM_ENTITY_USE_RESTRICTIONS_FIELD'),
            ),
            'RESTRICT_USER' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('FORM_ENTITY_RESTRICT_USER_FIELD'),
            ),
            'RESTRICT_TIME' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('FORM_ENTITY_RESTRICT_TIME_FIELD'),
            ),
            'RESTRICT_STATUS' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateRestrictStatus'),
                'title' => Loc::getMessage('FORM_ENTITY_RESTRICT_STATUS_FIELD'),
            ),
            'STAT_EVENT1' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateStatEvent1'),
                'title' => Loc::getMessage('FORM_ENTITY_STAT_EVENT1_FIELD'),
            ),
            'STAT_EVENT2' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateStatEvent2'),
                'title' => Loc::getMessage('FORM_ENTITY_STAT_EVENT2_FIELD'),
            ),
            'STAT_EVENT3' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateStatEvent3'),
                'title' => Loc::getMessage('FORM_ENTITY_STAT_EVENT3_FIELD'),
            ),
        );
    }
    
    /**
     * Returns validators for NAME field.
     *
     * @return array
     */
    public static function validateName() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 255),
        );
    }
    
    /**
     * Returns validators for SID field.
     *
     * @return array
     */
    public static function validateSid() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 50),
        );
    }
    
    /**
     * Returns validators for BUTTON field.
     *
     * @return array
     */
    public static function validateButton() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 255),
        );
    }
    
    /**
     * Returns validators for FIRST_SITE_ID field.
     *
     * @return array
     */
    public static function validateFirstSiteId() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 2),
        );
    }
    
    /**
     * Returns validators for SHOW_TEMPLATE field.
     *
     * @return array
     */
    public static function validateShowTemplate() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 255),
        );
    }
    
    /**
     * Returns validators for MAIL_EVENT_TYPE field.
     *
     * @return array
     */
    public static function validateMailEventType() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 50),
        );
    }
    
    /**
     * Returns validators for SHOW_RESULT_TEMPLATE field.
     *
     * @return array
     */
    public static function validateShowResultTemplate() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 255),
        );
    }
    
    /**
     * Returns validators for PRINT_RESULT_TEMPLATE field.
     *
     * @return array
     */
    public static function validatePrintResultTemplate() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 255),
        );
    }
    
    /**
     * Returns validators for EDIT_RESULT_TEMPLATE field.
     *
     * @return array
     */
    public static function validateEditResultTemplate() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 255),
        );
    }
    
    /**
     * Returns validators for RESTRICT_STATUS field.
     *
     * @return array
     */
    public static function validateRestrictStatus() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 255),
        );
    }
    
    /**
     * Returns validators for STAT_EVENT1 field.
     *
     * @return array
     */
    public static function validateStatEvent1() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 255),
        );
    }
    
    /**
     * Returns validators for STAT_EVENT2 field.
     *
     * @return array
     */
    public static function validateStatEvent2() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 255),
        );
    }
    
    /**
     * Returns validators for STAT_EVENT3 field.
     *
     * @return array
     */
    public static function validateStatEvent3() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array(
            new Length(null, 255),
        );
    }
}