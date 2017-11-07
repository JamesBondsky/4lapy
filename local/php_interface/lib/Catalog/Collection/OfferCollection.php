<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixOrm\Collection\CdbResultCollectionBase;
use FourPaws\Catalog\Model\Offer;

class OfferCollection extends CdbResultCollectionBase
{
    /**
     * @inheritdoc
     */
    protected function fetchElement(): \Generator
    {
        while ($fields = $this->getCdbResult()->GetNext()) {
            yield new Offer($fields);
        }
    }

}
