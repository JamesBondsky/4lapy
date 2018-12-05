<?php

namespace FourPaws\App\Geo\Entity\Table;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\Validator\Length;

class RegionTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return '4lapy_sxgeo_regions';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'id' => array(
                'data_type' => 'integer',
                'primary' => true,
            ),
            'iso' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateIso'),
            ),
            'country' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateCountry'),
            ),
            'name_ru' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateNameRu'),
            ),
            'name_en' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateNameEn'),
            ),
            'timezone' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateTimezone'),
            ),
            'okato' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateOkato'),
            ),
        );
    }

    /**
     * Returns validators for iso field.
     *
     * @return array
     */
    public static function validateIso()
    {
        return array(
            new Length(null, 7),
        );
    }

    /**
     * Returns validators for country field.
     *
     * @return array
     */
    public static function validateCountry()
    {
        return array(
            new Length(null, 2),
        );
    }

    /**
     * Returns validators for name_ru field.
     *
     * @return array
     */
    public static function validateNameRu()
    {
        return array(
            new Length(null, 128),
        );
    }

    /**
     * Returns validators for name_en field.
     *
     * @return array
     */
    public static function validateNameEn()
    {
        return array(
            new Length(null, 128),
        );
    }

    /**
     * Returns validators for timezone field.
     *
     * @return array
     */
    public static function validateTimezone()
    {
        return array(
            new Length(null, 30),
        );
    }

    /**
     * Returns validators for okato field.
     *
     * @return array
     */
    public static function validateOkato()
    {
        return array(
            new Length(null, 4),
        );
    }
}