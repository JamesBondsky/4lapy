<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixOrm\Collection\CdbResultCollectionBase;
use FourPaws\Catalog\Model\Brand;
use Generator;

class BrandCollection extends CdbResultCollectionBase
{
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new Brand($fields);
        }
    }

}
