<?php

namespace FourPaws\Catalog\Model\Filter\Abstraction;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Aggregation\Nested;

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

    public function getRuleCode(): string
    {
        return implode('.', [$this->getPath(), $this->getNestedRuleCode()]);
    }

    public function getAggRule(): AbstractAggregation
    {
        return (new Nested($this->getFilterCode(), $this->getPath()))->addAggregation(
            parent::getAggRule()
        );
    }
}
