<?php

namespace FourPaws\LogDoc\Collection;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use FourPaws\LogDoc\Model\Document;
use InvalidArgumentException;

class DocumentCollection extends ObjectArrayCollection
{
    /**
     * @param mixed $object
     */
    protected function checkType($object)
    {
        if (!($object instanceof Document)) {
            throw new InvalidArgumentException('Ожидается объект типа ' . Document::class);
        }
    }
}
