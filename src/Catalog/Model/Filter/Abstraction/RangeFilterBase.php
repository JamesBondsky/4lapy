<?php

namespace FourPaws\Catalog\Model\Filter\Abstraction;

use Elastica\Aggregation\Max;
use Elastica\Aggregation\Min;
use Elastica\Query\AbstractQuery;
use Elastica\Query\Range;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\AggCollection;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Filter\RangeFilterInterface;
use FourPaws\Search\SearchService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\HttpFoundation\Request;

abstract class RangeFilterBase extends FilterBase implements RangeFilterInterface
{
    /**
     * @var float
     */
    protected $fromValue = 0.0;

    /**
     * @var float
     */
    protected $toValue = 0.0;

    /**
     * @var float
     */
    protected $minValue;

    /**
     * @var float
     */
    protected $maxValue;

    /**
     * @var SearchService
     */
    protected $searchService;

    /**
     * RangeFilterBase constructor.
     *
     * @param array $fields
     *
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
        $this->searchService = Application::getInstance()->getContainer()->get('search.service');
    }

    /**
     * @inheritdoc
     */
    public function getMinValue(): float
    {
        if (is_null($this->minValue)) {
            $this->minValue = $this->doGetMinValue();
        }

        return $this->minValue;
    }

    /**
     * @param float $minValue
     *
     * @return $this
     */
    public function withMinValue(float $minValue)
    {
        $this->minValue = $minValue;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMaxValue(): float
    {
        if (is_null($this->maxValue)) {
            $this->maxValue = $this->doGetMaxValue();
        }

        return $this->maxValue;
    }

    /**
     * @param float $maxValue
     *
     * @return $this
     */
    public function withMaxValue(float $maxValue)
    {
        $this->maxValue = $maxValue;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFromValue(): float
    {
        return $this->fromValue;
    }

    /**
     * @param float $fromValue
     *
     * @return $this
     */
    public function withFromValue(float $fromValue)
    {
        $this->fromValue = $fromValue;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getToValue(): float
    {
        return $this->toValue;
    }

    /**
     * @param float $toValue
     *
     * @return $this
     */
    public function withToValue(float $toValue)
    {
        $this->toValue = $toValue;

        return $this;
    }

    /**
     * Возвращает пустую коллекцию, т.к. для этого типа список вариантов невозможен.
     *
     * @return VariantCollection
     */
    protected function doGetAllVariants(): VariantCollection
    {
        return new VariantCollection();
    }

    /**
     * @return mixed
     */
    private function doGetMinValue(): float
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($min, $max) = $this->getRange();

        return $min;
    }

    private function doGetMaxValue(): float
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($min, $max) = $this->getRange();

        return $max;
    }

    /**
     * @return array
     */
    protected function doGetRange(): array
    {
        //TODO Зависимость от региона!!!

        $minPriceAgg = $this->getMinAggRule();
        $maxPriceAgg = $this->getMaxAggRule();

        $search = $this->searchService->getIndexHelper()->createProductSearch();

        $search->getQuery()
               ->setSize(0)
               ->addAggregation($minPriceAgg)
               ->addAggregation($maxPriceAgg);

        $result = $search->search();

        $minValue = $maxValue = -1;

        foreach ($result->getAggregations() as $name => $aggregation) {
            if ($minPriceAgg->getName() === $name && key_exists('value', $aggregation)) {
                $minValue = $aggregation['value'];
            } elseif ($maxPriceAgg->getName() === $name && key_exists('value', $aggregation)) {
                $maxValue = $aggregation['value'];
            }
        }

        return [$minValue, $maxValue];
    }

    /**
     * Возвращает минимальное и максимальное возможные значения. Результат работы этого метода должен кешироваться.
     *
     * @return float[]
     */
    abstract protected function getRange(): array;

    /**
     * @inheritdoc
     */
    public function hasCheckedVariants(): bool
    {
        if ($this->getFromValue() > 0) {
            return true;
        }

        if ($this->getToValue() > 0) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getFilterRule(): AbstractQuery
    {
        //TODO Зависимость от региона!!!
        $args = [];

        if ($this->getFromValue() > 0) {
            $args['gte'] = $this->getFromValue();
        }

        if ($this->getToValue() > 0) {
            $args['lte'] = $this->getToValue();
        }

        return new Range(
            $this->getRuleCode(),
            $args
        );
    }

    /**
     * @inheritdoc
     */
    public function getAggs(): AggCollection
    {
        $minAgg = $this->getMinAggRule();
        $maxAgg = $this->getMaxAggRule();

        return new AggCollection([$minAgg, $maxAgg]);
    }

    /**
     * @param Request $request
     */
    public function initState(Request $request)
    {
        $fromValue = $request->query->get($this->getFromFilterCode());
        $toValue = $request->query->get($this->getToFilterCode());

        if (!is_numeric($fromValue)) {
            $fromValue = 0;
        }

        if (!is_numeric($toValue)) {
            $toValue = 0;
        }

        $this->withFromValue($fromValue)
             ->withToValue($toValue);
    }

    /**
     * @return Min
     */
    protected function getMinAggRule(): Min
    {
        return (new Min($this->getMinFilterCode()))->setField($this->getRuleCode());
    }

    /**
     * @return Max
     */
    protected function getMaxAggRule(): Max
    {
        return (new Max($this->getMaxFilterCode()))->setField($this->getRuleCode());
    }

    /**
     * @return string
     */
    private function getMinFilterCode(): string
    {
        return $this->getFilterCode() . 'Min';
    }

    /**
     * @return string
     */
    private function getMaxFilterCode(): string
    {
        return $this->getFilterCode() . 'Max';
    }

    /**
     * @return string
     */
    private function getFromFilterCode(): string
    {
        return $this->getFilterCode() . 'From';
    }

    /**
     * @return string
     */
    private function getToFilterCode(): string
    {
        return $this->getFilterCode() . 'To';
    }

}
