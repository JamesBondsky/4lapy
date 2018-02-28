<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\InputTypes;

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
     * @param $name
     * @param array $input
     * @param $value
     */
    public static function getEditHtmlSingle($name, array $input, $value)
    {
        global $APPLICATION;
        foreach ($input['ZONES'] as $code => $zone) {
            if (!in_array(
                $code,
                [
                    DeliveryService::ZONE_1,
                    DeliveryService::ZONE_2,
                    DeliveryService::ZONE_3,
                    DeliveryService::ZONE_4,
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
