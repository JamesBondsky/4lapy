<?php

namespace FourPaws\App\Geo\Entity\Table;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\Validator\Length;

class CityTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return '4lapy_sxgeo_cities';
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
            'region_id' => array(
                'data_type' => 'integer',
                'required' => true,
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
            'lat' => array(
                'data_type' => 'float',
                'required' => true,
            ),
            'lon' => array(
                'data_type' => 'float',
                'required' => true,
            ),
            'okato' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateOkato'),
            ),
            new ReferenceField(
                'region',
                'FourPaws\App\Geo\Entity\Table\RegionTable',
                ['=this.region_id' => 'ref.id']
            ),
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
     * Returns validators for okato field.
     *
     * @return array
     */
    public static function validateOkato()
    {
        return array(
            new Length(null, 20),
        );
    }

    /**
     * Returns validators for fias field.
     *
     * @return array
     */
    public static function validateFias()
    {
        return array(
            new Length(null, 100),
        );
    }
}