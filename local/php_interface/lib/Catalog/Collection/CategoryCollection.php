<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixIblockORM\Collection\CollectionBase;
use FourPaws\Catalog\Model\Category;

class CategoryCollection extends CollectionBase
{

    /**
     * @return Category|false
     */
    protected function doFetch()
    {
        $fields = $this->getCDBResult()->GetNext();
        if (false === $fields) {
            return false;
        }
        return new Category($fields);
    }

}
