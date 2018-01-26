<?php

namespace FourPaws\BitrixOrm\Query;

use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;

class HlbReferenceQuery extends D7QueryBase
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
        return new HlbReferenceItemCollection($this->doExec());
    }

}
