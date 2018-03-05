<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Converter\GpsSeparator;
use FourPaws\Migrator\Converter\MetroToReference;
use FourPaws\Migrator\Converter\PhoneBuilder;
use FourPaws\Migrator\Converter\StoreNameBuilder;
use FourPaws\Migrator\Converter\StringToLocation;
use FourPaws\Migrator\Converter\StringToNotEmptyString;
use FourPaws\Migrator\Converter\StringToReference;
use FourPaws\Migrator\Converter\TextHtmlToString;

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
            'NAME'                     => 'TITLE',
            'SORT'                     => 'SORT',
            'XML_ID'                   => 'XML_ID',
            'ACTIVE'                   => 'ACTIVE',
            'TITLE'                    => 'TITLE',
            'PROPERTY_ADDRESS'         => 'ADDRESS',
            'PROPERTY_CITY_NAME'       => 'UF_LOCATION',
            'PROPERTY_PHONE'           => 'PHONE',
            'PROPERTY_PHONE_DOB'       => 'ADDITIONAL_PHONE',
            'PROPERTY_WORK_TIME'       => 'SCHEDULE',
            'PROPERTY_GPS'             => 'GPS',
            'PROPERTY_EMAIL'           => 'EMAIL',
            'PROPERTY_PICKUP'          => 'ISSUING_CENTER',
            'PROPERTY_ADD_INFORMATION' => 'DESCRIPTION',
            'PROPERTY_ID_SHOP_YM'      => 'UF_YANDEX_SHOP_ID',
            'IS_SHOP'                  => 'UF_IS_SHOP',
            'PROPERTY_METRO_NAME'      => 'UF_METRO',
            'METRO_WAY'                => 'METRO_WAY',
            'METRO_WAY_COLOR'          => 'METRO_WAY_COLOR',
            'PROPERTY_SERVICES'        => 'UF_SERVICES',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function getConverters() : array
    {
        $nameBuilder          = new StoreNameBuilder('TITLE');
        $phoneBuilder         = new PhoneBuilder('PHONE');
        $gpsSeparator         = new GpsSeparator('GPS');
        $addressConverter     = new StringToNotEmptyString('ADDRESS');
        $descriptionConverter = new TextHtmlToString('DESCRIPTION');
        
        $converters = [
            $nameBuilder,
            $phoneBuilder,
            $gpsSeparator,
            $addressConverter,
            $descriptionConverter,
        ];
        
        try {
            $locationConverter = new StringToLocation('UF_LOCATION');
            
            $converters[] = $locationConverter;
        } catch (\Exception $e) {
        }
        
        try {
            $serviceReference = new StringToReference('UF_SERVICES');
            $serviceReference->setReferenceCode('StoreServices');
            $serviceReference->setReturnFieldName('ID');
            
            $converters[] = $serviceReference;
        } catch (\Exception $e) {
        }
        
        try {
            $metroReference = new MetroToReference('UF_METRO');
            $metroReference->setReferenceCode('MetroStations');
            $metroReference->setReturnFieldName('ID');
            
            $converters[] = $metroReference;
        } catch (\Exception $e) {
        }
        
        return $converters;
    }
}
