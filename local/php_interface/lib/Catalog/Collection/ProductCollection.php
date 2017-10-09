<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixIblockORM\Collection\CollectionBase;
use FourPaws\Catalog\Model\Product;

class ProductCollection extends CollectionBase
{
    /**
     * @return Product|false
     */
    protected function doFetch()
    {
        $fields = $this->getCDBResult()->GetNext();

        if (false == $fields) {
            return false;
        }

        return new Product($fields);
    }

}
