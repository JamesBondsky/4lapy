<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Collection;

use FourPaws\BitrixOrm\Collection\D7CollectionBase;
use FourPaws\UserBundle\Model\ConfirmCode;
use Generator;

/**
 * Class ConfirmCodeCollection
 *
 * @package FourPaws\UserBundle\Collection
 */
class ConfirmCodeCollection extends D7CollectionBase
{
    /**
     * @inheritdoc
     */
    protected function fetchElement() : Generator
    {
        while ($fields = $this->getResult()->fetch()) {
            yield new ConfirmCode($fields);
        }
    }
}
