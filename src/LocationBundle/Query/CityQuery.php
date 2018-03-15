<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LocationBundle\Query;

use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\D7QueryBase;
use FourPaws\LocationBundle\Collection\CityCollection;

class CityQuery extends D7QueryBase
{
    /**
     * @inheritdoc
     */
    public function getBaseSelect(): array
    {
        return ['*'];
    }

    /**
     * @inheritdoc
     */
    public function getBaseFilter(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function exec(): CollectionBase
    {
        return new CityCollection($this->doExec());
    }
}
