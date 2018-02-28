<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Converter\StringToLocation;

/**
 * Class StoreLocation
 *
 * @package FourPaws\Migrator\Provider
 */
class StoreLocation extends ProviderAbstract
{
    /**
     * @inheritdoc
     */
    public function getMap(): array
    {
        return [
            'XML_ID' => 'XML_ID',
            'PROPERTY_CITY_NAME' => 'UF_LOCATION',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getConverters(): array
    {
        $converters = [];

        try {
            $locationConverter = new StringToLocation('UF_LOCATION');

            $converters[] = $locationConverter;
        } catch (\Exception $e) {
        }

        return $converters;
    }
}
