<?php

namespace FourPaws\BitrixOrm\Collection;

use FourPaws\BitrixOrm\Model\ColorReferenceItem;
use Generator;

class HlbColorCollection extends D7CollectionBase
{
    /**
     * @inheritdoc
     */
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getResult()->fetch()) {
            yield new ColorReferenceItem($fields);
        }
    }

}
