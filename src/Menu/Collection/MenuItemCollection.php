<?php

namespace FourPaws\Menu\Collection;

use FourPaws\BitrixOrm\Collection\CdbResultCollectionBase;
use FourPaws\Menu\Model\MenuItem;
use Generator;

class MenuItemCollection extends CdbResultCollectionBase
{
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new MenuItem($fields);
        }
    }
}
