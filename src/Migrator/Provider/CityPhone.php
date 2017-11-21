<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Converter\LocationCodeToLocation;
use FourPaws\Migrator\Converter\StringToBool;

class CityPhone extends ProviderAbstract
{
    /**
     * @inheritdoc
     */
    public function getMap() : array
    {
        $map = [
            'NAME'                   => 'UF_PHONE|UF_NAME',
            'PROPERTY_LOCATION'      => 'UF_LOCATION',
            'ACTIVE'                 => 'UF_ACTIVE',
            'PROPERTY_DELIVERY_TEXT' => 'UF_DELIVERY_TEXT',
        ];
        
        return $map;
    }
    
    /**
     * @inheritdoc
     */
    public function getConverters() : array
    {
        return [
            new StringToBool('UF_ACTIVE'),
            new LocationCodeToLocation('UF_LOCATION'),
        ];
    }
}
