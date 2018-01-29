<?php

namespace FourPaws\DeliveryBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Entity\StockResult;

class StockResultCollection extends ArrayCollection
{
    public function getAvailable()
    {
        return $this->filter(
            function (StockResult $stockResult) {
                return $stockResult->getType() === StockResult::TYPE_AVAILABLE;
            }
        );
    }

    public function getDelayed()
    {
        return $this->filter(
            function (StockResult $stockResult) {
                return $stockResult->getType() === StockResult::TYPE_DELAYED;
            }
        );
    }

    public function getUnavailable()
    {
        return $this->filter(
            function (StockResult $stockResult) {
                return $stockResult->getType() === StockResult::TYPE_UNAVAILABLE;
            }
        );
    }
}
