<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Dpd\Lib\Service;

use Bitrix\Main\Loader;
use FourPaws\DeliveryBundle\Dpd\Lib\User;

if (!Loader::includeModule('ipol.dpd')) {
    class Calculator
    {
    }

    return;
}

class Calculator extends \Ipolh\DPD\API\Service\Calculator
{
    public function __construct(User $user)
    {
        $this->client = ClientFactory::create($this->wdsl, $user);
    }
}
