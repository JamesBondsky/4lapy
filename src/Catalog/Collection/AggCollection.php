<?php

namespace FourPaws\Catalog\Collection;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use Elastica\Aggregation\AbstractAggregation;
use InvalidArgumentException;

class AggCollection extends ObjectArrayCollection
{
    /**
     * @param mixed $variant
     */
    protected function checkType($variant)
    {
        if (!($variant instanceof AbstractAggregation)) {
            throw new InvalidArgumentException('Ожидается объект типа ' . AbstractAggregation::class);
        }
    }
}
