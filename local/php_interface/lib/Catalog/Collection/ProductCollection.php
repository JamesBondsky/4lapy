<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixOrm\Collection\CdbResultCollectionBase;
use FourPaws\Catalog\Model\Product;
use Generator;

class ProductCollection extends CdbResultCollectionBase
{
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new Product($fields);
        }
    }
}
