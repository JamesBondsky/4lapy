<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Repository\Table;

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\SystemException;

class DpdLocationTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'b_ipol_dpd_location';
    }

    /**
     * @return array
     * @throws SystemException
     */
    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new StringField('COUNTRY_CODE', [
                'required' => false,
            ]),
            new StringField('COUNTRY_NAME', [
                'required' => false,
            ]),
            new StringField('REGION_CODE', [
                'required' => false,
            ]),
            new StringField('REGION_NAME', [
                'required' => false,
            ]),
            new StringField('CITY_ID', [
                'required' => false,
            ]),
            new StringField('CITY_CODE', [
                'required' => false,
            ]),
            new StringField('CITY_NAME', [
                'required' => false,
            ]),
            new StringField('LOCATION_ID', [
                'required' => false,
            ]),
            new BooleanField('IS_CASH_PAY', [
                'values' => array('N', 'Y'),
                'default_value' => 'N',
            ]),
        ];
    }
}