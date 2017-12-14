<?php

namespace FourPaws\Search\Collection;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use FourPaws\Search\Model\Bucket;
use InvalidArgumentException;

class BucketCollection extends ObjectArrayCollection
{
    /**
     * @param mixed $object
     */
    protected function checkType($object)
    {
        if (!($object instanceof Bucket)) {
            throw new InvalidArgumentException('Ожидается объект типа ' . Bucket::class);
        }
    }
}
