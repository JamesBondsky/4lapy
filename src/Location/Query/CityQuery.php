<?php

namespace FourPaws\Location\Query;

use FourPaws\Location\Collection\CityCollection;
use FourPaws\BitrixOrm\Query\D7QueryBase;
use FourPaws\BitrixOrm\Collection\CollectionBase;

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
