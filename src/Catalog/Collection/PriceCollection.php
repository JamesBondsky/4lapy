<?php

namespace FourPaws\Catalog\Collection;

use Bitrix\Main\DB\ArrayResult;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\BitrixOrm\Collection\D7CollectionBase;
use FourPaws\Catalog\Model\Price;
use Generator;

class PriceCollection extends D7CollectionBase
{
    /**
     * @inheritdoc
     */
    protected function fetchElement(): Generator
    {
        while ($fields = $this->getResult()->fetch()) {
            yield new Price($fields);
        }
    }

    /**
     * @param ArrayCollection $catalogPriceCollection
     *
     * @return PriceCollection
     */
    public static function createIndexedByRegion(ArrayCollection $catalogPriceCollection)
    {
        $self = new self(new ArrayResult([]));

        /** @var Price $price */
        foreach ($catalogPriceCollection as $price) {
            if ($price instanceof Price) {
                $self->set($price->getRegionId(), $price);
            }
        }

        return $self;
    }
}
