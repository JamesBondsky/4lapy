<?php

namespace FourPaws\Catalog\Collection;

use FourPaws\App\Application;
use FourPaws\BitrixOrm\Collection\IblockElementCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\StoreBundle\Collection\StockCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Service\StoreService;

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
