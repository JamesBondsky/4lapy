<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\InputTypes;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\Input\StringInput;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class DeliveryInterval extends StringInput
{
    /**
     * @param $value
     *
     * @return bool
     */
    public static function isMultiple($value)
    {
        return false;
    }

    /**
     * @param       $name
     * @param array $input
     * @param       $value
     *
     * @throws ArgumentTypeException
     * @throws SystemException
     */
    public static function getEditHtmlSingle($name, array $input, $value)
    {
        global $APPLICATION;
        foreach ($input['ZONES'] as $code => $zone) {
            if (!\in_array(
                $code,
                [
                    DeliveryService::ZONE_1,
                    DeliveryService::ZONE_2,
                    DeliveryService::ZONE_3,
                    DeliveryService::ZONE_4,
                    DeliveryService::ZONE_5,
                    DeliveryService::ZONE_6,
                    DeliveryService::ZONE_NIZHNY_NOVGOROD,
                    DeliveryService::ZONE_NIZHNY_NOVGOROD_REGION,
                    DeliveryService::ZONE_VLADIMIR,
                    DeliveryService::ZONE_VLADIMIR_REGION,
                    DeliveryService::ZONE_VORONEZH,
                    DeliveryService::ZONE_VORONEZH_REGION,
                    DeliveryService::ZONE_YAROSLAVL,
                    DeliveryService::ZONE_YAROSLAVL_REGION,
                    DeliveryService::ZONE_TULA,
                    DeliveryService::ZONE_TULA_REGION,
                    DeliveryService::ZONE_KALUGA,
                    DeliveryService::ZONE_KALUGA_REGION,
                    DeliveryService::ZONE_IVANOVO,
                    DeliveryService::ZONE_IVANOVO_REGION,
                ],
                true
            )) {
                unset($input['ZONES'][$code]);
            }
        }
        $APPLICATION->IncludeComponent(
            'fourpaws:delivery.interval.edit',
            '',
            [
                'VALUE'      => $value,
                'ZONES'      => $input['ZONES'],
                'INPUT_NAME' => $name,
            ]
        );
    }

    /**
     * @param array $input
     * @param $reload
     *
     * @return array
     */
    public static function getSettings(array $input, $reload)
    {
        return [];
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function isDeletedSingle($value)
    {
        return false;
    }
}
