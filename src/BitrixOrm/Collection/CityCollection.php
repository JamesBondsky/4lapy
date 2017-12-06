<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\City;
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
