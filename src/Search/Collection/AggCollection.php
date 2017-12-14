<?php

namespace FourPaws\Search\Collection;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use Elastica\Aggregation\AbstractAggregation;
use InvalidArgumentException;

class AggCollection extends ObjectArrayCollection
{

    /**
     * @param mixed $object
     */
    protected function checkType($object)
    {
        if (!($object instanceof AbstractAggregation)) {
            throw new InvalidArgumentException('Ожидается объект типа ' . AbstractAggregation::class);
        }
    }

}
