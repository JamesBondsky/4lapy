<?php

namespace FourPaws\Search\Model;

use Elastica\ResultSet;
use FourPaws\Catalog\Collection\ProductCollection;

interface ProductResultInterface
{
    public function getProductCollection(): ProductCollection;

    public function getResultSet(): ResultSet;
}
