<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\ConfirmCode\Collection;

use FourPaws\BitrixOrm\Collection\D7CollectionBase;
use FourPaws\ConfirmCode\Model\ConfirmCode;
use Generator;

class ConfirmCodeCollection extends D7CollectionBase
{
    /**
     * @inheritdoc
     */
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getResult()->fetch()) {
            yield new ConfirmCode($fields);
        }
    }
}
