<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Dpd\Lib\Service;

use Bitrix\Main\Loader;

if (!Loader::includeModule('ipol.dpd')) {
    class ClientFactory
    {
    }

    return;
}

class ClientFactory extends \Ipolh\DPD\API\Client\Factory
{
    public static function create($wdsl, \Ipolh\DPD\API\User $user)
    {
        if (class_exists('\\SoapClient')) {
            return new Soap($wdsl, $user);
        }

        throw new \Exception("Soap client is not found", 1);
    }
}
