<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixOrm\Collection\CdbResultCollectionBase;
use FourPaws\Catalog\Model\Category;
use Generator;

class CategoryCollection extends CdbResultCollectionBase
{
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new Category($fields);
        }
    }
}
