<?php

namespace FourPaws\DeliveryBundle\Dpd;

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
        $defaultLocation = \Bitrix\Main\Config\Option::get('sale', 'location', '', ADMIN_SECTION ? 's1' : false);
        $currentLocation = \Bitrix\Main\Config\Option::get(IPOLH_DPD_MODULE, 'SENDER_LOCATION', $defaultLocation);

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
