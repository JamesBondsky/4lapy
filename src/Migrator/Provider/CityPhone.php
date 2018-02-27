<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Converter\GpsSeparator;
use FourPaws\Migrator\Converter\StringToBool;

class CityPhone extends ProviderAbstract
{
    /**
     * @inheritdoc
     */
    public function getMap(): array
    {
        $map = [
            'NAME' => 'UF_PHONE|UF_NAME',
            'PROPERTY_LOCATION' => 'UF_LOCATION',
            'ACTIVE' => 'UF_ACTIVE',
            'PROPERTY_DELIVERY_TEXT' => 'UF_DELIVERY_TEXT',
            'PROPERTY_GPS' => 'GPS',
        ];

        return $map;
    }

    /**
     * @inheritdoc
     */
    public function getConverters(): array
    {
        $gpsConverter = new GpsSeparator('GPS');
        $gpsConverter->setGpsN('UF_LATITUDE');
        $gpsConverter->setGpsN('UF_LONGITUDE');

        return [
            new StringToBool('UF_ACTIVE'),
        ];
    }
}
