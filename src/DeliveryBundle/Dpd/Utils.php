<?php

namespace FourPaws\DeliveryBundle\Dpd;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Sale\Location\LocationTable;

if (!Loader::includeModule('ipol.dpd')) {
    class Utils
    {
    }

    return;
}

class Utils extends \Ipolh\DPD\Utils
{
    public static function getSaleLocationId()
    {
        $defaultLocation = Option::get('sale', 'location', '', ADMIN_SECTION ? 's1' : false);
        $currentLocation = Option::get(IPOLH_DPD_MODULE, 'SENDER_LOCATION', $defaultLocation);

        $location = LocationTable::getList(
            [
                'filter' => ['ID' => $currentLocation],
            ]
        )->fetch();

        if (!$location) {
            return false;
        }

        return $location['ID'];
    }
}
