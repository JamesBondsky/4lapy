<?php

namespace FourPaws\Location\Collection;

use FourPaws\BitrixOrm\Collection\D7CollectionBase;
use FourPaws\Location\Model\City;
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
