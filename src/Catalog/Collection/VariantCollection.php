<?php

namespace FourPaws\Catalog\Collection;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use FourPaws\Catalog\Model\Variant;
use InvalidArgumentException;

class VariantCollection extends ObjectArrayCollection
{
    /**
     * @param mixed $variant
     */
    protected function checkType($variant)
    {
        if (!($variant instanceof Variant)) {
            throw new InvalidArgumentException('Ожидается объект типа ' . Variant::class);
        }
    }

}
