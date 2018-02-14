<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Dpd;

use Bitrix\Main\Loader;

if (!Loader::includeModule('ipol.dpd')) {
    class TerminalTable
    {
    }

    return;
}

class TerminalTable extends \Ipolh\DPD\DB\Terminal\Table
{
}
