<?php

namespace FourPaws\Catalog\Model\Filter\Abstraction;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Aggregation\Nested;
use Elastica\Query\AbstractQuery;
use Elastica\Query\Nested as NestedQuery;

/**
 * Class ReferenceFilterNested
 *
 * @package FourPaws\Catalog\Model\Filter\Abstraction
 */
abstract class ReferenceFilterNested extends ReferenceFilterBase
{
    /**
     * Код объекта, где будет осуществляться поиск
     * @return string
     */
    abstract public function getPath(): string;

    /**
     * Поле, по которому искать
     * @return string
     */
    abstract public function getNestedRuleCode(): string;

    /**
     * @return string
     */
    public function getRuleCode(): string
    {
        return implode('.', [$this->getPath(), $this->getNestedRuleCode()]);
    }

    /**
     * @return AbstractAggregation
     */
    public function getAggRule(): AbstractAggregation
    {
        return (new Nested($this->getFilterCode(), $this->getPath()))->addAggregation(
            parent::getAggRule()
        );
    }

    /**
     * @inheritdoc
     */
    public function getFilterRule(): AbstractQuery
    {
        $query = new NestedQuery();
        $query->setPath($this->getPath());

        return $query->setQuery(parent::getFilterRule());
    }
}
