<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Catalog\Model\Filter;

use Elastica\Aggregation\Nested;
use Elastica\Query\AbstractQuery;
use Elastica\Query\Nested as NestedQuery;
use FourPaws\Catalog\Collection\AggCollection;
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
                && \is_array($aggResult[$subAggName])
            ) {
                parent::collapse($subAggName, $aggResult[$subAggName]);
            }
        }
    }

    public function getAggs(): AggCollection
    {
        return new AggCollection([
            (new Nested($this->getMinNestedAggName(), $this->getNestedPath()))->addAggregation($this->getMinAggRule()),
            (new Nested($this->getMaxNestedAggName(), $this->getNestedPath()))->addAggregation($this->getMaxAggRule()),
        ]);
    }

    /**
     * @return string
     */
    protected function getNestedPath()
    {
        return 'offers';
    }

    /**
     * @return string
     */
    protected function getMinNestedAggName()
    {
        return $this->getMinFilterCode() . 'Nested';
    }

    /**
     * @return string
     */
    protected function getMaxNestedAggName()
    {
        return $this->getMaxFilterCode() . 'Nested';
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

    public function getFilterRule(): AbstractQuery
    {
        $query = new NestedQuery();
        $query->setPath($this->getNestedPath());

        return $query->setQuery(parent::getFilterRule());
    }
}
