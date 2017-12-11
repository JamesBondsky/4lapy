<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\CatalogGroup;
use Generator;

class CatalogGroupCollection extends CdbResultCollectionBase
{
    /**
     * @return Generator CatalogGroup[]
     */
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new CatalogGroup($fields);
        }
    }

}
