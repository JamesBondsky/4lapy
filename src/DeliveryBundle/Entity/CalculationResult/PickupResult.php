<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use FourPaws\StoreBundle\Entity\Store;

class PickupResult extends BaseResult
{
    public function doCalculateDeliveryDate(): void
    {
        $shops = $this->getStockResult()->getStores();
        /** @var Store $shop */
        foreach ($shops as $shop) {

        }

        parent::doCalculateDeliveryDate();
    }
}
