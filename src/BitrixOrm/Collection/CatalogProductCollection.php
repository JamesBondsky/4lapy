<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\CatalogProduct;
use Generator;

class CatalogProductCollection extends CdbResultCollectionBase
{
    /**
     * @return Generator CatalogGroup[]
     */
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new CatalogProduct($fields);
        }
    }

}
