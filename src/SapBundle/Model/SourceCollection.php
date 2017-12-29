<?php

namespace FourPaws\SapBundle\Model;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use FourPaws\SapBundle\Source\SourceInterface;
use InvalidArgumentException;

class SourceCollection extends ObjectArrayCollection
{

    /**
     * @param mixed $object
     *
     * @throws InvalidArgumentException
     * @return void
     */
    protected function checkType($object)
    {
        if (!($object instanceof SourceInterface)) {
            throw new InvalidArgumentException(sprintf('Trying to pass not %s object', SourceInterface::class));
        }
    }
}
