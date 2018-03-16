<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LocationBundle\Collection;

use FourPaws\BitrixOrm\Collection\D7CollectionBase;
use FourPaws\LocationBundle\Model\City;
use Generator;

class CityCollection extends D7CollectionBase
{
    /**
     * @inheritdoc
     */
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getResult()->fetch()) {
            yield new City($fields);
        }
    }
}
