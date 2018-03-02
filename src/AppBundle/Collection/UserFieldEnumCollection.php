<?php

namespace FourPaws\AppBundle\Collection;

use Adv\Bitrixtools\Collection\ObjectArrayCollection;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use InvalidArgumentException;

class UserFieldEnumCollection extends ObjectArrayCollection
{
    /**
     * @param UserFieldEnumValue $enumValue
     */
    protected function checkType($enumValue)
    {
        if (!($enumValue instanceof UserFieldEnumValue)) {
            throw new InvalidArgumentException('Ожидается объект типа ' . UserFieldEnumValue::class);
        }
    }
}
