<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixIblockORM\Collection\CollectionBase;
use FourPaws\BitrixIblockORM\Model\BitrixArrayItemBase;
use FourPaws\Catalog\Model\Product;

class ProductCollection extends CollectionBase
{
    /**
     * @return Product
     */
    protected function doFetch()
    {
        return new Product($this->getCDBResult()->GetNext());
    }

}
