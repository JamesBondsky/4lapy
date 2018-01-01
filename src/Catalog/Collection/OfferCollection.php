<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\BitrixOrm\Collection\IblockElementCollection;
use FourPaws\Catalog\Model\Offer;

class OfferCollection extends IblockElementCollection
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
