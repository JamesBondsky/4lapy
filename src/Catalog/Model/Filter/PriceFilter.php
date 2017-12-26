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
        return 'offers.price';
    }

    /**
     * @inheritdoc
     */
    public function collapse(string $aggName, array $aggResult)
    {
        foreach ([$this->getMinFilterCode(), $this->getMaxFilterCode()] as $subAggName) {
            if (
                array_key_exists($subAggName, $aggResult)
                && is_array($aggResult[$subAggName])
            ) {
                parent::collapse($subAggName, $aggResult[$subAggName]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getRange(): array
    {
        $callDoGetRange = function () {
            return $this->doGetRange();
        };
        return (new BitrixCache())
            ->withId(__METHOD__)
            ->resultOf($callDoGetRange);
    }
}
