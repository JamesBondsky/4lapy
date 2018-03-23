<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Model;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use FourPaws\SapBundle\Consumer\ConsumerInterface;
use FourPaws\SapBundle\Exception\InvalidArgumentException;

class ConsumerCollection extends ObjectArrayCollection
{

    /**
     * @param mixed $object
     *
     * @throws InvalidArgumentException
     * @return void
     */
    protected function checkType($object)
    {
        if (!($object instanceof ConsumerInterface)) {
            throw new InvalidArgumentException(sprintf('Trying to pass not %s object', ConsumerInterface::class));
        }
    }
}
