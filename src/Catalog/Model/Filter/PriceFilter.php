<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\RangeFilterBase;
use WebArch\BitrixCache\BitrixCache;

class PriceFilter extends RangeFilterBase
{
    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Price';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'offers.prices.PRICE';
    }

    /**
     * @inheritdoc
     */
    protected function getRange(): array
    {

        $callDoGetRange = function () {
            return $this->doGetRange();
        };

        //TODO Добавить зависимость от региона!
        return (new BitrixCache())->withId(__METHOD__)
                                  ->resultOf($callDoGetRange);
    }

}
