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
use FourPaws\CatalogBundle\Service\FilterService;
use FourPaws\Search\SearchService;
use InvalidArgumentException;
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
     * @var FilterService
     */
    protected $filterService;

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
        $this->filterService = Application::getInstance()->getContainer()->get(FilterService::class);
    }

    /**
     * @inheritdoc
     */
    public function getMinValue(): float
    {
        $this->initRange();

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
        $this->initRange();

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
     * Заполняет границы доступного диапазона при первом обращении.
     */
    private function initRange()
    {
        if (is_null($this->minValue) || is_null($this->maxValue)) {
            list($this->minValue, $this->maxValue) = $this->getRange();
        }
    }

    /**
     * @return array
     */
    protected function doGetRange(): array
    {
        $internalFilters = $this->catalogService->getInternalFilters();

        $search = $this->searchService->getIndexHelper()->createProductSearch();
        $search->getQuery()
               ->setSize(0)
               ->setParam('query', $this->searchService->getFullQueryRule($internalFilters));

        foreach ($this->getAggs() as $agg) {
            $search->getQuery()->addAggregation($agg);
        }

        $result = $search->search();

        foreach ($result->getAggregations() as $aggName => $aggResult) {
            $this->collapse($aggName, $aggResult);
        }

        /**
         * Лучше прямое обращение к полям, чтобы не рисковать получить зацикливание,
         * если аггрегация будет возвращаться неверно.
         */
        return [$this->minValue, $this->maxValue];
    }

    /**
     * Возвращает минимальное и максимальное возможные значения. Результат работы этого метода должен кешироваться.
     *
     * @return float[]
     */
    abstract protected function getRange(): array;

    /**
     * Возвращает true, если фильтр выбран.
     *
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
     * Возвращает true, если фильтр доступен.
     *
     * @inheritdoc
     */
    public function hasAvailableVariants(): bool
    {
        if ($this->getMinValue() === $this->getMaxValue() && $this->getMinValue() === 0) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getFilterRule(): AbstractQuery
    {
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
     * @inheritdoc
     */
    public function collapse(string $aggName, array $aggResult)
    {
        if (!array_key_exists('value', $aggResult)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Отсутствует value в результате аггрегации `%s`',
                    $aggName
                )
            );
        }

        if ($this->getMinFilterCode() === $aggName) {
            $this->withMinValue((float)$aggResult['value']);
        } elseif ($this->getMaxFilterCode() === $aggName) {
            $this->withMaxValue((float)$aggResult['value']);
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Неподдерживаемое имя аггрегации %s',
                    $aggName
                )
            );
        }
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
    protected function getMinFilterCode(): string
    {
        return $this->getFilterCode() . 'Min';
    }

    /**
     * @return string
     */
    protected function getMaxFilterCode(): string
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
