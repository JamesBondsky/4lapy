<?php

namespace FourPaws\Migrator\Provider;

/**
 * Class Store
 *
 * @package FourPaws\Migrator\Provider
 */
class Store extends ProviderAbstract
{
    /**
     * @inheritdoc
     */
    public function getMap() : array
    {
        return [
            'NAME'                     => 'NAME',
            'SORT'                     => 'SORT',
            'XML_ID'                   => 'XML_ID',
            'ACTIVE'                   => 'ACTIVE',
            'PROPERTY_ADDRESS'         => 'ADDRESS',
            'PROPERTY_CITY'            => 'UF_LOCATION',
            'PROPERTY_PHONE'           => 'PHONE',
            'PROPERTY_PHONE_DOB'       => 'PHONE',
            'PROPERTY_WORK_TIME'       => 'SCHEDULE',
            'PROPERTY_GPS'             => 'GPS',
            'PROPERTY_EMAIL'           => 'EMAIL',
            'PROPERTY_PICKUP'          => 'ISSUING_CENTER',
            'PROPERTY_ADD_INFORMATION' => 'DESCRIPTION',
            'PROPERTY_ID_SHOP_YM'      => 'UF_YANDEX_SHOP_ID',
            'IS_SHOP'                  => 'UF_IS_SHOP',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function getConverters() : array
    {
        
        
        $converters = [
        
        ];
        
        return $converters;
    }
}
